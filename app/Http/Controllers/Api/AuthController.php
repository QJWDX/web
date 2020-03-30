<?php


namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // 退出登录
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(false);
            return $this->success('User logged out successfully');
        } catch (JWTException $exception) {
            return $this->error(500, 'Sorry, the user cannot be logged out');
        }
    }

    // 获取授权用户信息
    public function getAuthUser(Request $request)
    {
        $user = JWTAuth::authenticate($request);
        return $this->success(['user' => $user]);
    }
}

