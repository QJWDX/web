<?php


namespace App\Models\Notification;

use App\Models\BaseModel;
use Dx\Role\Models\User;

class Notifications extends BaseModel
{
    protected $table = 'notifications';
    protected $guarded = [];

    public $type = [
        systemNotifications::class,
    ];

    public $notifiable_type = [
        User::class
    ];


    protected $casts = [
        'id' => 'string'
    ];

    /**
     * 获取通知列表
     * @param array $where
     * @return array
     */
    public function getNotifications($where = array()){
        return $this->PaginateForApi($this->builderQuery($where));
    }

    /**
     * 构造查询
     * @param array $where
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builderQuery($where = array()){
        $builder = $this->newQuery();
        $builder->when($where['notifiable_type'], function ($query) use ($where){
            $query->where('notifiable_type', $where['notifiable_type']);
        })->when($where['notifiable_id'], function ($query) use ($where){
            $query->where('notifiable_id', $where['notifiable_id']);
        })->when(isset($where['startTime']) && $where['startTime'], function ($query) use ($where){
            $query->where('created_at', '>', $where['startTime']);
        })->when(isset($where['endTime']) && $where['endTime'], function ($query) use ($where){
            $query->where('created_at', '<', $where['endTime']);
        })->when($where['read_at'], function ($query) use ($where){
            if($where['read_at'] == 1){
                $query->whereNull('read_at');
            }
            if($where['read_at'] == 2){
                $query->whereNotNull('read_at');
            }
        });
        return $builder->select(['id', 'data', 'read_at', 'created_at', 'updated_at']);
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
        return $this->newQuery()->where('id',$id)->exists();
    }


    /**
     * 标记已读
     * @param $id
     * @return int
     */
    public function makeRead($id){
        return $this->newQuery()->where('id', $id)->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 消息已读未读总数数量统计
     * @param $notifiable_id
     * @param int $type
     * @param int $notifiable_type
     * @return array
     */
    public function notificationCountStatistics($notifiable_id, $type = 0, $notifiable_type = 0){
        $where = array(
            'type' => $this->type[$type],
            'notifiable_id' => $notifiable_id,
            'notifiable_type' => $this->notifiable_type[$notifiable_type],
            'read_at' => 0
        );
        $count = $this->builderQuery($where)->count();
        $where['read_at'] = 1;
        $unread_count = $this->builderQuery($where)->count();
        $read_count = $count - $unread_count;
        return compact('count', 'read_count', 'unread_count');
    }
}
