<?php

namespace App\Service\AMQP;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPHeartbeatMissedException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPServer
{
    /** @var AMQPStreamConnection $connect */
    protected $connect;
    /** @var AMQPChannel $channel */
    protected $channel;
    private $queueName;
    private $tempQueueName;
    private $exchangeName;
    private $tempExchangeName;
    private $routingKey;
    private $host;
    private $port;
    private $user;
    private $password;
    private $vHost;
    protected $exchangeType;

    public function __construct(
        $host,
        $port = 5672,
        $user = 'guest',
        $password = 'guest',
        $vHost = '/',
        $routingKey = 'message',
        $exchangeName = '',
        $queueName = '',
        $exchangeType = AMQPExchangeType::DIRECT
    )
    {
        $this->exchangeType = $exchangeType;
        $this->queueName = $queueName;
        $this->tempQueueName = $queueName;
        $this->exchangeName = $exchangeName;
        $this->tempExchangeName = $exchangeName;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vHost = $vHost;
        $this->routingKey = $routingKey;
        $this->connectServer($host, $port, $user, $password, $vHost);
    }

    public function connectServer($host, $port, $user, $password, $vHost)
    {
        $this->connect = new AMQPStreamConnection(
            $host,
            $port,
            $user,
            $password,
            $vHost,
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
        // 设置通道
        $this->channel = $this->connect->channel();
        // 声明队列
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        // 声明交换器
        $this->channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);
        // 绑定队列到交换器
        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
    }

    /**
     * 发布消息
     * @param $msg
     */
    public function sendMessageToServer($msg)
    {
        $message = new AMQPMessage($msg, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        try {
            $this->channel->basic_publish($message, $this->exchangeName, $this->routingKey);
        } catch (\Exception $exception) {
            unset($this->connect);
            $this->connectServer($this->host, $this->port, $this->user, $this->password, $this->vHost);
            $this->channel->basic_publish($message, $this->exchangeName, $this->routingKey);
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
            Log::channel("amqp")->error("重连错误:" . $exception->getMessage());
            $this->connect->getIO()->connect();
        }
    }


    public function modifyQueue($queue = '', $exchange = '')
    {
        $this->queueName = $this->tempQueueName;
        $this->exchangeName = $this->tempExchangeName;
        if ($queue != '') {
            $this->queueName = $queue;
        }
        if ($exchange != '') {
            $this->exchangeName = $exchange;
        }
        $this->connectChannel();
    }
}
