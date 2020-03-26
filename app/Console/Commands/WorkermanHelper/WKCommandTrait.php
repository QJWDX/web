<?php
/**
 * Created by PhpStorm.
 * User: SuHuayao
 * Date: 2019/10/31
 * Time: 15:35
 */

namespace App\Console\Commands\WorkermanHelper;


use Workerman\Worker;

trait WKCommandTrait
{


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->signature .= " {action} {option?}";
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    /**
     * @throws \Exception
     */
    public function handle()
    {
        global $argv;
        $arg = $this->argument('action');
        $argv[1] = $argv[2];
        $argv[2] = (isset($argv[3]) && !empty($argv[3])) ? "-{$argv[3]}" : '';
        switch ($arg) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                break;
            case 'restart':
                break;
            case 'reload':
                break;
            case 'status':
                break;
            case 'connections':
                break;
        }

        Worker::$pidFile = storage_path(str_replace("\\", '-',class_basename($this) . ".pid"));
        Worker::runAll();
    }

    protected $worker;

    protected function start()
    {
        $this->worker = new Worker();
    }
}
