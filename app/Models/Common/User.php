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
}
