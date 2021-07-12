<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfterMiddleware
{

    protected $total_sql = [];


    public function handle(Request $request, Closure $next)
    {
        $this->setSqlByListen($request);
        $response = $next($request);
        if ($this->passRequestMethod($request) && $this->passFunc($request)) {
            $sql = $this->getNeedSaveSql();
            $log = [
                'user_id' => Auth::guard('api')->user()->id ?? 0,
                'path' => substr($request->path(), 0, 255),
                'method' => $request->method(),
                'ip' => $request->header('x-real-ip', $request->ip()),
                'input' => json_encode($request->input()),
                'sql' => json_encode($sql)
            ];
            try {
                Log::channel('operation_log')->info('操作日志记录', $log);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
            }
        }
        return $response;
    }


    protected function passRequestMethod(Request $request)
    {
        if (in_array($request->method(), ['OPTIONS', 'HEAD'])) {
            return false;
        }
        return true;
    }


    protected function passFunc(Request $request)
    {
        if (strpos(app()->version(), "Lumen") !== false) {
            $action = $request->route()[1]['uses'];
        } else {
            $action = $request->route()->getActionName();
        }
        list($class, $method) = explode('@', $action);
        //除去检测是否登录得空接口
        if ($method == 'checkIsLoggedIn') {
            return false;
        }
        return true;
    }

    /**
     * 设置数据库监听
     * @param Request $request
     */
    public function setSqlByListen(Request $request) : void
    {
        DB::listen(function (QueryExecuted $query) use($request) {
            $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
            $bindings = $query->connection->prepareBindings($query->bindings);
            $pdo = $query->connection->getPdo();
            $realSql = $sqlWithPlaceholders;
            if (count($bindings) > 0) {
                $realSql = vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings));
            }
            // 记录慢查询日志 方便排查慢查询问题
            $executeTime = $query->time;
            if(config('slow_sql_log.enable') && $executeTime > config('slow_sql_log.min_time')){
                $slow_logs = [
                    'path' => substr($request->path(), 0, 255),
                    'sql' => $realSql,
                    'execute_time' => $executeTime
                ];
                Log::channel('slow_sql_log')->info($slow_logs);
            }
            $this->total_sql[] = $realSql;
        });
    }


    public function getNeedSaveSql() : array
    {
        $final_sql = [];
        foreach ($this->total_sql as $key => $value) {
            if (strpos($value, 'select') === 0) {
                Log::error($value);
                continue;
            }
            $final_sql[] = $value;
        }
        return $final_sql;
    }
}
