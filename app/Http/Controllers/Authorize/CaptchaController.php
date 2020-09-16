<?php


namespace App\Http\Controllers\Authorize;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class CaptchaController extends Controller
{
    public function getCaptcha(){
        $captcha = app('captcha')->create('flat', true);
        $key = 'captcha_'.$captcha['key'];
        Redis::connection()->setex($key, config('login.captcha_ttl', 60*5), $captcha['key']);
        return $this->success($captcha);
    }
}
