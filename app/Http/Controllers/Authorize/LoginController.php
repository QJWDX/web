<?php


namespace App\Http\Controllers\Authorize;
use App\Events\UserLogin;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\Common\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}

