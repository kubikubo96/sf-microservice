<?php

return [
    // rabbitmq connection
    'connection' => [
        'host' => env('RABBITMQ_HOST', 'localhost'),
        'port' => env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
        'consumer_tag' => env('RABBITMQ_CONSUMER_TAG', 'consumer'),
    ],

    // rabbitmq service
    'micro' => [
        'rpc' => [
            'key' => 'key_micro_rpc',
            'queue' => 'queue_micro_rpc',
            'exchange' => 'exchange_micro_rpc'
        ],
        'wk' => 'wq_micro_work_queue',
        'ps' => [
            'exchange' => 'ps_exchange_micro_notification',
            'queue' => 'ps_queue_micro_notification',
        ],
    ],
];
