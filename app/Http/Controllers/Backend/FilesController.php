<?php


namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use App\Models\Common\Files;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    private $M;
    public function __construct(Files $files)
    {
        $this->M = $files;
    }

    public function index(Request $request){
        $type = $request->get('type');
        $title = $request->get('title');
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $list = $this->M->getList(compact('type', 'title', 'startTime', 'endTime'));
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
            return $this->success('上传文件成功');
        }
        return $this->error('上传文件失败');
    }



    public function show($id){
        $menu = $this->M->getRow(['id' => $id]);
        return $this->success($menu);
    }


    public function update(Request $request, $id){
        $data = $request->only([
            'title',
        ]);
        $res = $this->M->newQuery()->where('id', $id)->update($data);
        if($res){
            return $this->success('编辑成功');
        }
        return $this->error('编辑失败');
    }

    /**
     * 文件类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeSelect(){
        $types = config('filesystems.uploader.type');
        return $this->success($types);
    }
}
