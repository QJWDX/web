<?php


namespace App\Http\Middleware;


use App\Exceptions\ApiRequestExcept;
use App\Service\RedisRsa;
use App\Service\Rsa;
use Illuminate\Http\Request;

class RsaBeforeMiddleware
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws ApiRequestExcept
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($request->method() == 'OPTIONS') {
            return $next($request);
        }
        // 判断是否有一个叫encryptKey
        if ($request->hasHeader("encryptKey")) {
            // 拿到Private_key
            $encrypt_key = trim($request->header("encryptKey"));

            $key = RedisRsa::getFlashRsaKey($encrypt_key);

            if (!$key) throw new ApiRequestExcept('encrypt_key错误', 500);

            //解密Post请求中的数据
            $encrypt_data = $request->input("encrypt_data");
            if (!$encrypt_data) throw new  ApiRequestExcept('密文不存在', 500);
            $dataJson = Rsa::rsaDecrypt($encrypt_data, $key);
            if (!$dataJson) throw new  ApiRequestExcept('密文错误', 500);
            $data = json_decode($dataJson, true);
            foreach ($data as $key => $val) {
                $request->request->set($key, $val);
                $request->merge([$key => $val]);
            }
        }
        return $next($request);
    }
}
