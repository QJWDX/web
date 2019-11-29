<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer as Timer;
use Workerman\Worker;

class wsClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ws_client';

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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $worker = new Worker();
        $worker->onWorkerStart = function (){
            $con = new AsyncTcpConnection('ws://192.168.128.128:9501');
            $time_interval = 2;
            $con->onConnect = function ($con) use ($time_interval) {
                Timer::add($time_interval, function() use ($con){
                    $message = array(
                        'msg_sn' => 66,
                        'msg_id' => 256,
                        'bus_id' => -1
                    );
                    $params = json_encode($message);
                    $con->send($params);
                });
            };
            $con->onMessage = function ($con, $data){
                echo $data."\n";
            };
            $con->connect();
        };
        Worker::runAll();
    }
}
