<?php

namespace App\Services\Stores;

use App\Models\Game;
use App\Models\GameMinPrice;
use App\Models\GameStoreListing;
use App\Models\WishlistItem;
use App\Notifications\GamePriceChangedNotification;
use Carbon\CarbonImmutable;

class PriceChangeNotificationService
{
    public function notifyForMinPriceChange(
        Game $game,
        GameStoreListing $listing,
        string $newMinPrice,
        ?string $originalPrice,
        ?int $discountPercent,
        bool $isOnSale,
        CarbonImmutable $now,
    ): void {
        $wishlistItems = WishlistItem::query()
            ->where('game_id', $game->id)
            ->where('notifications_enabled', true)
            ->with('user')
            ->get();

        if ($wishlistItems->isEmpty()) {
            return;
        }

        $previousMinPrice = GameMinPrice::query()
            ->where('game_id', $game->id)
            ->orderByDesc('recorded_at')
            ->skip(1)
            ->first();

        $previousPrice = $previousMinPrice?->price;

        foreach ($wishlistItems as $wishlistItem) {
            $user = $wishlistItem->user;
            if (! $user) {
                continue;
            }

            $user->notify(new GamePriceChangedNotification(
                $game,
                $listing,
                $previousPrice,
                $newMinPrice,
                $originalPrice,
                $discountPercent,
                $isOnSale,
                $now,
            ));

            $wishlistItem->forceFill([
                'last_notified_price' => $newMinPrice,
                'last_notified_at' => $now,
            ])->save();
        }
    }
}
