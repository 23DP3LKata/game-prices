<?php

namespace App\Console\Commands;

use App\Services\Stores\ItadPriceSyncService;
use Illuminate\Console\Command;

class SyncItadListings extends Command
{
    protected $signature = 'itad:sync-listings';
    protected $description = 'Resolve ITAD IDs and create store listings.';

    public function handle(ItadPriceSyncService $syncService): int
    {
        $stats = $syncService->syncListings();

        $this->info('ITAD listing sync completed.');
        $this->line('Resolved: '.$stats['resolved']);
        $this->line('Missing: '.$stats['missing']);
        $this->line('Listings created: '.$stats['listings_created']);
        $this->line('Listings updated: '.$stats['listings_updated']);
        $this->line('Failed: '.$stats['failed']);

        return self::SUCCESS;
    }
}
