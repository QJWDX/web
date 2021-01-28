<?php

namespace App\Models\Log;

use Carbon\Carbon;
use App\Models\BaseModel;
use Dx\Role\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OperationLog extends BaseModel
{
    protected $table = 'operation_log';

    protected $fillable = ['user_id', 'path', 'method', 'ip', 'input', 'sql'];


    public static $methods = [
        'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH',
        'LINK', 'UNLINK', 'COPY', 'HEAD', 'PURGE',
    ];

    public function setTable($table)
    {
        $this->table = sprintf('%s_%s', $table, $this->getTableName());
        return $this;
    }

    public function getTableName()
    {
        return Carbon::now()->format('Ym');
    }

    public function getOriginalTableName()
    {
        return 'operation_log';
    }

    /**
     * 查询时候必须调用
     * @return $this
     */
    public function setMonthTable()
    {
        $this->setTable($this->getOriginalTableName());
        return $this;
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['id','username']);
    }

    public function getLogList($params = []){
        return $this->paginateForApi($this->builderQuery($params));
    }

    public function getLogInfo($where = array()){
        $builder = $this->setMonthTable()->newQuery();
        if(!$where){
            return false;
        }
        return $builder->where($where)->first();
    }


    public function builderQuery($params = [], $field = ['*']){
        $builder = $this->setMonthTable()->newQuery()->with('user')->select($field);
        $builder = $builder->when($params['user_id'], function ($query) use($params){
            $query->whereIn('user_id', $params['user_id']);
        })->when($params['startTime'], function ($query) use($params){
            $query->where('login_time', '>', $params['startTime']);
        })->when($params['endTime'], function ($query) use($params){
            $query->where('login_time', '<', $params['endTime']);
        });
        return $builder;
    }


    public function del($ids = []){
        if(empty($ids)){
            return false;
        }
        return $this->setMonthTable()->newQuery()->whereIn('id', $ids)->delete();
    }

    public function createTable(){
        $tableName = sprintf('%s_%s', $this->table, $this->getTableName());
        if(!Schema::hasTable($tableName)){
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('path', 255);
                $table->string('method', 10);
                $table->string('ip', 255);
                $table->text('input');
                $table->text('sql');
                $table->timestamps();
            });
        }
    }
}
