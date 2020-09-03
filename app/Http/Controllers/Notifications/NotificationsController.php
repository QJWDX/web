<?php


namespace App\Http\Controllers\Notifications;


use App\Events\sendNotification;
use App\Http\Controllers\Controller;
use App\Models\Common\Notifications;
use App\Models\Common\systemNotifications;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public $notifiable_type = [
        1 => 'App\Notifications\systemNotification',
        2 => 'App\Notifications\otherNotification'
    ];

    /**
     * 消息通知列表
     * @param Request $request
     * @param User $userModel
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request, User $userModel){
        $uid = $request->get('uid', false);
        if(!$uid){
            return $this->error(500, '参数失败');
        }
        $type = $request->get('type', 0);
        $exists = $userModel->newQuery()->where('id', $uid)->exists();
        if(!$exists){
            return $this->error(500, '用户不存在');
        }
        $user = $userModel->newQuery()->find($uid);
        switch ((int)$type){
            case 0:
                $data = $user->notifications;
                break;
            case 1:
                $data = $user->readNotifications;
                break;
            case 2:
                $data = $user->unreadNotifications;
                break;
        }
        if($request->has('notifiable_type')){
            $notifiable_type = $request->get('notifiable_type');
            if(!array_key_exists($notifiable_type, $this->notifiable_type)){
                return $this->error(500, '参数失败');
            }
            $data  = $data->where('type', $this->notifiable_type[$notifiable_type])->all();
        }
        if(!$data){
            return $this->error(500, '获取失败');
        }
        return $this->success($data);
    }


    /**
     * 创建系统通知
     * @param Request $request
     * @param systemNotifications $systemNotifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNotifications(Request $request, systemNotifications $systemNotifications){
        $title = $request->get('title', false);
        $content = $request->get('content', false);
        if($title == false || $content == false){
            return $this->error(500, '参数错误');
        }
        $sysNotifications = $systemNotifications->addSystemNotifications([$title, $content]);
        if(!$sysNotifications){
            return $this->error(500, '创建失败');
        }
        // 向所有用户发送系统消息通知
        event(new sendNotification($sysNotifications));
        return $this->success('创建成功');
    }

    /**
     * 一键标记为已读
     * @param Request $request
     * @param User $userModel
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeRead(Request $request, User $userModel, Notifications $notifications){
        $id = $request->get('id', false);
        $uid = $request->get('uid', false);
        if(!$uid){
            return $this->error(500, '参数失败');
        }
        $exists = $userModel->newQuery()->where('id', $uid)->exists();
        if(!$exists){
            return $this->error(500, '用户不存在');
        }
        $user = $userModel->newQuery()->find($uid);
        if($id){
            $user->unreadNotifications->where('id', $id)->markAsRead();
        }else{
            $user->unreadNotifications->markAsRead();
        }
        return $this->success('标记已读成功');
    }

    /**
     * 删除消息通知
     * @param Request $request
     * @param User $userModel
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function delNotifications(Request $request, User $userModel, Notifications $notifications){
        $id = $request->get('id', false);
        $uid = $request->get('uid', false);
        if(!$uid){
            return $this->error(500, '参数错误');
        }
        $exists = $userModel->newQuery()->where('id', $uid)->exists();
        if(!$exists){
            return $this->error(500, '用户不存在');
        }
        $user = $userModel->newQuery()->find($uid);
        if($id){
            $res = $user->notifications()->where('id', $id)->delete();
        }else{
            $res = $user->notifications()->whereNotNull('read_at')->delete();
        }
        if($res){
            return $this->success('删除消息通知成功');
        }
    }

    /**
     * 未读通知数量
     * @param Request $request
     * @param User $userModel
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNumber(Request $request, User $userModel){
        $uid = $request->get('uid', false);
        if(!$uid){
            return $this->error(500, '参数错误');
        }
        $exists = $userModel->newQuery()->where('id', $uid)->exists();
        if(!$exists){
            return $this->error(500, '用户不存在');
        }
        $user = $userModel->newQuery()->find($uid);
        $count = $user->unreadNotifications()->count();
        return $this->success(['unreadNumber' => $count]);
    }
}
