<?php


namespace App\Models\Common;


use App\Models\BaseModel;

class Files extends BaseModel
{
    protected $table = 'files';
    protected $guarded = [];


    /**
     * 获取文件信息
     * @param array $where
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getFile($where = array()){
        return $this->newQuery()->where($where)->first();
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
