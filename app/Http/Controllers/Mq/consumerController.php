<?php


namespace App\Http\Controllers\Mq;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class consumerController extends Controller
{

    /**
     * rabbitMQ消息处理
     * @param $message
     */
    public static function process_message($message){
        try{
            if($message->body){
                $data = json_decode($message->body, true);
                print_r($message->body."\n");
            }
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }catch (\Exception $exception){
            Log::channel('mq')->error($exception->getMessage());
        }
    }
}
