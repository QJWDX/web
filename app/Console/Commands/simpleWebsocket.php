<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use WebSocket\Client;

class simpleWebsocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '简易版webscoket客服端';

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
     * @throws \WebSocket\BadOpcodeException
     */
    public function handle()
    {
        $url = 'ws://120.79.71.105:9501';
        $client = new Client($url);
        $client->send('hello');
        $data = $client->receive();
        var_dump($data);
    }
}
