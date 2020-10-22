<?php

return [
    'config' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'admin'),
        'password' => env('AMQP_PWD', 'admin123'),
        'vhost' => env('AMQP_VHOST', '/')
    ],
    'queue' => env('AMQP_QUEUE', 'message'),
    'exchange' => env('AMQP_EXCHANGE', 'message'),
    'exchange_type' => 'direct',
    'routing_key' => 'message',
    'callback' => ['App\Http\Controllers\Queue\CallbackController', 'default']
];
