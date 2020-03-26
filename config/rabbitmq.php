<?php
return [
    'host' => env("MQ_HOST", '120.79.71.105'),
    'port' => env('MQ_PORT', '5672'),
    'user' => env('MQ_USER', 'admin'),
    'password' => env('MO_PASSWORD', 'admin123456'),
    'exchange' => env('EXCHANGE', 'test_exchange'),
    'queue' => env("QUEUE", 'test_queue'),
    'routing_key' => env("ROUTING_KEY", 'test'),
    'vhost' => env("VHOST", '/'),
];
