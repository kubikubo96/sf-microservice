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
        'routes' => [
            '/micro-b' => [
                'method' => 'resource',
                'action' => 'DemoController',
                'auth' => false
            ],
        ],
        'rpc' => [
            'key' => 'micro_b_rpc',
            'queue' => 'micro_b_rpc',
            'exchange' => 'micro_b_rpc'
        ],
        'queue' => 'micro_work_queue'
    ],
];
