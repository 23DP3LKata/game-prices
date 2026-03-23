<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\Stores\SteamPriceSyncService;
use Illuminate\Console\Command;

class SyncSteamPrices extends Command
{
    protected $signature = 'steam:sync-prices {--store=steam}';
    protected $description = 'Sync Steam prices in EUR and store price history.';

    public function handle(SteamPriceSyncService $syncService): int
    {
        $storeCode = (string) $this->option('store');

        $store = Store::query()
            ->where('code', $storeCode)
            ->where('sync_enabled', true)
            ->where('is_active', true)
            ->first();

        if (! $store) {
            $this->error('Store not found or sync disabled: '.$storeCode);
            return self::FAILURE;
        }

        $stats = $syncService->syncStoreListings($store);

        $this->info('Steam sync completed.');
        $this->line('Synced: '.$stats['synced']);
        $this->line('Updated: '.$stats['updated']);
        $this->line('Skipped: '.$stats['skipped']);
        $this->line('Failed: '.$stats['failed']);

        return self::SUCCESS;
    }
}
