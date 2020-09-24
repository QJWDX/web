<?php


namespace App\Models\Common;


use App\Models\BaseModel;

class LoginLog extends BaseModel
{
    protected $table = 'login_log';
    protected $guarded = [];


    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['id','username']);
    }

    /**
     * 获取列表数据
     * @param array $where
     * @return array
     */
    public function getList($where = array()){
        return $this->modifyPaginateForApi($this->builderQuery($where));
    }


    /**
     * 获取单条数据
     * @param array $where
     * @param array $field
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getRow($where = array(), $field = array()){
        $builder = $this->newQuery();
        if(!$where){
            return false;
        }
        if($field){
            $builder = $builder->select($field);
        }
        return $builder->where($where)->first();
    }


    public function builderQuery($where= array(), $field = array()){
        $builder = $this->newQuery()->with('user');
        if($field){
            $builder = $builder->select($field);
        }
        $builder = $builder->when($where['startTime'], function ($query) use($where){
            $query->where('login_time', '>', $where['startTime']);
        })->when($where['endTime'], function ($query) use($where){
            $query->where('login_time', '<', $where['endTime']);
        });
        return $builder;
    }

    /**
     * 删除
     * @param array $ids
     * @return bool
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
}
