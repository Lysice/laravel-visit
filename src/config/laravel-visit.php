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

    /**
     * 接口形式对接
     */
    // 获取浏览量配置
    'getCount' => function ($data) {
        $res = \Wangan\Event\Service\EventService::i()->statistics($data);
        return $res;
    },
    // 同步浏览量配置
    'syncCount' => function ($data) {
        $r = \Wangan\Event\Service\EventService::i()->pushEvent($data);
        return $r;
    },

    // 异步的延迟分钟数 >0 为异步 否则为同步
    'sync-delay' => env('LV_SYNC_DELAY', 0),
];
