<?php
return [
    'config' => [
        'host' => env('LOG_AMQP_HOST', 'localhost'),
        'port' => env('LOG_AMQP_PORT', 5672),
        'user' => env('LOG_AMQP_USER', 'admin'),
        'password' => env('LOG_AMQP_PWD', 'admin123'),
        'vhost' => env('LOG_AMQP_VHOST', '/')
    ],
    'queue' => 'operation_log',
    'exchange' => env('LOG_AMQP_EXCHANGE', 'logs'),
    'exchange_type' => 'direct',
    'routing_key' => 'sql_log',
    'callback' => ['App\Http\Controllers\Queue\CallbackController', 'handOperationLog']
];
