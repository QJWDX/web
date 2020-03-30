<?php


namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public $loginAfterSignUp = true;

    // 注册用户
    public function register(RegisterAuthRequest $request)
    {
        $user = new User();
        $result = $user->newQuery()->create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => $request->get('password')
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
    public function login(Request $request)
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
        return $this->success([
            'token' => $jwt_token
        ]);
    }
}

