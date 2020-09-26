<?php


namespace App\Models\Common;


use App\Models\BaseModel;
use Illuminate\Http\Request;

class User extends BaseModel
{
    protected $table = 'user';
    protected $guarded = [];

    /**
     * 数据列表
     * @param Request $request
     * @return array
     */
    public function getDataList(Request $request){
        $builder = $this->builderQuery($request);
        return $this->modifyPaginateForApi($builder);
    }

    // 构造查询
    public function builderQuery(Request $request){
        $username = $request->get('username', false);
        $builder = $this->newQuery();
        $builder->when($username, function ($query) use ($username){
            $query->where('username', 'like', '%'. $username);
        });
        return $builder;
    }

    // 单个用户信息
    public function getUser($id){
        return $this->newQuery()->find($id);
    }

    /**
     * 是否存在用户
     * @param $id
     * @return bool
     */
    public function hasUser($id){
        return $this->newQuery()->where('id', $id)->exists();
    }

    /**
     * 设置头像
     * @param $val
     * @return string
     */
    public function getAvatarAttribute($val)
    {
        return config('app.url').'/upload/'.$val;
    }
}
