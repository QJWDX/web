<?php


namespace App\Http\Controllers\System;


use App\Http\Controllers\Controller;

class SystemController extends Controller
{
    public function system(){
        $params = [
            'bd_code' => config('baiDuTj.bd_code')
        ];
        return $this->success($params);
    }
}
