<?php


namespace App\Models\Base;


use App\Models\BaseModel;
use Illuminate\Http\Request;

class Role extends BaseModel
{
    protected $table = 'role';
    protected $guarded = [];


    /**
     * 角色列表
     * @param Request $request
     * @return array
     */
    public function roleList(Request $request){
        $builder = $this->newQuery();
        $roleName =  $request->get('role_name', false);
        if($roleName){
            $builder = $builder->where('role_name', 'like', '%'.$roleName.'%');
        }
        $builder = $builder->select(['*', 'parent_id as parent_name']);
        return $this->modifyPaginateForApi($builder);
    }

    /**
     * 修改is_super属性值
     * @param $isSuper
     * @return mixed
     */
    public function getIsSuperAttribute($isSuper){
        $super_zh = ['否', '是'];
        return $super_zh[$isSuper];
    }

    /**
     * 父级角色属性值
     * @param $parentId
     * @return \Illuminate\Support\Collection|string
     */
    public function getParentNameAttribute($parentId){
        if($parentId == 0){
            $parentName = '顶级角色';
        }else{
            $parentName = $this->newQuery()->where('id', $parentId)->first()->pluck('role_name');
        }
        return $parentName;
    }
}
