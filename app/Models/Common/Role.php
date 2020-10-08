<?php


namespace App\Models\Common;
use App\Models\BaseModel;

class Role extends BaseModel
{
    protected $table = 'role';
    protected $guarded = [];


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
    public function getRow($where = array(), $field = array('*')){
        $builder = $this->newQuery();
        if(!$where){
            return false;
        }
        $builder->select($field);
        return $builder->where($where)->first();
    }


    public function builderQuery($where = array(), $field = array('*')){
        $builder = $this->newQuery();
        $builder->when($where['role_name'], function ($query) use($where){
            $query->where('role_name', 'like', '%'. $where['role_name']. '%');
        });
        $builder->select($field);
        return $builder;
    }


    /**
     * 是否超级角色
     * @param $id
     * @return bool
     */
    public function isSuperRole($id){
        return $this->newQuery()->where('id', $id)->where('is_super', 1)->exists();
    }


    /**
     * 是否含有超级角色
     * @param $ids
     * @return bool
     */
    public function hasSuperRole($ids = array()){
        return $this->newQuery()->whereIn('id', $ids)->where('is_super', 1)->exists();
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


    /**
     * 获取全部数据
     * @param array $field
     * @param array $where
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll($field = array(), $where = array()){
        return $this->newQuery()->where($where)->select($field)->get();
    }
}
