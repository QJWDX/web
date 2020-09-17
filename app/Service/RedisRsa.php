<?php


namespace App\Service;
use App\Exceptions\ApiRequestExcept;
use Illuminate\Support\Facades\Redis;

class RedisRsa
{
    public static function getFlashRsaKey($key)
    {
        $private_key = Redis::get($key);
        if (!$private_key) throw new ApiRequestExcept('encrypt_key不存在', 500);
//        Redis::del($key);
        return $private_key;
    }
}
