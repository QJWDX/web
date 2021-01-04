<?php


namespace App\Http\Controllers\File;


use App\Handlers\ExportHandler;
use App\Handlers\UploadHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Common\Files;
use App\Models\Common\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public function index(Request $request, Files $files, ExportHandler $exportHandler){
        $type = $request->get('type');
        $title = $request->get('title');
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $export = $request->get('export', 0);
        $list = $files->getList(compact('type', 'title', 'startTime', 'endTime', 'export'));
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
        $files = new Files();
        $menu = $files->getRow(['id' => $id]);
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
     * 获取格式下文件夹名称
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderSelect(Request $request){
        $type = $request->get('type');
        if (!in_array($type, array_keys(config('filesystems.uploader.type')))){
            return $this->error(500,'文件类型不存在');
        }
        $folders = config('filesystems.uploader.folder.'.$type);
        return $this->success($folders);
    }


    public function del(DelRequest $request, Notifications $notifications){
        $ids = $request->get('ids');
        if($notifications->del($ids)){
            return $this->success('删除消息通知成功');
        }
        return $this->error(500, '删除消息通知失败');
    }


    /**
     * 文件上传
     * @param Request $request
     * @param UploadHandler $uploadHandler
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function upload(Request $request, UploadHandler $uploadHandler){
        $type = $request->get('type', false);
        $folder = $request->get('folder', false);
        $files = $request->file('file');
        foreach ($files as $key => $file){
            $uploadHandler->storeFile($file, $type, $folder);
        }
        return $this->success('上传成功');
    }

    /**
     * 下载文件
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id){
        $file = Files::query()->find($id);
        if(!Storage::disk($file['disks'])->exists($file['path'])){
            return redirect(config('app.url').'#/404');
        }
        $file->increment('downloads');
        $file->save();
        $path = public_path($file['disks'].'/'.$file['path']);
        return response()->download($path);
    }
}
