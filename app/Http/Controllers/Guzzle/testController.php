<?php


namespace App\Http\Controllers\Guzzle;


use App\Http\Controllers\Controller;
use App\Service\Http;
use Illuminate\Support\Facades\Log;

class testController extends Controller
{
    private $query;
    private $url = '';
    private $sessionId;
    protected $header = [];

    public function __construct()
    {
        $this->header['Content-type'] = 'application/json';
        $this->header['X-Openerp-Session-id'] = $this->sessionId;
    }

    public function http(){
        $this->query['params']['user'] = "lzx";
        $this->query['params']['password'] = '1111';
        $source = $this->getSourceData($this->url, 'post', $this->query ,$this->header);
        var_dump($source);
    }


    /**
     * @param $url
     * @param string $method
     * @param array $query
     * @param array $header
     * @return \Illuminate\Support\Collection|mixed
     */
    protected function getSourceData($url, $method = 'get', $query = [], $header = [])
    {
        $source = [];

        $query = array_merge($query);

        try {
            $header = array_merge($header, ['verify' => false]);

            $source = Http::httpRequire($url, $query, $header, $method);

        } catch (\Exception $exception) {
            Log::channel("api")->error($exception->getMessage());
        }

        return $source['result'] instanceof Collection ? $source['result'] : collect($source['result']);
    }
}
