<?php

namespace App\Console\Commands;

use App\Console\Commands\WorkermanHelper\WKCommandTrait;
use App\Service\AsyncProcess\Process;
use Illuminate\Console\Command;
use Workerman\Lib\Timer;
use Workerman\Worker;
class ConnectionDiagnostics extends Command
{
    use WKCommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnosis:ip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '路段网关配置诊断功能';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function start()
    {
        define("KEEPALIVE", 120);
        define("AUTO_STOP", false);
        date_default_timezone_set("PRC");
        $worker = new Worker("websocket://0.0.0.0:9090");
        $worker->onConnect = function ($connection) use($worker) {
            $connection->onWebSocketConnect = function ($connection, $http_header) use ($worker){
                $connections = count($worker->connections);
                if($connections > 10){
                    $this->closeConnection($connection);
                    return;
                }
                $check = true;
                $error_message = '';
//                if($_SERVER['HTTP_ORIGIN'] != '')
//                {
//                    $check = false;
//                    $error_message = 'Not a legitimate connection source';
//                }
                $parse = parse_url($_SERVER["REQUEST_URI"]);
                $path = explode("/",  $parse['path']);
                $diagnosis_type = strtolower($path[count($path)-1]);
                // 诊断类型不在其中 直接关闭
                if(!in_array($diagnosis_type, ['ping', 'tcp', 'tracert'])){
                    $this->closeConnection($connection);
                    return;
                }
                $this->sendMessage($connection,[
                    "type" => "connected",
                    "diagnosis_type" =>  $diagnosis_type,
                    'data' => "Successful connection waiting for test ..."
                ]);
                $ip = isset($_GET['ip']) ? $_GET['ip'] : "";
                $port = isset($_GET['port']) ? $_GET['port'] : "";
                if(!filter_var($ip, FILTER_VALIDATE_IP) || in_array('127', explode('.',$ip)) || $ip == '0.0.0.0'){
                    $check = false;
                    $error_message = "Is not a valid ip";
                }
                if($port){
                    if(!is_numeric($port) || $port > 65530 || $port < 0){
                        $check = false;
                        $error_message = "Port must be numeric and range from 0-65530";
                    }
                }
                switch ($check){
                    case true:
                        $command = array();
                        switch ($diagnosis_type){
                            case 'ping':
                                $command = ['ping', '-c', '10', $ip];
                                break;
                            case 'tcp':
                                $command = ['telnet', $ip, $port];
//                                $command = ['tcping' '-d' '-w' '3', '-x', '5', $ip, $port];
                                break;
                            case 'tracert':
                                $command = ['tracepath', $ip];
                                break;
                        }
                        $process = $this->runCommand($command, function ($type, $buffer) use ($connection, $diagnosis_type) {
                            $this->sendMessage($connection, [
                                "diagnosis_type" => $diagnosis_type,
                                "type" => Process::ERR === $type ? "error" : "message",
                                "data" => $buffer
                            ]);
                        }, function () use ($connection, $diagnosis_type) {
                            $this->sendMessage($connection, [
                                "diagnosis_type" => $diagnosis_type,
                                "type" => "end",
                                'data' => "END\n"
                            ]);
                            $connection->close();
                        });
                        $connection->process = $process;
                        $connection->onClose = function ($con) {
                            if (isset($con->process)) {
                                $con->process->stop();
                            }
                        };
                        break;
                    case false:
                        $this->sendMessage($connection, [
                            "diagnosis_type" => $diagnosis_type,
                            "type" => "error",
                            "data" => $error_message."\n"
                        ]);
                        $this->sendMessage($connection, [
                            "diagnosis_type" => $diagnosis_type,
                            "type" => "end",
                            'data' => "END\n"
                        ]);
                        $this->closeConnection($connection);
                        break;
                }
            };
        };
    }

    // 执行命令
    public function runCommand($command, $messageCallback, $endCallback)
    {
        $process = new Process($command, null, getenv());
        $process->start();
        $process->waitAsync(function ($type, $buffer) use ($messageCallback) {
            if (Process::ERR === $type) {
                echo ' ERR > ' . $buffer;
            } else {
                echo ' OUT > ' . $buffer;
            }
            is_callable($messageCallback) && $messageCallback($type, $buffer);
        }, function () use ($command, $endCallback) {
            $command = implode(' ', $command);
            echo "Command: \"$command\" END.\n";
            is_callable($endCallback) && $endCallback();
        });
        return $process;
    }

    // 发送消息
    public function sendMessage($connection, $data = array()){
        $connection->send(json_encode($data));
    }

    //  关闭连接
    public function closeConnection($connection){
        Timer::add(1,function($connection){
            $connection->close();
        },[$connection], false);
    }
}
