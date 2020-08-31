<?php

return [
    'queue' => env("QUEUE_NAME", 'test_queue'),
    'exchange' => env('EXCHANGE_NAME', 'test_exchange'),
    'host' => env("MQ_HOST", '192.168.1.185'),
    'port' => env('MQ_PORT', '5672'),
    'user' => env('MQ_USER', 'test'),
    'password' => env('MO_PASSWORD', 'test123'),
    'routing_key' => env("ROUTING_KEY", 'test'),
    'topic_exchange_name' => env("TOPIC_EXCHANGE_NAME", 'amq.topic'),
    'topic_routing_key' => env("TOPIC_ROUTING_KEY", 'test_mqtt'),
    'vhost' => env("VHOST", '/'),
];
