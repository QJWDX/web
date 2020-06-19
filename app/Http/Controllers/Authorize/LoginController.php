<?php


namespace App\Http\Controllers\Authorize;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\Base\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

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
            return $this->error(500, 'User does not exist');
        }
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return $this->error(401, 'Invalid username or Password');
        }
        $user = auth()->user();
        $role_ids = $userRole->newQuery()->where('user_id', $user['id'])->pluck('role_id')->toArray();
        $user['role'] = implode(',',$role_ids);
        return $this->success([
            'token' => $jwt_token,
            'user' => $user
        ], 200, '登录成功');
    }
}

