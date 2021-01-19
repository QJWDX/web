<?php


namespace App\Service\BaiDu;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use function App\Repositories\Common\tocurl;

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
            $this->refresh_access_token(true);
        }else{
            $tokenInfo = $this->getTokenInfo();
            $this->access_token = $tokenInfo['access_token'];
            $this->refresh_token = $tokenInfo['refresh_token'];
            // 距离token过期多少秒刷新token, 默认两小时
            if($tokenInfo['expires_in'] - time() < $this->refresh_diff_time){
                $this->refresh_access_token();
            }
        }
    }

    /**
     * redis或者文件中获取token信息,redis数据丢失则去json文件中取
     * @return mixed
     */
    public function getTokenInfo(){
        try {
            $hasCache = Redis::connection()->exists($this->redisKey);
            if($hasCache){
                return json_decode(Redis::connection()->get($this->redisKey), true);
            }
            return json_decode(file_get_contents(public_path($this->jsonFileName)), true);
        }catch (\Exception $exception){
            print_r("获取token信息失败\n");
            print_r($exception->getMessage()."\n");
        }
    }

    /**
     * 刷新access_token
     * @param bool $useCode
     */
    public function refresh_access_token($useCode = false){
        $header = [
            "Content-Type: application/json; charset=utf-8"
        ];
        $url = "http://openapi.baidu.com/oauth/2.0/token?";
        $params['grant_type'] = 'refresh_token';
        $params['refresh_token'] = $this->refresh_token;
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if($useCode){
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $this->code;
            $params['redirect_uri'] = $this->redirect_uri;
            unset($params['refresh_token']);
        }
        $url = $url . http_build_query($params);
        $jsonData = tocurl($url, $header, 'GET');
        $result = json_decode($jsonData, true);
        if(isset($result['access_token'])){
            // 默认返回过期时间是一个月的时间戳 加上当前时间
            $result['expires_in'] = time() + $result['expires_in'];
            $cacheData = json_encode($result);
            Redis::connection()->set($this->redisKey, $cacheData);
            file_put_contents(public_path($this->jsonFileName), $cacheData);
            $this->access_token = $result['access_token'];
            $this->refresh_token = $result['refresh_token'];
        }else{
            print_r("刷新token出错\n");
            print_r($result);
            print_r("\n");
            Log::debug("[statistic:baidu_refresh_token]执行出错:");
        }
    }


    public function setConfig()
    {
        $config = config("baidu.baiduTj");
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
        $url = "https://openapi.baidu.com/rest/2.0/tongji/config/getSiteList?access_toen=".$this->access_token;
        $rs = tocurl($url, [], 'GET');
        $result = json_decode($rs, true);
        if(isset($result['error_code'])){
//            print_r($rs."\n");
        }
        return $result;
    }

    /**
     * 获取统计数据
     * @param $method
     * @param $start_date
     * @param $end_date
     * @param array $others
     * @param string $metrics
     * @param string $site_id
     * @return array|mixed|string
     */
    public function getData($method,$start_date,$end_date,$others=[],$site_id='15326924',$metrics=self::metrics_default){
        $url="https://openapi.baidu.com/rest/2.0/tongji/report/getData?";
        $content=[
            'access_token'=>$this->access_token,
            'site_id'=>$site_id,
            'method'=>$method,
            'start_date'=>$start_date,
            'end_date'=>$end_date,
            'metrics'=>$metrics
        ];
        if(!empty($others)){
            $content = array_merge($content,$others);
        }
        $params = http_build_query($content,'&');
        $url = $url.$params;
        $rs = tocurl($url,[],'GET');
        if($rs){
            $result = json_decode($rs, true);
            if(isset($result['error_code'])){
//                print_r($rs."\n");
            }
            return $result;
        }else{
            print_r("接口返回数据为空\n");
        }
    }
}
