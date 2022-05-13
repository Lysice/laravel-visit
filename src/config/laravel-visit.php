<?php

return [
    'host' => env('LV_HOST'),
    'database' => env('LV_DATABASE'),
    'port' => env('LV_PORT'),
    'password' => env('LV_PASSWORD'),

    'view_key' => env('LV_VIEW_KEY', 'view_key_'),
    'view_key_limit_count' => env('LV_VIEW_KEY_LIMIT_COUNT', 10000),
    'view_key_limit_prob' => env('LV_VIEW_KEY_LIMIT_PROB', 0.8),
    'type' => [
        // example:
//        [
//            'prefix' => 's:',
//            'type' => 'topic'
//        ]
    ],

    // mode support:db mode or request mode
    'mode' => 'db',
    'table' => env('LV_TABLE', ''),
];
