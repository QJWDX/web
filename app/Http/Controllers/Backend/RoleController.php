<?php


namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Common\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $M;
    public function __construct(Role $role)
    {
        $this->M = $role;
    }

    public function index(Request $request){
        $role_name = $request->get('role_name', false);
        $list = $this->M->getList(compact('role_name'));
        return $this->success($list);
    }


    public function store(Request $request){
        $data = $request->only([
            'role_name',
            'description',
            'is_super'
        ]);
        $res = $this->M->newQuery()->create($data);
        if($res){
            return $this->success('新增菜单成功');
        }
        return $this->error('新增菜单失败');
    }


    public function show($id){
        $menu = $this->M->getRow(['id' => $id]);
        return $this->success($menu);
    }


    public function update(Request $request, $id){
        $data = $request->only([
            'role_name',
            'description',
            'is_super'
        ]);
        $res = $this->M->newQuery()->where('id', $id)->update($data);
        if($res){
            return $this->success('编辑成功');
        }
        return $this->error('编辑失败');
    }

    /**
     * 批量删除角色
     * @param DelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delRole(DelRequest $request){
        $ids = $request->get('ids');
        if($this->M->hasSuperRole($ids)){
            return $this->error(500, '选中项有超级管理员不允许删除，请重新选择');
        }
        $this->M->del($ids);
        return $this->success('删除成功');
    }

    /**
     * 获取角色树形列表
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleTree(){
        $list = $this->M->getAll(['id', 'role_name', 'is_super']);
        $treeData = [];
        foreach ($list as $value){
            array_push($treeData, ['id' => $value['id'], 'label' => $value['role_name'] .'['. ($value->is_super ? 'super' : 'other') . ']']);
        }
        return $this->success($treeData);
    }
}
