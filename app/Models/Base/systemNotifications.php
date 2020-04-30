<?php


namespace App\Models\Base;


use App\Models\BaseModel;

class systemNotifications extends BaseModel
{
    protected $table = 'system_notifications';
    protected $guarded = [];


    /**
     * 添加系统通知
     * @param $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function addSystemNotifications($data){
        $instance = $this->newQuery()->create([
             'title' => $data[0],
             'content' => $data[1],
         ]);
         return $instance;
    }
}
