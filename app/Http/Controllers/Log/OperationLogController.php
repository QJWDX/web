<?php


namespace App\Http\Controllers\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Log\OperationLog;
use Illuminate\Http\Request;
use Dx\Role\Models\User;
class OperationLogController extends Controller
{
    public function index(Request $request, OperationLog $operationLog, User $user){
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $username = $request->get('username');
        $user_id = [];
        if($username){
            $user_id = $user->newQuery()->where('username', 'like', '%'.$username.'%')->pluck('id')->toArray();
        }
        $list = $operationLog->getLogList(compact('startTime', 'endTime', 'user_id'));
        return $this->success($list);
    }

    public function show($id){
        $operationLog = new OperationLog();
        $menu = $operationLog->getLogInfo(['id' => $id]);
        return $this->success($menu);
    }

    public function destroy($id)
    {
        $loginLog = new OperationLog();
        $result = $loginLog->newQuery()->where('id', $id)->delete();
        if($result){
            return $this->success('删除日志成功');
        }
        return $this->error('删除日志失败');
    }

    public function delOperationLog(DelRequest $request, OperationLog $operationLog){
        $ids = $request->get('ids', []);
        if($operationLog->del($ids)){
            return $this->success('删除日志成功');
        }
        return $this->error('删除日志失败');
    }
}
