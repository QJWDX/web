<?php

namespace App\Console\Commands;

use App\Service\Amqp\AmqpConsumer;
use Illuminate\Console\Command;

class storeOperationLog extends Command
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
        new AmqpConsumer(
            $config['config'],
            $config['queue'],
            $config['exchange'],
            $config['routing_key'],
            $config['callback'],
            $config['exchange_type']
        );
    }
}
