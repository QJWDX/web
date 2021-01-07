<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 数据分页
     * @param Builder $builder
     * @return array
     */
    public function paginateForApi(Builder $builder)
    {
        $request = request();
        $perPage = 10;
        if ($request->has("perPage")) {
            $perPage = $request->get("perPage");
        }
        if ($request->get("export", 0)) {
            $api = $builder->get();
        } else {
            $paginate = $builder->paginate($perPage);
            $api = [
                'current_page' => 0,
                'total' => 0,
                'last_page' => 0,
                'per_page' => 0,
                'items' => []
            ];
            $api['current_page'] = $paginate->currentPage();
            $api['total'] = $paginate->total();
            $api['last_page'] = $paginate->lastPage();
            $api['items'] = $paginate->items();
            $api['per_page'] = intval($paginate->perPage());
        }
        return $api;
    }


    /**
     * 生成唯一的uuid
     * @param bool $symbol
     * @return string
     */
    public function uuid($symbol = true)
    {
        //optional for php 4.2.0 and up.
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        if ($symbol === false) {
            $hyphen = '';
        }
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }
}
