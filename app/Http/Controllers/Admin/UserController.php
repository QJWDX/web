<?php

namespace App\Http\Controllers\Admin;

use App\Handlers\UploadHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Common\User;
use App\Models\Common\UserRole;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * 用户列表
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, User $user)
    {
        $list = $user->getDataList($request);
        return $this->success($list);
    }

    /**
     * 新增用户
     * @param UserRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request, User $user)
    {
        $data = $request->only(['name', 'username', 'email', 'tel', 'sex', 'status']);
        $data['password'] = bcrypt('123456');
        $res = $user->newQuery()->create($data);
        if($res){
            return $this->success('新增用户成功');
        }
        return $this->error('新增用户失败');
    }

    /**
     * 查看用户
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = new User();
        $data = $user->newQuery()->select(['id', 'name', 'username', 'email', 'tel', 'sex', 'status'])->find($id);
        return $this->success($data);
    }


    /**
     * 更新用户
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->only(['name', 'username', 'email', 'tel', 'sex', 'status']);
        $user = new User();
        $res = $user->newQuery()->where('id', $id)->update($data);
        if($res){
            return $this->success('编辑用户成功');
        }
        return $this->error('编辑用户失败');
    }


    /**
     * 删除用户
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = new User();
        $res = $user->newQuery()->where('id', $id)->delete();
        if($res){
            return $this->success('删除用户成功');
        }
        return $this->error('删除用户失败');
    }


    /**
     * 获取用户角色id数组
     * @param $id
     * @param UserRole $userRole
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRole($id, UserRole $userRole){
        $roles = $userRole->newQuery()->where('user_id', $id)->distinct()->pluck('role_id');
        return $this->success($roles);
    }

    /**
     * 设置用户角色
     * @param $id
     * @param Request $request
     * @param UserRole $userRole
     * @return \Illuminate\Http\JsonResponse
     */
    public function setUserRole($id, Request $request, UserRole $userRole){
        $role = $request->get('role');
        if(!is_array($role)){
            return $this->error('参数错误');
        }
        $userRole->newQuery()->where('user_id', $id)->delete();
        $insertData = [];
        if(!empty($role)){
            for ($i=0; $i<count($role); $i++){
                array_push($insertData, ['user_id' => $id, 'role_id' => $role[$i]]);
            }
            $userRole->newQuery()->insert($insertData);
        }
        return $this->success('角色设置成功');
    }

    /**
     * 头像上传
     * @param Request $request
     * @param UploadHandler $uploadHandler
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function uploadAvatar(Request $request, UploadHandler $uploadHandler, User $user){
        $id = $request->route('id');
        if(!$user->hasUser($id)){
            return $this->error('用户不存在');
        }
        $file = $request->file('file');
        $data = $uploadHandler->storeFile($file, 'image', 'avatar');
        $res = $user->newQuery()->where('id', $id)->update(['avatar' => $data['path']]);
        if($res){
            return $this->success($data);
        }
        return $this->error('头像更新失败');
    }
}
