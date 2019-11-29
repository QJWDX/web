<?php

namespace App\Service;

use App\Models\Base\Line;
use App\Models\Base\LinePlatform;
use App\Models\Base\Vehicle;
use App\Models\Base\VehicleOperational;
use App\Models\Base\VehicleRealtimeData;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Exception\AMQPNoDataException;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class AmqpConsumer
{
    protected $connect;
    protected $channel;
    protected $exchange;
    protected $queue;
    private $port;
    private $user;
    private $password;
    private $vhost;
    private $host;
    const WAIT_BEFORE_RECONNECT_uS = 1000000;

    public function __construct($host, $port, $user, $password, $vhost = '/', $queue, $exchange)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->queue = $queue;
        $this->exchange = $exchange;
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

    public function cleanup_connection()
    {
        // Connection might already be closed.
        // Ignoring exceptions.
        try {
            if ($this->connect !== null) {
                $this->connect->close();
            }
        } catch (\ErrorException $e) {
        }
    }


    public function connectServer()
    {
        $this->connect = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost
        );
    }

    public function connectChannel()
    {
        //设置通道
        /** @var AMQPChannel $this ->channel */
        $this->channel = $this->connect->channel();

        $this->channel->queue_declare($this->queue, false, true, false, false);

        $this->channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);

        $this->channel->queue_bind($this->queue, $this->exchange);

        $this->channel->basic_qos(null, 200, null);

        $this->channel->basic_consume($this->queue, "subway_consumer", false, false, false, false, ['App\Http\Controllers\rabbit\consumerController', 'process_message']);

        while ($this->channel->is_consuming()) {
            try {
                $this->channel->wait();
            } catch (\Error $e) {
                echo $e->getMessage();
                unset($this->connect);
                $this->init();
            }
        }
    }

    public static function shutdown($channel, $connection)
    {
        $channel->close();
        $connection->close();
    }

}
