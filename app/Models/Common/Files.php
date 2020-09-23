<?php


namespace App\Models\Common;


use App\Models\BaseModel;

class Files extends BaseModel
{
    protected $table = 'files';
    protected $guarded = [];


    /**
     * 文件按是否存在
     * @param array $where
     * @return bool
     */
    public function fileIsExists($where = array()){
        if($where){
            return $this->newQuery()->where($where)->exists();
        }
        return false;
    }

    /**
     * 新增文件
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function add($params = array()){
        return $this->newQuery()->create($params);
    }
}
