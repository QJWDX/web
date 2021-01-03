<?php

namespace App\Console\Commands;

use App\Service\AMQP\AMQPConsumer;
use Illuminate\Console\Command;

class StoreOperationLog extends Command
{
    protected $signature = 'store_op_log';

    protected $description = '消息队列中用户操作日志保存';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        print_r("操作日志记录开始 \n");
        $config = config('operation_log');
        new AMQPConsumer(
            $config['config'],
            $config['queue'],
            $config['exchange'],
            $config['routing_key'],
            $config['callback'],
            $config['exchange_type']
        );
    }
}
