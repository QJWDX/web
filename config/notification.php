<?php
return [
    'config' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'admin'),
        'password' => env('AMQP_PWD', 'admin123'),
        'vhost' => env('AMQP_VHOST', '/')
    ],
    'queue' => 'notification',
    'exchange' => 'amq.topic',
    'exchange_type' => 'topic',
    'routing_key' => 'notification',
    'callback' => ['App\Http\Controllers\Queue\CallbackController', 'default'],
    'type' => [
        [
            'name' => '系统消息',
            'class' => \App\Notifications\systemNotification::class
        ]
    ]
];
