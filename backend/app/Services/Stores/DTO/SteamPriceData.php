<?php

namespace App\Services\Stores\DTO;

class SteamPriceData
{
    public function __construct(
        public readonly bool $isAvailable,
        public readonly bool $isOnSale,
        public readonly ?string $title,
        public readonly ?string $price,
        public readonly ?string $originalPrice,
        public readonly ?int $discountPercent,
    ) {
    }

    public function hasPrice(): bool
    {
        return $this->price !== null;
    }
}
