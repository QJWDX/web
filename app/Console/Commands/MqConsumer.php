<?php

namespace App\Console\Commands;
use App\Service\AMQP\AMQPConsumer;
use Illuminate\Console\Command;

class mqConsumer extends Command
{
    protected $signature = 'mq_consumer_test';

    protected $description = '队列消费测试';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        print_r("rabbitMQ消费开始 \n");
        $config = config('amqp');
        $connectConfig = $config['config'];
        $queue = $config['queue'];
        $exchange = $config['exchange'];
        $exchangeType = $config['exchange_type'];
//        $exchange = 'amq.topic';
//        $exchangeType = 'topic';
        $routingKey = $config['routing_key'];
        $callback = $config['callback'];
        new AMQPConsumer(
            $connectConfig,
            $queue,
            $exchange,
            $routingKey,
            $callback,
            $exchangeType
        );
    }
}
