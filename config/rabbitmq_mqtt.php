<?php
return [
    'host' => env("MQ_HOST", '120.79.71.105'),
    'port' => env('MQ_PORT', '5672'),
    'user' => env('MQ_USER', 'guest'),
    'password' => env('MO_PASSWORD', 'guest'),
    'exchange' => env('EXCHANGE', 'amq.topic'),
    'queue' => env("QUEUE", ''),
    'routing_key' => env("ROUTING_KEY", 'World'),
    'vhost' => env("VHOST", '/'),
];
