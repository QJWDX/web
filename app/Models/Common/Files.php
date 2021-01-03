<?php


namespace App\Models\Common;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Files extends BaseModel
{
    use SoftDeletes;
    protected $table = 'files';
    protected $guarded = [];

    public function getList($where = array()){
        $builder = $this->builderQuery($where);
        if($where['export'] == 1){
            return $builder->select([
                'uid',
                'title',
                'type',
                'disks',
                'folder',
                'path',
                'mime_type',
                'size',
                'width',
                'height',
                'created_at',
                'downloads'
            ])->get();
        }
        return $this->PaginateForApi($builder);
    }


    public function getRow($where = array()){
        $builder = $this->builderQuery($where);
        return $builder->first();
    }

    public function builderQuery($where = array(), $field = array()){
        $builder = $this->newQuery()->withTrashed();
        if($field){
            $builder = $builder->select($field);
        }
        $builder = $builder->when($where, function ($query) use($where){
            $query->where('title', 'like', '%'. $where['title']. '%');
        })->when($where, function ($query) use($where){
            $query->where('type', 'like', '%'. $where['type']. '%');
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
