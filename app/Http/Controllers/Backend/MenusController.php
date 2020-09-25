<?php


namespace App\Http\Controllers\Backend;


use App\Handlers\CategoryHandler;
use App\Http\Controllers\Controller;
use App\Models\Common\Menus;
use App\Models\Common\Role;
use App\Models\Common\RoleMenus;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    private $model;
    public function __construct(Menus $menus)
    {
        $this->model = $menus;
    }

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
     * @param Role $role
     * @param Menus $menus
     * @param RoleMenus $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleMenus(Request $request, Role $role, Menus $menus, RoleMenus $roleMenus){
        $role_id = $request->get('role', false);
        if(!$role_id) return $this->error('参数错误');
        $role = $role->newQuery()->find($role_id);
        if($role){
            if($role->getOriginal('is_super') == 1){
                $checkMenus = $menus->newQuery()->pluck('id');
            }else{
                $checkMenus = $roleMenus->newQuery()->where('role_id', $role_id)->distinct()->pluck('menus_id');
            }
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
            $menu_ids = (new RoleMenus())->newQuery()->whereIn('role_id', $ids)->distinct()->pluck('menus_id')->toArray();
        }
        $permissionData = $menus->permissionMenusAndRoute($isSuper, $menu_ids);
        return $this->success($permissionData);
    }

    /**
     * 设置角色权限菜单
     * @param Request $request
     * @param Role $role
     * @param RoleMenus $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function setRoleMenus(Request $request, Role $role, RoleMenus $roleMenus){
        $role_id = $request->get('role', false);
        $menus = $request->get('menus', false);
        $insert_data = [];
        if(is_array($menus)){
            $currentRole = $role->newQuery()->find($role_id);
            if($currentRole->getOriginal('is_super') == 1){
                return $this->success('超级管理员拥有所有权限');
            }
            $roleMenus->newQuery()->where('role_id', $role_id)->delete();
            foreach ($menus as $menu){
                array_push($insert_data, ['role_id' => $role_id, 'menus_id' => $menu]);
            }
            $res = $roleMenus->newQuery()->insert($insert_data);
            if($res){
                return $this->success('权限菜单配置成功');
            }
            return $this->error('权限菜单配置失败');
        }
        return $this->error('参数错误');
    }


    // 菜单列表
    public function index(Menus $menus){
        $list = $menus->getList();
        return $this->success($list);
    }


    // 新增菜单
    public function store(Request $request, Menus $menus){
        $data = $request->only([
            'name',
            'parent_id',
            'icon',
            'path',
            'component',
            'is_related_route',
            'is_show',
            'is_default',
            'sort_field'
        ]);
        $res = $menus->newQuery()->create($data);
        if($res){
            return $this->success('新增菜单成功');
        }
        return $this->error('新增菜单失败');
    }


    // 菜单详情
    public function show($id){
        $menu = $this->model->newQuery()->find($id);
        return $this->success($menu);
    }


    // 更新菜单
    public function update(Request $request, $id){
        $data = $request->only([
            'name',
            'parent_id',
            'icon',
            'path',
            'component',
            'is_related_route',
            'is_show',
            'is_default',
            'sort_field'
        ]);
        $res = $this->model->newQuery()->where('id', $id)->update($data);
        if($res){
            return $this->success('编辑菜单成功');
        }
        return $this->error('编辑菜单失败');
    }

    // 删除菜单
    public function destroy($id){
        if($this->model->hasSubMenu($id)){
            return $this->success('含有子菜单,不允许删除');
        }
        $res = $this->model->newQuery()->where('id', $id)->delete();
        if($res){
            return $this->success('删除菜单成功');
        }
        return $this->error('删除菜单失败');
    }


    /**
     * 父级菜单下拉框
     * @param CategoryHandler $categoryHandler
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuSelect(CategoryHandler $categoryHandler){
        $menus = $this->model->newQuery()->select(['id', 'parent_id', 'name'])->get();
        return $this->success($categoryHandler->select($menus, 0));
    }
}
