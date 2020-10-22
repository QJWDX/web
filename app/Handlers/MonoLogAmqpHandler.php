<?php


namespace App\Handlers;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class MonoLogAmqpHandler
 * @package App\Service
 */
class MonoLogAmqpHandler extends AbstractProcessingHandler
{
    protected $channel;

    protected function channel(): AMQPChannel
    {
        if (!$this->channel) {
            $this->channel = $this->connection->channel();
        }
        switch ($this->exchangeType) {
            case AMQPExchangeType::DIRECT :
                $this->channel->queue_declare($this->queueName, false, true, false, false);
                $this->channel->exchange_declare($this->exchangeName, AMQPExchangeType::DIRECT, false, true, false);
                $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
                break;
            case AMQPExchangeType::TOPIC :
                $this->channel->exchange_declare($this->exchangeName, AMQPExchangeType::TOPIC, false, true, false);
                break;
        }

        return $this->channel;
    }


    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $exchangeName;

    protected $exchangeType;

    protected $queueName;

    protected $routingKey;

    /**
     * @param array $amqpConfig amqp config
     * @param string $exchangeName
     * @param int $level
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */

    /**
     * MonoLogAmqpHandler constructor.
     * @param $amqpConfig
     * @param string $exchangeName
     * @param string $queueName
     * @param string $exchangeType
     * @param int $level
     * @param bool $bubble
     * @throws \Exception
     */
    public function __construct(
        $amqpConfig,
        $exchangeName = 'log',
        $queueName = 'log',
        $exchangeType = AMQPExchangeType::DIRECT,
        $level = Logger::DEBUG,
        $bubble = true
    )
    {
        $this->connection = new AMQPLazyConnection(
            $amqpConfig['host'],
            $amqpConfig['port'],
            $amqpConfig['user'],
            $amqpConfig['password'],
            $amqpConfig['vhost']
        );

        $this->exchangeName = $exchangeName;
        $this->queueName = $queueName;
        $this->exchangeType = $exchangeType;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $data = $record["formatted"];
        $this->routingKey = $this->getRoutingKey($record);
        $this->channel()->basic_publish(
            $this->createAmqpMessage($data),
            $this->exchangeName,
            $this->routingKey
        );
    }

    /**
     * {@inheritDoc}
     */
    public function handleBatch(array $records)
    {
        foreach ($records as $record) {
            if (!$this->isHandling($record)) {
                continue;
            }
            $record = $this->processRecord($record);
            $data = $this->getFormatter()->format($record);
            $this->routingKey = $this->getRoutingKey($record);
            $this->channel()->batch_basic_publish(
                $this->createAmqpMessage($data),
                $this->exchangeName,
                $this->routingKey
            );
        }

        $this->channel()->publish_batch();
    }

    /**
     * Gets the routing key for the AMQP exchange
     *
     * @param array $record
     * @return string
     */
    protected function getRoutingKey(array $record)
    {
        //自定义routing key
        if (isset($record['context']['routingKey'])) {
            $routingKey = $record['context']['routingKey'];
            unset($record['context']['routingKey']);
        } else {
            $routingKey = sprintf(
                '%s.%s',
                // TODO 2.0 remove substr call
                substr($record['level_name'], 0, 4),
                $record['channel']
            );
        }
        return strtolower($routingKey);
    }

    /**
     * @param string $data
     * @return AMQPMessage
     */
    private function createAmqpMessage($data)
    {
        return new AMQPMessage(
            (string)$data,
            array(
                'delivery_mode' => 2,
                'content_type' => 'application/json',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, false);
    }
}
