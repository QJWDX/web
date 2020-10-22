<?php

namespace App\Service\AMQP;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class AMQPConsumer
{
    /**
     * AMQPStreamConnection 构造参数
     * @var array
     */
    protected $connectConfig = array();
    /**
     * @var AMQPStreamConnection $connect
     */
    protected $connect;
    /** @var AMQPChannel $this ->channel */
    protected $channel;
    protected $queueName;
    protected $exchangeName;
    protected $exchangeType;
    protected $routingKey;
    protected $callback = ['App\Http\Controllers\Queue\CallbackController', 'default'];
    protected $exchangeTypeList = array(
        AMQPExchangeType::DIRECT,
        AMQPExchangeType::HEADERS,
        AMQPExchangeType::FANOUT,
        AMQPExchangeType::TOPIC
    );
    const WAIT_BEFORE_RECONNECT_uS = 1000000;
    public function __construct(
        $connectConfig,
        $queueName,
        $exchangeName,
        $routingKey,
        $callback = array(),
        $exchangeType = AMQPExchangeType::DIRECT
    )
    {
        $this->connectConfig = $connectConfig;
        $this->queueName = $queueName;
        $this->exchangeName = $exchangeName;
        $this->routingKey = $routingKey;
        if(!in_array($exchangeType, $this->exchangeTypeList)){
            print_r("exchange type error \t\n");
            return;
        }
        $this->exchangeType = $exchangeType;
        $this->setCallback($callback);
        try {
            $this->init();
        } catch (\Error $e) {
            echo $e->getMessage();
            unset($this->connect);
            $this->init();
        }
    }

    public function init()
    {
        $this->connectServer();
        $this->connectChannel();
    }


    public function connectServer()
    {
        // 获取连接
        $this->connect = new AMQPStreamConnection(
            $this->connectConfig['host'],
            $this->connectConfig['port'],
            $this->connectConfig['user'],
            $this->connectConfig['password'],
            $this->connectConfig['vhost']
        );
    }

    public function connectChannel()
    {
        // 设置通道
        $this->channel = $this->connect->channel();
        // 队列声明
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        // 交换器声明
        $this->channel->exchange_declare($this->exchangeName, $this->exchangeType, false, true, false);
        // 队列绑定到交换器
        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
        // 指定QoS
        $this->channel->basic_qos(null, 200, null);
        // 发布消息
        $this->channel->basic_consume($this->queueName, "amqp_consumer", false, false, false, false, $this->callback);

        while(count($this->channel->callbacks)){
            try {
                $this->channel->wait();
            } catch (\Error $e) {
                echo $e->getMessage();
                unset($this->connect);
                $this->init();
            }
        }
    }

    /**
     * 关闭连接和通道
     * @param $channel
     * @param $connection
     */
    public static function shutdown($channel, $connection)
    {
        if($channel !== null){
            $channel->close();
        }
        if($connection !== null){
            $connection->close();
        }
    }

    /**
     * 关闭连接
     */
    public function close_connection()
    {
        try {
            if ($this->connect !== null) {
                $this->connect->close();
            }
        } catch (\ErrorException $e) {
        }
    }


    /**
     * 设置回调方法
     * @param array $callback
     */
    public function setCallback($callback = array()){
        if(!$this->checkCallback($callback)){
            print_r("The callback method parameter is wrong, please check \r\n");
            return;
        }
        $this->callback = $callback;
    }

    /**
     * 检查回调方法是否存在
     * @param array $callback
     * @return bool
     */
    public function checkCallback($callback = array()){
        $bool = true;
        if(empty($callback) || count($callback) != 2){
            $bool = false;
        }
        if(!class_exists($callback[0])){
            $bool = false;
        }
        if(!method_exists($callback[0],$callback[1])){
            $bool = false;
        }
        return $bool;
    }

}
