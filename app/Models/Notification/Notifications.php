<?php


namespace App\Models\Notification;

use App\Models\BaseModel;

class Notifications extends BaseModel
{
    protected $table = 'notifications';
    protected $guarded = [];

    protected $casts = [
        'id' => 'string'
    ];

    /**
     * 获取通知列表
     * @param array $params
     * @return array
     */
    public function getNotifications($params = []){
        return $this->PaginateForApi($this->builderQuery($params));
    }

    public function builderQuery($params = []){
        $builder = $this->newQuery();
        $builder->when(isset($params['notifiable_id']) && $params['notifiable_id'], function ($query) use ($params){
            $query->where('notifiable_id', $params['notifiable_id']);
        })->when(isset($params['startTime']) && $params['startTime'], function ($query) use ($params){
            $query->where('created_at', '>', $params['startTime']);
        })->when(isset($where['endTime']) && $where['endTime'], function ($query) use ($params){
            $query->where('created_at', '<', $params['endTime']);
        })->when(isset($params['read_at']) && $params['read_at'], function ($query) use ($params){
            if($params['read_at'] == 1){
                $query->whereNotNull('read_at');
            }
            if($params['read_at'] == 2){
                $query->whereNull('read_at');
            }
        });
        return $builder->select(['*']);
    }


    /**
     * 删除
     * @param $ids
     * @return mixed
     */
    public function del($ids = array()){
        if(empty($ids)){
            return false;
        }
        $instances = $this->newQuery()->whereIn('id', $ids)->get('id');
        foreach ($instances as $instance){
            $instance->delete();
        }
        return true;
    }

    /**
     * 是否存在
     * @param $id
     * @return bool
     */
    public function isExits($id){
        return $this->newQuery()->where('id', $id)->exists();
    }

    /**
     * 标记已读
     * @param array $ids
     * @return int
     */
    public function makeRead($ids = []){
        if(!$ids) return false;
        return $this->newQuery()->whereIn('id', $ids)->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 消息已读未读数量
     * @param $notifiable_id
     * @return array
     */
    public function notificationCount($notifiable_id){
        $params = [
            'notifiable_id' => $notifiable_id,
            'read_at' => 0
        ];
        $count = $this->builderQuery($params)->count();
        $params['read_at'] = 1;
        $read = $this->builderQuery($params)->count();
        $unread = $count - $read;
        return compact('count', 'read', 'unread');
    }
}
