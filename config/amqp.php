<?php

return [
    'config' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'admin'),
        'password' => env('AMQP_PWD', 'admin123'),
        'vhost' => env('AMQP_VHOST', '/')
    ],
    'queue' => env('AMQP_QUEUE', 'sql_log'),
    'exchange' => env('AMQP_EXCHANGE', 'logs'),
    'exchange_type' => 'direct',
    'routing_key' => 'sql_log',
    'callback' => ['App\Http\Controllers\Queue\CallbackController', 'default']
];
