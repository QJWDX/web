<?php

namespace App\Console\Commands;

use App\Service\RabbitMq\AmqpServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class rabbitMqProducer extends Command
{
    private $rabbit;

    private $config;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'producer';

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
        $this->connect();
        $data = array(
            'username' => '小狐仙',
            'password' => md5('123456'),
            'email' => '1131941069@qq.com',
            'tel' => '18070573141',
            'status' => 1
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
        $this->rabbit = new AmqpServer(
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
