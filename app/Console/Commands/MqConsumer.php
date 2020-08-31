<?php

namespace App\Console\Commands;
use App\Service\AMQP\AMQPConsumer;
use Illuminate\Console\Command;

class mqConsumer extends Command
{
    private $consumer;
    private $config;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rabbitmq消费者';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = config('amqp');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "rabbitMQ消费开始\n";
        $this->consumerConnect();
        echo "rabbitMQ消费结束\n";
    }

    /**
     * rabbitMQ消费者
     */
    public function consumerConnect(){
        $this->consumer = new AMQPConsumer(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
            $this->config['queue'],
            $this->config['exchange']
        );
    }
}
