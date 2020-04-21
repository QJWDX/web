<?php


namespace App\Http\Controllers\Notifications;


use App\Http\Controllers\Controller;
use App\Models\Base\Notifications;
use App\Models\Base\systemNotifications;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * 消息通知列表
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications(Request $request, Notifications $notifications){
        $type = $request->get('type', false);
        if(!array_key_exists(intval($type), $notifications->type)){
            return $this->error(500, '参数失败');
        }
        $res = $notifications->allNotifications($request);
        if(!$res){
            return $this->error(500, '获取失败');
        }
        return $this->success($res);
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
        $res = $systemNotifications->addNotifications([$title, $content]);
        if(!$res){
            return $this->error(500, '创建失败');
        }
        return $this->success('创建成功');
    }

    /**
     * 一键标记已读
     * @param Request $request
     * @param Notifications $notifications
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeRead(Request $request, Notifications $notifications){
        $ids = $request->get('ids');
        $ids = explode(',', $ids);
        if(empty($ids)){
            return $this->error(500, '参数错误');
        }
        $res = $notifications->newQuery()->whereIn('id', $ids)->update(['read_at', Carbon::now()]);
        if($res){
            return $this->success('标记已读成功');
        }
        return $this->error(500, '标记已读失败');
    }
}
