<?php


namespace App\Console\Commands;


use App\Models\Log\OperationLog;
use Illuminate\Console\Command;

class createOperationLogTable extends Command
{
    protected $signature = 'createOperationLogTable';

    protected $description = '自动创建操作日志表';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){
        $log = new OperationLog();
        $log->createTable();
        print_r("[".date('Y-m-d H:i:s')."]自动创建操作日志表执行成功\n");
    }
}
