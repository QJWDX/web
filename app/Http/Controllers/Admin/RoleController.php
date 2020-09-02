<?php


namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Base\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * 角色列表
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleList(Request $request, Role $role){
        $data = $role->roleList($request);
        return $this->success($data);
    }

    /**
     * 获取角色信息
     * @param $id
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleInfo($id, Role $role){
        $result = $role->newQuery()->find($id);
        if($result){
            $data = $result->toArray();
            $data['is_super'] = $data['is_super'] == '是' ? 1 : 0;
            return $this->success($data);
        }
        return $this->error(500, '获取失败');
    }

    /**
     * 新增角色
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function addRole(Request $request, Role $role){
        $roleName = $request->get('role_name', false);
        $description = $request->get('description');
        $is_super = $request->get('is_super', 0);
        $parent_id = $request->get('parent_id', 0);
        if($roleName == false){
            return $this->error(500, '参数错误');
        }
        $res = $role->newQuery()->insert([
            'role_name' => $roleName,
            'description' => $description,
            'is_super' => $is_super,
            'parent_id' => $parent_id,
        ]);
        if($res){
            return $this->success('新增成功');
        }
        return $this->error(500, '新增失败');
    }

    /**
     * 编辑角色
     * @param Request $request
     * @param $id
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function modRole(Request $request, $id, Role $role){
        $roleName = $request->get('role_name');
        $description = $request->get('description');
        $is_super = $request->get('is_super');
        $res = $role->newQuery()->where('id', $id)->update([
            'role_name' => $roleName,
            'description' => $description,
            'is_super' => $is_super
        ]);
        if($res){
            return $this->success('编辑成功');
        }
        return $this->error(500, '编辑失败');
    }

    /**
     * 删除角色
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function delRole(Request $request, Role $role){
        $ids = $request->get('ids', false);
        if(!$ids){
            return $this->error(500, '');
        }
        $ids = explode(',', $ids);
        if(empty($ids)){
            return $this->error(500, '参数错误');
        }
        $has_super = $role->newQuery()->whereIn('id', $ids)->where('is_super', 1)->exists();
        if($has_super){
            return $this->error(500, '选中项有超级管理员不允许删除，请重新选择');
        }
        $res = $role->newQuery()->whereIn('id', $ids)->delete();
        if($res){
            return $this->success('删除成功');
        }
        return $this->error(500, '删除失败');
    }
}
