<?php

namespace App\Console\Commands;

use App\Service\AmqpServer;
use Illuminate\Console\Command;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class mqttServer extends Command
{
    private $config;
    private $connection;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt';

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
        $this->config = config('mqtt');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!$this->connection instanceof AmqpServer){
            $this->connect();
        }
        $n = 1;
        while (true){
            $msg = array(
                'id' => $n,
                'name' => '123'
            );
            $n += 1;
            $message = json_encode($msg);
            $this->connection->sendMessageToServer($message, $this->config['routing_key']);
            sleep(2);
        }
    }


    public function connect(){
        $this->connection = new AmqpServer(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
            $this->config['routing_key'],
            $this->config['exchange'],
            $this->config['queue'],
            AMQPExchangeType::TOPIC
        );
    }
}
