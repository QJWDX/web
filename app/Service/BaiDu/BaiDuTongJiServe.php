<?php


namespace App\Service\BaiDu;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use function App\Helpers\curl_request;

class BaiDuTongJiServe
{
    protected $access_token;
    protected $refresh_token;
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;
    protected $code;
    protected $jsonFileName = 'bd_token.json';
    protected $redisKey = 'baidu_token';
    protected $refresh_diff_time = 7200;
    const metrics_default = "pv_count,pv_ratio,visit_count,visitor_count,new_visitor_count,new_visitor_ratio,ip_count,bounce_ratio,avg_visit_time,avg_visit_pages,trans_count,trans_ratio";

    public function __construct()
    {
        $this->setConfig();
        if(!file_exists(public_path($this->jsonFileName))){
            $this->getAccessToken();
        }else{
            $this->checkLocalToken();
        }
    }

    /**
     * 判断本地token是否存在或过期 过期刷新
     * @return mixed
     */
    public function checkLocalToken(){
        $token = Redis::connection()->get($this->redisKey);
        if($token){
            $token = json_decode($token, true);
        } else{
            $token = json_decode(file_get_contents(public_path($this->jsonFileName)), true);
        }
        $this->access_token = $token['access_token'];
        $this->refresh_token = $token['refresh_token'];
        // 距离token过期多少秒刷新token, 默认两小时
        if($token['expires_in'] - time() < $this->refresh_diff_time){
            $this->getAccessToken(true);
        }
    }

    /**
     * 刷新access_token
     * @param bool $refresh 刷新
     */
    public function getAccessToken($refresh = false){
        $url = "http://openapi.baidu.com/oauth/2.0/token?";
        $params['grant_type'] = $refresh ? 'refresh_token' : 'authorization_code';
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        switch ($refresh){
            case true:
                $params['refresh_token'] = $this->refresh_token;
                break;
            case false:
                $params['code'] = $this->code;
                $params['redirect_uri'] = $this->redirect_uri;
                break;
        }
        $url = $url . http_build_query($params);
        $jsonResult = curl_request($url);
        $result = json_decode($jsonResult, true);
        if(isset($result['access_token'])){
            // 默认返回过期时间是一个月的时间戳 加上当前时间
            $result['expires_in'] = time() + $result['expires_in'];
            $cacheData = json_encode($result);
            Redis::connection()->set($this->redisKey, $cacheData);
            file_put_contents(public_path($this->jsonFileName), $cacheData);
            $this->access_token = $result['access_token'];
            $this->refresh_token = $result['refresh_token'];
        }else{
            print_r("刷新token出错:".$jsonResult."\n");
        }
    }


    public function setConfig()
    {
        $config = config("baiduTj");
        $this->client_id = $config['client_id'];
        $this->client_secret = $config['client_secret'];
        $this->redirect_uri = $config['redirect_uri'];
        $this->code = $config['code'];
        $this->refresh_diff_time = $config['refresh_diff_time'];
    }

    /**
     * 获取站点列表
     * @return array|mixed|string
     */
    public function getSiteList(){
        $url = "https://openapi.baidu.com/rest/2.0/tongji/config/getSiteList?access_token=".$this->access_token;
        $jsonResult = curl_request($url);
        $result = json_decode($jsonResult, true);
        if(isset($result['error_code'])){
            print_r("获取站点列表:".$jsonResult."\n");
        }
        dd($result);
    }

    /**
     * 获取统计数据
     * @param $method
     * @param $start_date
     * @param $end_date
     * @param array $others
     * @param int $site_id
     * @param string $metrics
     * @return mixed
     */
    public function getData($method, $start_date, $end_date, $others = [], $site_id = 0, $metrics = self::metrics_default){
        $url = "https://openapi.baidu.com/rest/2.0/tongji/report/getData?";
        $content = [
            'access_token' => $this->access_token,
            'site_id' => $site_id,
            'method' => $method,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'metrics' => $metrics
        ];
        if(!empty($others)){
            $content = array_merge($content, $others);
        }
        $params = http_build_query($content,'&');
        $url = $url.$params;
        $jsonResult = curl_request($url);
        if(!$jsonResult){
            print_r("接口返回数据为空\n");
        }
        $result = json_decode($jsonResult, true);
        if(isset($result['error_code'])){
            print_r($jsonResult."\n");
        }
        return $result;
    }
}
