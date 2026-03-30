<?php

namespace App\Services\Stores;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ItadClient
{
    private const API_BASE_URL = 'https://api.isthereanydeal.com';

    /**
     * @return array<string, string|null>
     */
    public function lookupGameIdsByTitle(array $titles): array
    {
        if (count($titles) === 0) {
            return [];
        }

        $response = $this->request()
            ->post('/lookup/id/title/v1', array_values($titles));

        if (! $response->ok()) {
            $this->logFailure('lookupGameIdsByTitle', $response, ['titles' => count($titles)]);
            return [];
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            $this->logInvalidPayload('lookupGameIdsByTitle', $payload, ['titles' => count($titles)]);
            return [];
        }

        /** @var array<string, string|null> $payload */
        return $payload;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function lookupShopIdsByGameIds(int $shopId, array $itadIds): array
    {
        if ($shopId <= 0 || count($itadIds) === 0) {
            return [];
        }

        $response = $this->request()
            ->post("/lookup/shop/{$shopId}/id/v1", array_values($itadIds));

        if (! $response->ok()) {
            $this->logFailure('lookupShopIdsByGameIds', $response, ['shop_id' => $shopId, 'itad_ids' => count($itadIds)]);
            return [];
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            $this->logInvalidPayload('lookupShopIdsByGameIds', $payload, ['shop_id' => $shopId, 'itad_ids' => count($itadIds)]);
            return [];
        }

        /** @var array<string, array<int, string>> $payload */
        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchPrices(array $itadIds, array $shopIds, ?string $country): array
    {
        if (count($itadIds) === 0 || count($shopIds) === 0) {
            return [];
        }

        $query = [
            'country' => $country,
            'shops' => implode(',', Arr::where($shopIds, fn ($id) => $id !== null && $id !== '')),
        ];

        $response = $this->request()
            ->withQueryParameters(array_filter($query))
            ->post('/games/prices/v3', array_values($itadIds));

        if (! $response->ok()) {
            $this->logFailure('fetchPrices', $response, ['itad_ids' => count($itadIds), 'shop_ids' => count($shopIds)]);
            return [];
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            $this->logInvalidPayload('fetchPrices', $payload, ['itad_ids' => count($itadIds), 'shop_ids' => count($shopIds)]);
            return [];
        }

        /** @var array<int, array<string, mixed>> $payload */
        return $payload;
    }

    private function request(): PendingRequest
    {
        $request = Http::timeout(20)
            ->baseUrl(self::API_BASE_URL)
            ->acceptJson();

        $apiKey = config('itad.api_key');
        if (is_string($apiKey) && $apiKey !== '') {
            $request = $request->withQueryParameters(['key' => $apiKey]);
        }

        return $request;
    }

    private function logFailure(string $operation, Response $response, array $context = []): void
    {
        $body = $response->body();
        Log::warning('ITAD request failed.', $context + [
            'operation' => $operation,
            'status' => $response->status(),
            'body' => is_string($body) ? substr($body, 0, 1000) : null,
        ]);
    }

    private function logInvalidPayload(string $operation, mixed $payload, array $context = []): void
    {
        Log::warning('ITAD response payload invalid.', $context + [
            'operation' => $operation,
            'payload_type' => gettype($payload),
        ]);
    }
}
