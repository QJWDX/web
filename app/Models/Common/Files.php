<?php


namespace App\Models\Common;


use App\Models\BaseModel;
use App\Traits\Curd;
use Illuminate\Database\Eloquent\SoftDeletes;

class Files extends BaseModel
{
    use SoftDeletes, Curd;
    protected $table = 'files';
    protected $guarded = [];

    public function getFileList($where = array()){
        $builder = $this->builderQuery($where);
        if($where['export']){
            return $builder->get();
        }
        return $this->PaginateForApi($builder);
    }

    public function getFile($where = []){
        return $this->newQuery()->where($where)->first();
    }

    public function builderQuery($where = array(), $field = array('*')){
        $builder = $this->newQuery();
        $builder->when(isset($where['title']) && $where['title'], function ($query) use($where){
            $query->where('title', 'like', '%'. $where['title']. '%');
        })->when(isset($where['type']) && $where['type'], function ($query) use($where){
            $query->where('type', 'like', '%'. $where['type']. '%');
        })->when(isset($where['startTime']) && $where['startTime'], function ($query) use($where){
            $query->where('created_at', '>', $where['startTime']);
        })->when(isset($where['endTime']) && $where['endTime'], function ($query) use($where){
            $query->where('created_at', '<',$where['endTime']);
        });
        $builder->select($field);
        return $builder;
    }
}
