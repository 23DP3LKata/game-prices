<?php

return [
    'api_key' => env('ITAD_API_KEY'),
    'country' => env('ITAD_COUNTRY', 'DE'),
    'currency' => 'EUR',
    'shops' => [
        'steam' => env('ITAD_SHOP_STEAM', 61),
        'epic' => env('ITAD_SHOP_EPIC', 16),
    ],
];
