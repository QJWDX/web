<?php


namespace App\Http\Controllers\Notifications;


use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Notification\Notifications;
use App\Notifications\systemNotification;
use App\Service\Amqp\AmqpServer;
use Dx\Role\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class NotificationsController extends Controller
{

    /**
     * 获取通知列表
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request, Notifications $notifications){
        $user = Auth::guard('api')->user();
        if(!$user){
            return $this->error(403, '用户未登录');
        }
        $notifiable_id = $user['id'];
        $read_at = $request->get('read_at', 0);
        if(!$notifiable_id){
            return $this->error(500, '通知对象id参数错误');
        }
        $startTime = $request->get('startTime', false);
        $endTime = $request->get('endTime', false);
        $type = $request->get('type', '');
        $where = compact('notifiable_id', 'read_at', 'startTime', 'endTime', 'type');
        $data = $notifications->getNotifications($where);
        $data['items'] = collect($data['items'] )->transform(function ($item){
            $message = json_decode($item['data'], true);
            $item['title'] = $message['title'];
            $item['content'] = $message['content'];
            unset($item['data']);
            return $item;
        });
        if(!$data){
            return $this->error('获取通知列表失败');
        }
        return $this->success($data);
    }

    /**
     * 标记为已读
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeRead(Request $request, Notifications $notifications){
        $ids = $request->get('ids', []);
        if($notifications->makeRead($ids)){
            return $this->success('标记已读成功');
        }
        return $this->error('标记已读失败');
    }


    /**
     * 删除通知
     * @param DelRequest $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function delNotifications(DelRequest $request, Notifications $notifications){
        $ids = $request->get('ids');
        if($notifications->del($ids)){
            return $this->success('删除消息通知成功');
        }
        return $this->error(500, '删除消息通知失败');
    }

    /**
     * 未读已读数量
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationCount(Request $request, Notifications $notifications){
        $user = Auth::guard('api')->user();
        if(!$user){
            return $this->error(403, '用户未登录');
        }
        $notifiable_id = $user['id'];
        $data = $notifications->notificationCount($notifiable_id);
        return $this->success($data);
    }

    /**
     * 消息通知类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationType(){
        $config = config('notification');
        $notificationTypes = $config['type'];
        if(count($notificationTypes) == 0){
            return $this->error('消息类型还未配置！');
        }
        $type = [];
        foreach ($notificationTypes as $key => $val){
            $type[] = [
                'id' => $key,
                'name' => $val['name'],
                'type' => $val['class'],
            ];
        }
        return $this->success($type);
    }

    /**
     * 发送系统通知
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request){
        $type = $request->get('type', 0);
        try {
            $config = config('notification');
            $message = $request->only(['title', 'content']);
            $notificationTypes = $config['type'];
            if(!isset($notificationTypes[$type])){
                return $this->error('消息类型还未配置,请联系管理员！');
            }
            $notificationTypeName = $notificationTypes[$type]['name'];
            $notificationClass = $notificationTypes[$type]['class'];
            $mqMsg = [
                "title" => trim($notificationTypeName),
                "message" => date('Y-m-d H:i:s')."</br>".trim($message['title'])."</br>"
            ];
            $users = User::all(['id']);
            Notification::send($users, new $notificationClass($message));
            $connectConfig = $config['config'];
            $queue = $config['queue'];
            $exchange = $config['exchange'];
            $exchangeType = $config['exchange_type'];
            $routingKey = $config['routing_key'];
            $mqMsg = json_encode($mqMsg);
            foreach ($users as $user){
                $rk = $routingKey."_user_id_".$user['id'];
                $connect = $this->connect($queue, $exchange, $exchangeType, $rk, $connectConfig);
                $connect->sendMessageToServer($mqMsg);
            }
            return $this->success('发送成功');
        }catch (\Exception $exception){
            return $this->error('发送失败：'.$exception->getMessage());
        }
    }

    public function connect($queue, $exchange, $exchangeType, $routingKey, $config = array()){
        return new AmqpServer(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost'],
            $routingKey,
            $exchange,
            $queue,
            $exchangeType
        );
    }
}
