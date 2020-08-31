<?php

namespace App\Console\Commands;

use App\Service\AMQP\AMQPServer;
use Illuminate\Console\Command;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class MqttProducer extends Command
{
    private $rabbit;
    protected $signature = 'producer';
    protected $description = 'Command description';
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->connect();
        $message = '测试消息'.time();
        $this->rabbit->sendMessageToServer($message);
    }


    /**
     * 实例化rabbitMQ
     */
    public function connect(){
        if(!$this->rabbit){
            $mq = config('amqp');
            $this->rabbit = new AMQPServer(
                $mq['host'],
                $mq['port'],
                $mq['user'],
                $mq['password'],
                "/",
                $mq['topic_routing_key'],
                $mq['topic_exchange_name'],
                '',
                AMQPExchangeType::TOPIC
            );
        }
    }
}
