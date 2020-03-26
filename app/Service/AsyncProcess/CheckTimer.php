<?php
/**
 * Created by PhpStorm.
 * User: SuHuayao
 * Date: 2019/10/15
 * Time: 18:58
 */

namespace  App\Service\AsyncProcess;
use Workerman\Lib\Timer;

class CheckTimer
{

    static private $asyncCheckCallbackMapper = [];

    static private $timerId;

    /**
     * @var int  检查间隔，s
     */
    static public $checkInterval = 1;


    static protected function timerInit()
    {
        return self::$timerId ?? self::$timerId = Timer::add(self::$checkInterval, function () {
                echo "[" . date("H:i:s") . "]" . " check timer running, count:" . count(self::$asyncCheckCallbackMapper), PHP_EOL;
                foreach (self::$asyncCheckCallbackMapper as $key => $call) {
                    /**
                     * @var $process Process
                     */
                    $process = $call[2];

                    $autoStop = defined("AUTO_STOP") ? AUTO_STOP : false;

                    if($autoStop){
                        echo "[" . date("H:i:s") . "] " . ($process->globalId??"unknown_global_id") . " will stop at: " . date("H:i:s", $process->keepLive), PHP_EOL;
                    }
                    if ($autoStop && $process->keepLive < time()) {
                        //结束进程
                        $process->stop();
                    }
                    try {
                        call_user_func_array($call[0], $call[1]);
                    } catch (\Exception $e) {
                        echo "[" . date("H:i:s") . "]" . " check timer catch error : " . $e->getMessage();
                    }


                }
            });
    }


    public static function addAsyncCheck($obj, $callback, ...$args)
    {
        self::timerInit();
        self::$asyncCheckCallbackMapper[spl_object_id($obj)] = [
            $callback, $args, $obj
        ];
    }

    public static function delAsyncCheck($obj)
    {
        unset(self::$asyncCheckCallbackMapper[is_numeric($obj) ? $obj : spl_object_id($obj)]);
    }

}
