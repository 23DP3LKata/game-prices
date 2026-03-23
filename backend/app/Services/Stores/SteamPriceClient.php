<?php

namespace App\Services\Stores;

use App\Services\Stores\DTO\SteamPriceData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SteamPriceClient
{
    private const API_BASE_URL = 'https://store.steampowered.com/api';

    public function fetchPrice(string $appId): ?SteamPriceData
    {
        $country = config('steam.country');
        $currency = config('steam.currency', 'EUR');
        if ($country === null || $country === '') {
            return null;
        }

        $response = Http::timeout(15)
            ->baseUrl(self::API_BASE_URL)
            ->get('appdetails', [
                'appids' => $appId,
                'cc' => $country,
                'l' => 'en',
            ]);

        if (! $response->ok()) {
            return null;
        }

        $payload = $response->json();
        $entry = $payload[$appId] ?? null;
        if (! is_array($entry) || ! ($entry['success'] ?? false)) {
            return new SteamPriceData(false, false, null, null, null, null);
        }

        $data = $entry['data'] ?? [];
        $title = is_string($data['name'] ?? null) ? $data['name'] : null;
        $isFree = (bool) ($data['is_free'] ?? false);

        if ($isFree) {
            return new SteamPriceData(true, false, $title, '0.00', '0.00', 0);
        }

        $priceOverview = $data['price_overview'] ?? null;
        if (! is_array($priceOverview)) {
            return new SteamPriceData(false, false, $title, null, null, null);
        }

        $apiCurrency = $priceOverview['currency'] ?? null;
        if (! is_string($apiCurrency) || Str::upper($apiCurrency) !== Str::upper((string) $currency)) {
            return new SteamPriceData(false, false, $title, null, null, null);
        }

        $finalCents = $priceOverview['final'] ?? null;
        $initialCents = $priceOverview['initial'] ?? null;
        $discountPercent = $priceOverview['discount_percent'] ?? null;

        if (! is_int($finalCents) || ! is_int($initialCents)) {
            return new SteamPriceData(false, false, $title, null, null, null);
        }

        $price = $this->formatCents($finalCents);
        $originalPrice = $this->formatCents($initialCents);
        $discount = is_int($discountPercent) ? $discountPercent : null;
        $isOnSale = $discount !== null && $discount > 0;

        return new SteamPriceData(true, $isOnSale, $title, $price, $originalPrice, $discount);
    }

    private function formatCents(int $value): string
    {
        return number_format($value / 100, 2, '.', '');
    }
}
