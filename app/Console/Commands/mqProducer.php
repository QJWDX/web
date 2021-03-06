<?php

namespace App\Console\Commands;
use App\Service\Amqp\AmqpServer;
use Illuminate\Console\Command;

class mqProducer extends Command
{

    protected $signature = 'mq_p';

    protected $description = '消息队列生产者测试';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        try {
            $config = config('notification');
            $connectConfig = $config['config'];
            $queue = $config['queue'];
            $exchange = $config['exchange'];
            $exchangeType = $config['exchange_type'];
            $routingKey = $config['routing_key'].'_user_id_1';
            $connect = $this->connect($queue, $exchange, $exchangeType, $routingKey, $connectConfig);
            $n = 0;
            while ($n < 10){
                $data = array(
                    'title' => '系统信息',
                    'message' => '这是一条测试消息！'.$n
                );
                $message = json_encode($data);
                $connect->sendMessageToServer($message);
                $n++;
                sleep(1);
                print_r('发送了'.$n."条\n");
            }
        } catch (\Exception $exception){
            print_r("error:" . $exception->getMessage() . "\n");
        }
    }

    public function connect($queue, $exchange, $exchangeType, $routingKey, $config = array()){
        return new AmqpServer(
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
