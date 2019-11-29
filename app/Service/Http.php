<?php


namespace App\Service;

use GuzzleHttp\Client;

class Http
{
    private static $client;

    public static $log;

    public function instance(){
        if(self::$client instanceof Client){
            return self::$client;
        }
        self::$client = new Client();
        return self::$client;
    }

    public static function httpRequire($url, $query = [], $option = [], $type = 'get'){
        self::$log = [];
        $client = self::instance();
        $array = [
            'json' => [],
            'query' => $query,
            'http_errors' => false
        ];
        if (count($option)) {
            $array = array_merge($array, $option);
        }

        $polling_times = 3;

        for ($i = 0; $i <= $polling_times; $i++) {
            try {
                $response = $client->request($type, $url, $array);
            } catch (\GuzzleHttp\Exception\ClientException $exception) {
                $response = $exception->getResponse();
                Log::error("time:" . date("Y-m-d H:i:s") .  "\tmessage: " . $response->getBody()->getContents() . "\t code: " . $response->getStatusCode());
                continue;
            }

            $code = $response->getStatusCode();
            $source_data = $response->getBody()->getContents();

            $log = [
                'url' => $url,
                'query' => $query,
                'code' => $response->getStatusCode(),
                'data' => $source_data
            ];

            self::$log = $log;

            if ($code != 200) {
                //  继续运行
                continue;
            }
            $data_array = json_decode($source_data, true);

            if (!isset($data_array['data'])) {
                continue;
            }

            return $data_array;
        }
    }
}
