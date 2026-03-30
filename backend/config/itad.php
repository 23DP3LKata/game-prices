<?php

return [
    'api_key' => env('ITAD_API_KEY'),
    'country' => env('ITAD_COUNTRY', 'DE'),
    'currency' => 'EUR',
    'shops' => [
        'steam' => env('ITAD_SHOP_STEAM', 61),
        'epic' => env('ITAD_SHOP_EPIC', 16),
        'microsoft_store' => env('ITAD_SHOP_MICROSOFT_STORE', 48),
        '2game' => env('ITAD_SHOP_2GAME', 19),
        'greenmangaming' => env('ITAD_SHOP_GREENMANGAMING', 36),
        'planetplay' => env('ITAD_SHOP_PLANETPLAY', 73),
        'gamesplanet' => env('ITAD_SHOP_GAMESPLANET', 27),
    ],
];
