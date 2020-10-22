<?php


namespace App\Http\Controllers\Queue;


use App\Http\Controllers\Controller;
use PhpAmqpLib\Channel\AMQPChannel;

class CallbackController extends Controller
{
    /**
     * 默认的队列消费者回调函数
     * @param $message
     */
    public static function default($message){
        print("start\r\n");
        if($message->body){
            $body = $message->body;
            var_dump($body);
        }
        /** @var AMQPChannel $channel*/
        $channel = $message->delivery_info['channel'];
        $channel->basic_ack($message->delivery_info['delivery_tag']);
        print("end\r\n");
    }

    /**
     * sql日志
     * @param $message
     */
    public static function handSqlLog($message){
        if($message->body){
            try {
                $body = json_decode($message->body, true);
                if(isset($body['context']) && !empty($body['context'])){
                    dd($body['context']);
                }
            }catch (\Exception $exception){
                print("error:".$exception->getMessage()."\r\n");
            }
        }
        /** @var AMQPChannel $channel*/
        $channel = $message->delivery_info['channel'];
        $channel->basic_ack($message->delivery_info['delivery_tag']);
    }
}
