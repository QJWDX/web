<?php


namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * 退出登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(false);
            return $this->success('User logged out successfully');
        } catch (JWTException $exception) {
            return $this->error(500, 'Sorry, the user cannot be logged out');
        }
    }

    /**
     * 获取授权用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUser(Request $request)
    {
        $user = JWTAuth::authenticate($request);
        return $this->success(['user' => $user]);
    }


    /**
     * 新增用户
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUser(Request $request, User $user)
    {
        $result = $user->newQuery()->create([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
            'tel' => $request->get('tel'),
            'id_card' => $request->get('id_card'),
            'sex' => $request->get('sex'),
            'address' => $request->get('address'),
            'head_img_url' => $request->get('head_img_url')
        ]);
        if(!$result){
            return $this->error(500, 'registration failed');
        }
        return $this->success($result);
    }
}

