<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPHeartbeatMissedException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

class AmqpServer
{
    /**
     * @var AMQPStreamConnection $connect
     */
    protected $connect;
    /** @var AMQPChannel $this ->channel */
    protected $channel;
    private $queue_name;
    private $temp_queue_name;
    private $exchange_name;
    private $temp_exchange_name;
    private $routing_key;
    private $host;
    private $port;
    private $user;
    private $password;
    private $vhost;
    private $exchange_type;

    public function __construct(
        $host,
        $port = 5672,
        $user = 'guest',
        $password = 'guest',
        $vhost = '/',
        $routing_key = 'message',
        $exchange_name = '',
        $queue_name = '',
        $exchange_type = AMQPExchangeType::DIRECT
    )
    {
        $this->exchange_type = $exchange_type;
        $this->queue_name = $queue_name;
        $this->temp_queue_name = $queue_name;
        $this->exchange_name = $exchange_name;
        $this->temp_exchange_name = $exchange_name;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->routing_key = $routing_key;
        $this->connectServer($host, $port, $user, $password, $vhost);
    }

    public function connectServer($host, $port, $user, $password, $vhost)
    {
        $this->connect = new AMQPStreamConnection(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            $insist = false,
            $login_method = 'AMQPLAIN',
            $login_response = null,
            $locale = 'en_US',
            $connection_timeout = 3.0,
            $read_write_timeout = 130.0,
            $context = null,
            $keepalive = true
        );

        $this->connectChannel();
    }

    public function connectChannel()
    {
        //设置通道
        $this->channel = $this->connect->channel();
        $this->initRoutingKey($this->queue_name, $this->exchange_name, $this->routing_key);
    }

    //反序列化的类
    public function sendMessageToServer($msg, $routing_key = '')
    {
        $message = new AMQPMessage($msg, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        if($routing_key){
            $this->routing_key = $routing_key;
        }
        try {
            $this->channel->basic_publish($message, $this->exchange_name, $this->routing_key);
        } catch (\Exception $exception) {
            unset($this->connect);
            $this->connectServer($this->host, $this->port, $this->user, $this->password, $this->vhost);
            $this->channel->basic_publish($message, $this->exchange_name, $this->routing_key);
        }
    }

    public function heartBeats()
    {
        $this->connect->getIO()->read(0);
    }

    public function reconnectServer()
    {
        try {
            $this->connect->getIO()->check_heartbeat();
        } catch (AMQPHeartbeatMissedException $exception) {
            //重新连接
            Log::channel("amqp")->error("\t 重连  message" . $exception->getMessage());
            $this->connect->getIO()->connect();
        }

    }

    public function modifyQueue($queue = '', $exchange = '')
    {
        $this->queue_name = $this->temp_queue_name;
        $this->exchange_name = $this->temp_exchange_name;
        if ($queue != '') {
            $this->queue_name = $queue;
        }
        if ($exchange != '') {
            $this->exchange_name = $exchange;
        }
        $this->connectChannel();
    }

    public function initRoutingKey($queue_name, $exchange_name, $routing_key)
    {
        /** @var AMQPChannel $this ->channel */
        switch ($this->exchange_type) {
            case AMQPExchangeType::DIRECT :
                $this->channel->queue_declare($queue_name, false, true, false, false);
                $this->channel->exchange_declare($exchange_name, AMQPExchangeType::DIRECT, false, true, false);
                $this->channel->queue_bind($queue_name, $exchange_name, $routing_key);
                break;
            case AMQPExchangeType::TOPIC :
                $this->channel->exchange_declare($exchange_name, AMQPExchangeType::TOPIC, false, true, false);
                break;
        }
    }
}
