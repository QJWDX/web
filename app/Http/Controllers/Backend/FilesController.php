<?php


namespace App\Http\Controllers\Backend;


use App\Handlers\ExportHandler;
use App\Http\Controllers\Controller;
use App\Models\Common\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    private $M;
    public function __construct(Files $files)
    {
        $this->M = $files;
    }

    public function index(Request $request, ExportHandler $exportHandler){
        $type = $request->get('type');
        $title = $request->get('title');
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $export = $request->get('export', 0);
        $list = $this->M->getList(compact('type', 'title', 'startTime', 'endTime', 'export'));
        $data = $export ? $list : $list['items'];
        $data = collect($data)->transform(function ($item){
            $item['download_url'] = config('filesystems.disks.'.$item['disks'].'.url').$item['path'];
            return $item;
        });
        if($export){
            $url = $exportHandler->filesDataExport($data->toArray());
            return $this->success(['download_url' => $url]);
        }
        $list['items'] = $data;
        return $this->success($list);
    }



    public function show($id){
        $menu = $this->M->getRow(['id' => $id]);
        return $this->success($menu);
    }

    /**
     * 文件类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeSelect(){
        $types = config('filesystems.uploader.type');
        return $this->success($types);
    }

    /**
     * 下载文件
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id){
        $file = $this->M->newQuery()->find($id);
        if(!Storage::disk($file['disks'])->exists($file['path'])){
            return $this->error(500, '文件不存在');
        }
        $path = public_path($file['disks'].'/'.$file['path']);
        return response()->download($path);
    }
}
