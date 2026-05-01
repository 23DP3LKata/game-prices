<?php

namespace App\Console\Commands;

use App\Models\ItadSyncLog;
use App\Services\ItadSyncStatsService;
use App\Services\Stores\ItadPriceSyncService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncItadPrices extends Command
{
    protected $signature = 'itad:sync-prices';
    protected $description = 'Sync ITAD prices for enabled stores.';

    public function __construct(private readonly ItadSyncStatsService $statsService)
    {
        parent::__construct();
    }

    public function handle(ItadPriceSyncService $syncService): int
    {
        $startedAt = Carbon::now();
        Log::info('ITAD price sync started.', [
            'command' => $this->signature,
        ]);

        try {
            $stats = $syncService->syncPrices();

            $this->info('ITAD price sync completed.');
            $this->line('Synced: '.$stats['synced']);
            $this->line('Updated: '.$stats['updated']);
            $this->line('Skipped: '.$stats['skipped']);
            $this->line('Failed: '.$stats['failed']);

            $output = implode(PHP_EOL, [
                'Synced: '.$stats['synced'],
                'Updated: '.$stats['updated'],
                'Skipped: '.$stats['skipped'],
                'Failed: '.$stats['failed'],
            ]);

            ItadSyncLog::query()->create([
                'sync_type' => 'prices',
                'status' => 'success',
                'command' => $this->signature,
                'stores_total' => $this->statsService->enabledStoresCount(),
                'games_total' => $this->statsService->activeGamesCount(),
                'output' => $output,
                'started_at' => $startedAt,
                'finished_at' => Carbon::now(),
            ]);

            Log::info('ITAD price sync completed.', [
                'command' => $this->signature,
                'stats' => $stats,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            ItadSyncLog::query()->create([
                'sync_type' => 'prices',
                'status' => 'failed',
                'command' => $this->signature,
                'stores_total' => $this->statsService->enabledStoresCount(),
                'games_total' => $this->statsService->activeGamesCount(),
                'output' => $exception->getMessage(),
                'started_at' => $startedAt,
                'finished_at' => Carbon::now(),
            ]);

            Log::error('ITAD price sync failed.', [
                'command' => $this->signature,
                'error' => $exception->getMessage(),
            ]);

            $this->error('ITAD price sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
