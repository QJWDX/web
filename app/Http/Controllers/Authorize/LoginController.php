<?php


namespace App\Http\Controllers\Authorize;
use App\Events\UserLogin;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\Common\UserRole;
use App\Models\User;
use App\Service\Rsa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    // 注册后是否登录
    public $loginAfterSignUp = true;

    // 注册用户
    public function register(RegisterAuthRequest $request)
    {
        $user = new User();
        $result = $user->newQuery()->create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password'))
        ]);
        if(!$result){
            return $this->error(500, 'registration failed');
        }
        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }
        return $this->success($result);
    }

    // 登录
    public function login(Request $request, UserRole $userRole)
    {
        $captcha = $request->get('captcha_code', false);
        $key = $request->get('captcha_key', false);
        if(!$captcha || !$key){
            return $this->error(500, '参数错误');
        }
        if(!Redis::connection()->get('captcha_'.$key)){
            return $this->error(500, '验证码过期，请重新获取');
        }
        if(!captcha_api_check($captcha, $key)){
            return $this->error(500, '验证码错误');
        }
        $user = new User();
        $input = $request->only('username', 'password');
        if(array_keys($input) !== ['username', 'password']){
            return $this->error(500, 'Parameter error');
        }
        $is_exists = $user->newQuery()
            ->where('username', $input['username'])
            ->exists();
        if(!$is_exists){
            return $this->error(500, '用户不存在');
        }
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return $this->error(401, '密码错误');
        }
        $user = auth()->user();
        if(!$user['status']){
            return $this->error(500, '该用户已禁用，请联系管理员');
        }
        event(new UserLogin($user));
        $role_ids = $userRole->newQuery()->where('user_id', $user['id'])->pluck('role_id')->toArray();
        $user['role'] = implode(',',$role_ids);
        return $this->success([
            'token' => $jwt_token,
            'user' => $user
        ], 200, '登录成功');
    }


    /**
     * 获取rsa密钥
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrivateKey()
    {
        $keys = Rsa::rsaCreateKey();
        //生成一个key储存到redis中。
        $redis_key = config("rsa.redis_prefix") . $this->uuid();
        Redis::connection()->setex($redis_key, config('rsa.ttl'), $keys['private_key']);
        return $this->success([
            'public_key' => $keys['public_key'], "key" => $redis_key
        ]);
    }
}

