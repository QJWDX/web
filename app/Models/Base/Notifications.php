<?php


namespace App\Models\Base;


use App\Models\BaseModel;

class Notifications extends BaseModel
{
    protected $table = 'notifications';
    protected $guarded = [];

    public $type = [
        1 => 'App\Models\Base\SystemNotifications'
    ];

    public function allNotifications($request){
        $type = $request->get('type', false);
        $builder = $this->newQuery();
        if($type){
            $builder = $builder->where('notifications_type', $type);
        }
        $this->modifyPaginateForApi($builder);
    }
}
