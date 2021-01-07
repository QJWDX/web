<?php

namespace App\Console\Commands;
use App\Service\Amqp\AmqpConsumer;
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
//        rabbitmq_web_mqtt 前端接受收时
//        $queue = '';
//        $exchange = 'amq.topic';
//        $exchangeType = 'topic';
        $routingKey = $config['routing_key'];
        $callback = $config['callback'];
        new AmqpConsumer(
            $connectConfig,
            $queue,
            $exchange,
            $routingKey,
            $callback,
            $exchangeType
        );
    }
}
