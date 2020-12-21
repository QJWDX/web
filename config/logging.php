<?php

use App\Handlers\MonoLogAmqpHandler;
use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'api' => [
            'driver' => 'single',
            'name' => 'api',
            'path' => storage_path('logs/api.log'),
            'level' => 'debug'
        ],

        'slow_sql_log' => [
            'driver' => 'single',
            'name' => 'slow_sql_log',
            'path' => storage_path('logs/slow_sql_log.log'),
            'level' => 'debug'
        ],

        'test_log' => [
            'driver' => 'single',
            'name' => 'test_log',
            'path' => storage_path('logs/test_log.log'),
            'level' => 'debug'
        ],

        'operation_log' => [
            'driver' => 'monolog',
            'name' => 'operation_log',
            'handler' => MonoLogAmqpHandler::class,
            'formatter' => Monolog\Formatter\JsonFormatter::class,
            'with' => [
                'queueName' => env('LOG_AMQP_QUEUE', 'operation_log'),
                'exchangeName' => env('LOG_AMQP_EXCHANGE', 'logs'),
                'exchangeType' => 'direct',
                'amqpConfig' => [
                    'host' => env('LOG_AMQP_HOST', 'localhost'),
                    'port' => env('LOG_AMQP_PORT', 5672),
                    'user' => env('LOG_AMQP_USER', 'admin'),
                    'password' => env('LOG_AMQP_PWD', 'admin123'),
                    'vhost' => env('LOG_AMQP_VHOST', '/')
                ]
            ],
        ],
    ],
];
