<?php

namespace App\Console\Commands;

use App\Models\ItadSyncLog;
use App\Services\ItadSyncStatsService;
use App\Services\Stores\ItadPriceSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncItadListings extends Command
{
    protected $signature = 'itad:sync-listings';
    protected $description = 'Resolve ITAD IDs and create store listings.';

    public function __construct(private readonly ItadSyncStatsService $statsService)
    {
        parent::__construct();
    }

    public function handle(ItadPriceSyncService $syncService): int
    {
        $startedAt = Carbon::now();

        Log::info('ITAD listing sync started.', [
            'command' => $this->signature,
        ]);

        try {
            $stats = $syncService->syncListings();

            $this->info('ITAD listing sync completed.');
            $this->line('Resolved: '.$stats['resolved']);
            $this->line('Missing: '.$stats['missing']);
            $this->line('Listings created: '.$stats['listings_created']);
            $this->line('Listings updated: '.$stats['listings_updated']);
            $this->line('Failed: '.$stats['failed']);

            $output = implode(PHP_EOL, [
                'Resolved: '.$stats['resolved'],
                'Missing: '.$stats['missing'],
                'Listings created: '.$stats['listings_created'],
                'Listings updated: '.$stats['listings_updated'],
                'Failed: '.$stats['failed'],
            ]);

            ItadSyncLog::query()->create([
                'sync_type' => 'listings',
                'status' => 'success',
                'command' => $this->signature,
                'stores_total' => $this->statsService->enabledStoresCount(),
                'games_total' => $this->statsService->activeGamesCount(),
                'output' => $output,
                'started_at' => $startedAt,
                'finished_at' => Carbon::now(),
            ]);

            Log::info('ITAD listing sync completed.', [
                'command' => $this->signature,
                'stats' => $stats,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            ItadSyncLog::query()->create([
                'sync_type' => 'listings',
                'status' => 'failed',
                'command' => $this->signature,
                'stores_total' => $this->statsService->enabledStoresCount(),
                'games_total' => $this->statsService->activeGamesCount(),
                'output' => $exception->getMessage(),
                'started_at' => $startedAt,
                'finished_at' => Carbon::now(),
            ]);

            Log::error('ITAD listing sync failed.', [
                'command' => $this->signature,
                'error' => $exception->getMessage(),
            ]);

            $this->error('ITAD listing sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
