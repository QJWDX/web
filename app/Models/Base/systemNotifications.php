<?php


namespace App\Models\Base;


use App\Models\BaseModel;
use App\Notifications\systemNotification;
use Illuminate\Notifications\Notifiable;

class systemNotifications extends BaseModel
{
    use Notifiable;
    protected $table = 'system_notifications';
    protected $guarded = [];


    /**
     * 添加系统通知
     * @param $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function addNotifications($data){
         $notifications = $this->newQuery()->create([
             'title' => $data[0],
             'content' => $data[1],
         ]);
         if($notifications){
             $notifications->notify(new systemNotification($notifications));
         }
         return $notifications;
    }
}
