<?php


namespace App\Models\Base;


use App\Models\BaseModel;
use Illuminate\Http\Request;

class Notifications extends BaseModel
{
    protected $table = 'notifications';
    protected $guarded = [];

    public $type = [
        1 => 'App\Models\Base\SystemNotifications'
    ];

    public function allNotifications(Request $request, $type = 0){
        $builder = $this->newQuery();
        if($type){
            $builder = $builder->where('notifiable_type', $this->type[$type]);
        }
        return $this->modifyPaginateForApi($builder);
    }
}
