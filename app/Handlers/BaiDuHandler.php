<?php


namespace App\Handlers;


use App\Service\Http;

class BaiDuHandler
{

    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    protected static $ak;

    const BASE_URL = 'http://api.map.baidu.com/';

    public static function getInstance()
    {
        self::$ak = config('bd.api.ak');
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * 普通ip定位
     * @param null $ip
     * @return string
     */
    public static function getLocationByIp($ip = null){
        if($ip == null){
            return '';
        }
        $params = [
            'ip' => $ip,
            'ak' => static::$ak
        ];
        $url = self::BASE_URL . 'location/ip';
        $data = Http::httpRequire($url, $params);
        if($data['status'] == 0){
            return $data['content']['address'];
        }
        return '';
    }
}
