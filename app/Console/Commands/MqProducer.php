<?php

namespace App\Console\Commands;
use App\Service\AMQP\AMQPServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class mqProducer extends Command
{
    private $rabbit;

    private $config;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:producer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rabbitmq生产者';

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
        $this->connect();
        $data = array(
            'username' => '小狐仙',
            'password' => md5('123456')
        );
        $message = json_encode($data);
        try {
            $n = 1;
            while (true){
                $this->sendMessage($message);
                echo "生产了第".$n."条信息\n";
                $n += 1;
            }
        } catch (\Exception $exception){
            $this->connect();
            Log::channel('mq')->error($exception->getMessage());
        }
    }

    public function connect(){
        $this->rabbit = new AMQPServer(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
            $this->config['routing_key'],
            $this->config['exchange'],
            $this->config['queue']
        );
    }

    public function sendMessage($message){
        $this->rabbit->sendMessageToServer($message, $this->config['routing_key']);
    }
}
