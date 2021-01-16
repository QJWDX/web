<?php


namespace App\Http\Controllers\Files;


use App\Handlers\ExportHandler;
use App\Handlers\UploadHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\DelRequest;
use App\Models\Common\Files;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    public function index(Request $request, Files $files, ExportHandler $exportHandler){
        $type = $request->get('type');
        $title = $request->get('title');
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $export = $request->get('export', 0);
        $list = $files->getFileList(compact('type', 'title', 'startTime', 'endTime', 'export'));
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


    /**
     * 文件详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id){
        $files = new Files();
        $result = $files->row($id);
        return $this->success($result);
    }


    /**
     * 文件类型下拉框
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeSelector(){
        $types = config('filesystems.uploader.type');
        return $this->success($types);
    }

    /**
     * 文件夹列表下拉框
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderSelector(Request $request){
        $type = $request->get('type');
        if (!in_array($type, array_keys(config('filesystems.uploader.type')))){
            return $this->error('文件类型不存在');
        }
        $folders = config('filesystems.uploader.folder.'.$type);
        return $this->success($folders);
    }


    /**
     * 文件删除
     * @param DelRequest $request
     * @param Files $files
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(DelRequest $request, Files $files){
        $ids = $request->get('ids');
        if($files->del($ids)){
            return $this->success('删除成功');
        }
        return $this->error('删除失败');
    }


    /**
     * 文件上传
     * @param Request $request
     * @param UploadHandler $uploadHandler
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function upload(Request $request, UploadHandler $uploadHandler){
        $title = $request->get('title');
        $type = $request->get('type');
        $folder = $request->get('folder');
        $files = $request->file('file');
        foreach ($files as $file){
            $uploadHandler->storeFile($file, $type, $folder,$title);
        }
        return $this->success('上传成功');
    }
}
