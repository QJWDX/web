<?php


namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Common\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $model;
    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    public function index(){
        $list = $this->model->getList();
        return $this->success($list);
    }


    public function store(Request $request){
        $data = $request->only([
            'role_name',
            'description',
            'is_super'
        ]);
        $res = $this->model->newQuery()->create($data);
        if($res){
            return $this->success('新增菜单成功');
        }
        return $this->error('新增菜单失败');
    }


    public function show($id){
        $menu = $this->model->getRow(['id' => $id]);
        return $this->success($menu);
    }


    public function update(Request $request, $id){
        $data = $request->only([
            'role_name',
            'description',
            'is_super'
        ]);
        $res = $this->model->newQuery()->where('id', $id)->update($data);
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
        if($this->model->hasSuperRole($ids)){
            return $this->error(500, '选中项有超级管理员不允许删除，请重新选择');
        }
        $this->model->del($ids);
        return $this->success('删除成功');
    }

    /**
     * 获取角色树形列表
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoleTree(){
        $list = $this->model->getAll(['id', 'role_name', 'is_super']);
        $treeData = [];
        foreach ($list as $value){
            array_push($treeData, ['id' => $value['id'], 'label' => $value['role_name'] .'['. ($value->is_super ? 'super' : 'other') . ']']);
        }
        return $this->success($treeData);
    }
}
