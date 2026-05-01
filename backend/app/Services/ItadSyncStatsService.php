<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Store;

class ItadSyncStatsService
{
    public function enabledStoresCount(): int
    {
        return Store::query()
            ->where('is_active', true)
            ->where('sync_enabled', true)
            ->whereNotNull('itad_shop_id')
            ->count();
    }

    public function activeGamesCount(): int
    {
        return Game::query()
            ->where('is_active', true)
            ->count();
    }
}
