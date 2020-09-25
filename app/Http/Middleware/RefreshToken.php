<?php

namespace App\Http\Middleware;
use App\Exceptions\InvalidUserException;
use App\Http\Controllers\Backend\Auth\CaptchaController;
use App\Http\Controllers\Backend\Auth\LoginController;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class RefreshToken extends BaseMiddleware
{
    protected $exceptedClass = [
        LoginController::class,
        CaptchaController::class
    ];

    protected $exceptedActions = [];

    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        //去除对options的验证
        if ($request->method() == 'OPTIONS') {
            return $next($request);
        }

        //去除对某些类与方法的验证
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $this->checkForToken($request);
        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->parseToken()->authenticate()) {
                //使这个系统登录成功
                //单用户登陆
                $this->checkPayload();
                Auth::login($this->auth->user());
                return $next($request);
            }
            $token = $this->auth->refresh();
            // 使用一次性登录以保证此次请求的成功
        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                // 刷新用户的 token
                $token = $this->auth->refresh();
                // 使用一次性登录以保证此次请求的成功
                Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
            } catch (TokenBlacklistedException $exception) {
                throw new InvalidUserException(401, 'token黑名单');
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                throw new InvalidUserException(403, $exception->getMessage());
            }
        } catch (TokenBlacklistedException $exception) {
            throw new InvalidUserException(401, 'token黑名单');
        }
        // 在响应头中返回新的 token
        return response(['code' => 201, 'server_time' => time(), 'message' => '', 'data' => ["token" => $token]], 201);
//        return $this->setAuthenticationHeader($next($request), $token);
    }

    /**
     * @param $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        //去除某一些类
        list($class, $method) = explode('@', $request->route()->getActionName());

        if (in_array($class, $this->exceptedClass) or in_array($method, $this->exceptedActions)) {
            return true;
        }

        return false;
    }

    public function checkPayload()
    {
        if (config("login.open_single_sign_on", false) === false) {
            return;
        }
        $array = $this->auth->payload()->jsonSerialize();
        $user = $this->auth->user();
        $key = sprintf('user_login#%s', $user->username);
        $str = Redis::connection()->get($key);

        if (!isset($array["signature"]) and isset($array['force']) and $array['force'] === 1) {
            return;
        }
        if (!isset($array['signature']) or $array['signature'] !== $str) {
            //拉黑token，报错
            $this->auth->invalidate();
            throw new InvalidUserException(401, '该账号已在其他地方登录，请重新登录');
        }
    }
}
