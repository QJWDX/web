<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Base\Menus;
use App\Models\Base\Role;
use App\Models\Base\RoleMenus;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    /**
     * 权限菜单树
     * @param Request $request
     * @param Role $role
     * @param Menus $menus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuTree(Request $request, Role $role, Menus $menus){
        $role_id = $request->get('role', false);
        if(!$role_id) return $this->error('参数错误');
        $role = $role->newQuery()->find($role_id);
        if($role){
            return $this->success($menus->getElTree($role->getOriginal('is_super')));
        }
        return $this->error('角色不存在');
    }


    /**
     * 获取角色已选中的菜单
     * @param Request $request
     * @param RoleMenus $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleMenus(Request $request, Role $role, Menus $menus, RoleMenus $roleMenus){
        $role_id = $request->get('role', false);
        if(!$role_id) return $this->error('参数错误');
        $role = $role->newQuery()->find($role_id);
        if($role){
            $checkMenus = $roleMenus->newQuery()->where('role_id', $role_id)->distinct()->pluck('menus_id');
            return $this->success($checkMenus);
        }
        return $this->error('角色不存在');
    }


    /**
     * 获取角色权限菜单和路由
     * @param Request $request
     * @param Role $role
     * @param Menus $menus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVueRoute(Request $request, Role $role, Menus $menus){
        $role_id = $request->get('role');
        $ids = explode(',', $role_id);
        $isSuper = $role->newQuery()->where('is_super', 1)->whereIn('id', $ids)->exists();
        $menu_ids = array();
        if(!$isSuper){
            $menu_ids = (new RoleMenus())->newQuery()->where('role_id', $role_id)->pluck('menus_id');
        }
        $permissionData = $menus->permissionMenusAndRoute($isSuper, $menu_ids);
        return $this->success($permissionData);
    }

    /**
     * 设置角色权限菜单
     * @param Request $request
     * @param RoleMenus $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function setRoleMenus(Request $request, RoleMenus $roleMenus){
        $role = $request->get('role', false);
        $menus = $request->get('menus', false);
        $insert_data = [];
        if(is_array($menus)){
            $roleMenus->newQuery()->where('role_id', $role)->delete();
            foreach ($menus as $menu){
                array_push($insert_data, ['role_id' => $role, 'menus_id' => $menu]);
            }
            $res = $roleMenus->newQuery()->insert($insert_data);
            if($res){
                return $this->success('权限菜单配置成功');
            }
            return $this->error('权限菜单配置失败');
        }
        return $this->error('参数错误');
    }
}
