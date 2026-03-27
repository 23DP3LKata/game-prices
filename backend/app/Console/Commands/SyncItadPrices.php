<?php

namespace App\Console\Commands;

use App\Services\Stores\ItadPriceSyncService;
use Illuminate\Console\Command;

class SyncItadPrices extends Command
{
    protected $signature = 'itad:sync-prices';
    protected $description = 'Sync ITAD prices for enabled stores.';

    public function handle(ItadPriceSyncService $syncService): int
    {
        $stats = $syncService->syncPrices();

        $this->info('ITAD price sync completed.');
        $this->line('Synced: '.$stats['synced']);
        $this->line('Updated: '.$stats['updated']);
        $this->line('Skipped: '.$stats['skipped']);
        $this->line('Failed: '.$stats['failed']);

        return self::SUCCESS;
    }
}
