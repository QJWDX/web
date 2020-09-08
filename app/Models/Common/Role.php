<?php


namespace App\Models\Common;
use App\Models\BaseModel;

class Role extends BaseModel
{
    protected $table = 'role';
    protected $guarded = [];


    /**
     * 获取列表数据
     * @param array $field
     * @return array
     */
    public function getList($field = array()){
        return $this->modifyPaginateForApi($this->builderQuery($field));
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


    public function builderQuery($field = array()){
        $role_name = request('role_name', false);
        $builder = $this->newQuery();
        if($field){
            $builder = $builder->select($field);
        }
        $builder = $builder->when($role_name, function ($query) use($role_name){
            $query->where('role_name', 'like', '%'. $role_name. '%');
        });
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
     */
    public function del($ids = array()){
        $roles = $this->newQuery()->whereIn('id', $ids)->get();
        foreach ($roles as $role){
            $role->delete();
        }
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
