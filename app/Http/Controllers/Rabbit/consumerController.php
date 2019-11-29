<?php


namespace App\Http\Controllers\Rabbit;


use App\Http\Controllers\Controller;
use App\Models\Rabbit\User;
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
                print_r($message->body."\n");
                $data = json_decode($message->body, true);
                static::handleData($data, ['username']);
            }
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }catch (\Exception $exception){
            Log::channel('mq')->error($exception->getMessage());
        }
    }


    /**
     * 更新或插入数据库
     * @param $data
     * @param array $attributeList 属性列表
     */
    public static function handleData($data,$attributeList = array()){
        try{
            $user = new User();
            $attribute = array();
            if($attributeList){
                foreach ($attributeList as $v){
                    $attribute[$v] = $data[$v];
                }
            }
           $user->newQuery()->updateOrCreate($attribute,$data);
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
        }
    }
}
