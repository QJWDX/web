<?php


namespace App\Http\Controllers\Notification;


use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Notification\Notifications;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{

    /**
     * 获取通知列表
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request, Notifications $notifications){
        $notifiable_id = $request->get('notifiable_id', 0);
        $type = $request->get('type', 0);
        $notifiable_type = $request->get('notifiable_type', 0);
        $read_at = $request->get('read_at', 0);
        if(!array_key_exists($type, $notifications->type)){
            return $this->error(500, '通知所属类型参数错误');
        }
        if(!array_key_exists($notifiable_type, $notifications->notifiable_type)){
            return $this->error(500, '通知对象类型错误');
        }
        if(!$notifiable_id){
            return $this->error(500, '通知对象id参数错误');
        }
        $notice = new $notifications->notifiable_type[$notifiable_type]();
        if(!$notice->newQuery()->find($notifiable_id)){
            return $this->error(500, '通知对象不存在');
        }
        $type = $notifications->type[$type];
        $notifiable_type = $notifications->notifiable_type[$notifiable_type];
        $startTime = $request->get('startTime', false);
        $endTime = $request->get('endTime', false);
        $where = compact('type', 'notifiable_id', 'notifiable_type', 'read_at', 'startTime', 'endTime');
        $data = $notifications->getNotifications($where);
        if(!$data){
            return $this->error(500, '获取通知列表失败');
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
        $id = $request->get('id', 0);
        if(!$id || !$notifications->isExits($id)) {
            return $this->error(500, '通知id错误');
        }
        if($notifications->makeRead($id)){
            return $this->success('标记已读成功');
        }
        return $this->error(500, '标记已读失败');
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
     * 未读已读数
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationCountStatistics(Request $request, Notifications $notifications){
        $notifiable_id = $request->get('notifiable_id', 0);
        $type = $request->get('type', 0);
        $notifiable_type = $request->get('notifiable_type', 0);
        if(!array_key_exists($type, $notifications->type)){
            return $this->error(500, '通知所属类型参数错误');
        }
        if(!array_key_exists($notifiable_type, $notifications->notifiable_type)){
            return $this->error(500, '通知对象类型错误');
        }
        if(!$notifiable_id){
            return $this->error(500, '通知对象id参数错误');
        }
        $notice = new $notifications->notifiable_type[$notifiable_type]();
        if(!$notice->newQuery()->find($notifiable_id)){
            return $this->error(500, '通知对象不存在');
        }
        $data = $notifications->notificationCountStatistics($notifiable_id, $type, $notifiable_type);
        return $this->success($data);
    }
}
