<?php


namespace App\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Log;

class Http
{
    private static $client;

    public static $log;

    public static function instance(){
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
        $response = $client->request($type, $url, $array);
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
            Log::error($code);
            Log::error($source_data);
            return false;
        }
        return json_decode($source_data, true);
    }


    /**
     * @param bool $simple
     * @return bool|string
     */
    public static function getLog($simple = false)
    {
        $log = json_encode(self::$log, JSON_PRETTY_PRINT);
        if ($simple && strlen($log) > 1000) {
            return substr($log, 0, 1000);
        }
        return $log;
    }

    /**
     * 并发请求
     * @param $requests
     * @param Closure $success
     * @param Closure $reject
     * @param int $concurrent_count
     */
    public static function concurrentHttp($requests, Closure $success, Closure $reject, $concurrent_count = 5, $timeout = 5)
    {
        $client = self::instance();

        $pool = new Pool($client, $requests, [
            //并发数
            'concurrency' => $concurrent_count,
            'fulfilled' => $success,
            'rejected' => $reject,
            'options' => ['timeout' => $timeout]
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

    }

    /**
     * 从http下载文件
     * @param $url
     * @param $file_path
     * @param $file_name
     * @param array $query
     * @param array $option
     * @param string $type
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpRequireByDown($url, $file_path, $file_name, $query = [], $option = [], $type = 'get')
    {
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

        $response = $client->request($type, $url, $array);

        $code = $response->getStatusCode();
        $source_data = $response->getBody();
        if ($code == 200) {
            return $this->saveFile($file_path, $file_name, $source_data);
        } else {
            return false;
        }
    }
}
