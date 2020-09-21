<?php


namespace App\Service;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Redis;

class RedisRsa
{
    public static function getFlashRsaKey($key)
    {
        $private_key = Redis::connection()->get($key);
        if (!$private_key) {
            throw new ApiException('encrypt_key不存在', 500);
        }
        Redis::connection()->del($key);
        return $private_key;
    }
}
