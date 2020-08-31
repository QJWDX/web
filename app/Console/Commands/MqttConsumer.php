<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MqttConsumer extends Command
{
    protected $signature = 'consumer';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $mq = config('amqp');
        $con = new AMQPStreamConnection('192.168.1.185', '5672', $mq['user'], $mq['password']);
        $channel =  $con->channel();
        list($queue_name,,) = $channel->queue_declare('', false, true, false, false);
        $channel->exchange_declare('amq.topic', 'topic', false, true, false);
        $channel->queue_bind($queue_name, 'amq.topic', $mq['topic_routing_key']);
        $callback = function ($msg){
            echo $msg->body."\n";
        };
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        while (count($channel->callbacks)){
            $channel->wait();
        }
        $channel->close();
        $con->close();
    }
}
