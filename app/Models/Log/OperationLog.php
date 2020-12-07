<?php

namespace App\Models\Log;

use App\Models\Common\User;
use Carbon\Carbon;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logList(): array
    {
        $builder = $this->newQuery()->with('user');

        return $this->modifyPaginateForApi($builder);
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
