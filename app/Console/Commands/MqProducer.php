<?php

namespace App\Console\Commands;
use App\Service\AMQP\AMQPServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class mqProducer extends Command
{

    protected $signature = 'mq_producer_test';

    protected $description = '消息队列生产者测试';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        try {
            $config = config('amqp');
            $connectConfig = $config['config'];
            $queue = $config['queue'];
            $exchange = $config['exchange'];
            $exchangeType = $config['exchange_type'];
            // rabbitmq_web_mqtt 前端接受收时
//            $queue = '';
//            $exchange = 'amq.topic';
//            $exchangeType = 'topic';
            $routingKey = $config['routing_key'];
            $connect = $this->connect($queue, $exchange, $exchangeType, $routingKey, $connectConfig);
            $data = array(
                'message' => '这是一条测试信息',
                'date' => date('Y-m-d H:i:s')
            );
            $message = json_encode($data);
            $n = 0;
            while ($n < 10){
                $connect->sendMessageToServer($message);
                $n++;
                print_r('发送了'.$n."条\n");
            }
        } catch (\Exception $exception){
            print_r("error:" . $exception->getMessage() . "\n");
        }
    }

    public function connect($queue, $exchange, $exchangeType, $routingKey, $config = array()){
        return new AMQPServer(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost'],
            $routingKey,
            $exchange,
            $queue,
            $exchangeType
        );
    }
}
