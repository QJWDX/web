<?php


namespace App\Helpers;


class CommonFun
{
    function checkParam(array $params, string $key){
        if(!$params) return false;
        return isset($params[$key]) && $params[$key];
    }
}
