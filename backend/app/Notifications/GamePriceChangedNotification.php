<?php

namespace App\Notifications;

use App\Models\Game;
use App\Models\GameStoreListing;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GamePriceChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Game $game,
        private readonly GameStoreListing $listing,
        private readonly ?string $previousPrice,
        private readonly string $currentPrice,
        private readonly ?string $originalPrice,
        private readonly ?int $discountPercent,
        private readonly bool $isOnSale,
        private readonly CarbonImmutable $changedAt,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $gameName = $this->game->name;
        $storeName = $this->listing->store?->name ?? 'a store';
        $formattedPrice = number_format((float) $this->currentPrice, 2, '.', '');
        $formattedPrevious = $this->previousPrice !== null
            ? number_format((float) $this->previousPrice, 2, '.', '')
            : null;
        $message = $formattedPrevious !== null
            ? sprintf('%s changed at %s from €%s to €%s.', $gameName, $storeName, $formattedPrevious, $formattedPrice)
            : sprintf('%s is now €%s at %s.', $gameName, $formattedPrice, $storeName);

        return [
            'type' => 'price_changed',
            'title' => $gameName . ' price changed',
            'message' => $message,
            'game' => [
                'id' => $this->game->id,
                'name' => $this->game->name,
                'slug' => $this->game->slug,
            ],
            'store' => [
                'id' => $this->listing->store?->id,
                'code' => $this->listing->store?->code,
                'name' => $this->listing->store?->name,
            ],
            'price' => $this->currentPrice,
            'previous_price' => $this->previousPrice,
            'original_price' => $this->originalPrice,
            'discount_percent' => $this->discountPercent,
            'is_on_sale' => $this->isOnSale,
            'changed_at' => $this->changedAt->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
