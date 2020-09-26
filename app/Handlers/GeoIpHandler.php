<?php


namespace App\Handlers;


use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIpHandler
{
    private static $instance = null;

    public static $blacklist = [
        '127.0.0.1'
    ];

    public static function instance(){
        if(static::$instance == null){
            static::$instance = new Reader(public_path('access/GeoLite2/GeoLite2-City.mmdb'));
        }
        return static::$instance;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __construct()
    {
    }

    /**
     * 根据ip获取地址
     * @param $ip
     * @return string
     */
    public static function getAddress($ip){
        try {
            if(empty($ip) || in_array($ip, static::$blacklist)){
                return '';
            }
            $reader = static::instance();
            $record = $reader->city($ip);
            $address = '';
            $continent = $record->continent->name;
            $country = $record->country->name;
            $city = $record->city->name;
            $continent = isset($continent['zh-CN']) ? $continent['zh-CN'] : $continent;
            $country = isset($country['zh-CN']) ? $country['zh-CN'] : $country;
            $city = isset($city['zh-CN']) ? $city['zh-CN'] : $city;
            $address .= $continent .";". $country .";". $city;
            return $address;
        }catch (InvalidDatabaseException $e){
            Log::channel('api')->error($ip);
            Log::channel('api')->error($e->getMessage());
        }
    }
}
