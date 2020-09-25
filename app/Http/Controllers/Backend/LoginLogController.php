<?php


namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Common\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    private $model;
    public function __construct(LoginLog $loginLog)
    {
        $this->model = $loginLog;
    }

    public function index(Request $request){
        $where = $request->all();
        $list = $this->model->getList($where);
        return $this->success($list);
    }

    public function show($id){
        $menu = $this->model->getRow(['id' => $id]);
        return $this->success($menu);
    }

    public function delLoginLog(DelRequest $request){
        $ids = $request->get('ids');
        if($this->model->del($ids)){
            return $this->success('删除日志成功');
        }
        return $this->error(500, '删除日志失败');
    }
}
