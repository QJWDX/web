<?php

namespace App\Console\Commands;

use App\Service\AmqpConsumer;
use Illuminate\Console\Command;

class consumer extends Command
{
    private $consumer;
    private $config;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = config('mq');
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
        $this->consumer = new AmqpConsumer(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
            $this->config['exchange'],
            $this->config['queue']
        );
    }
}
