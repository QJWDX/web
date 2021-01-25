<?php

namespace App\Console\Commands;
use App\Service\Amqp\AmqpConsumer;
use Illuminate\Console\Command;

class mqConsumer extends Command
{
    protected $signature = 'mq_c';

    protected $description = '队列消费测试';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        print_r("rabbitMQ消费开始 \n");
        $config = config('notification');
        $connectConfig = $config['config'];
        $queue = $config['queue'];
        $exchange = $config['exchange'];
        $exchangeType = $config['exchange_type'];
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
