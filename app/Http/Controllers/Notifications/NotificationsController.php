<?php


namespace App\Http\Controllers\Notifications;


use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Notification\Notifications;
use App\Notifications\systemNotification;
use Dx\Role\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $where = compact('notifiable_id', 'read_at', 'startTime', 'endTime');
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
     * 发送系统通知
     * @param Request $request
     * @param User $user
     */
    public function sendNotification(Request $request, User $user){
        $type = $request->get('type', 1);
        try {
            $message = $request->only(['title', 'content']);
            $class = null;
            switch ($type){
                case 1:
                    $class = new systemNotification($message);
                    break;
            }
            $user->notify($class);
            $config = config('notification');
            $connectConfig = $config['config'];
            $queue = $config['queue'];
            $exchange = $config['exchange'];
            $exchangeType = $config['exchange_type'];
            $routingKey = $config['routing_key'];
            $connect = $this->connect($queue, $exchange, $exchangeType, $routingKey, $connectConfig);
            $message = json_encode($message);
            $connect->sendMessageToServer($message);
            $this->success('发送成功');
        }catch (\Exception $exception){
            $this->error('发送失败：'.$exception->getMessage());
        }
    }
}
