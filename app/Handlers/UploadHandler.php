<?php


namespace App\Handlers;

use App\Exceptions\ApiException;
use App\Models\Files\Files;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadHandler
{
    private $FileModel;

    public function __construct(Files $files)
    {
        $this->FileModel = $files;
    }

    /**
     * 保存文件
     * @param  UploadedFile $file
     * @param $type
     * @param $folder
     * @param string $disks
     * @param string $title
     * @return array
     * @throws ApiException
     */
    public function storeFile($file, $type, $folder, $title = '', $disks = 'upload'){
        $folder_name = $type."/$folder/" . date("Ym", time()) . '/'.date("d", time());
        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->extension()) ? : 'png';
        // 检查文件后缀是否是规则允许后缀
        if (!in_array($extension, config('filesystems.uploader.'.$type.'.allowed_ext'))){
            throw new ApiException('文件格式错误，请检查文件后缀', 500);
        }
        if (!in_array($folder, config('filesystems.uploader.folder.'.$type))){
            throw new ApiException('文件夹错误', 500);
        }
        // 原始文件名
        $title = $title ? $title.'.'.$extension : $file->getClientOriginalName();

        // 获取文件的 Mime
        $mimeType = $file->getClientMimeType();

        // 获取文件大小
        $size = $file->getSize();

        // 获取文件 MD5 值
        $md5 = md5_file($file->getPathname());

        // 检查文件是否已上传过
        if($fileModel = $this->checkFile($md5, $type, $folder)){
            return [
                'full_path' => config(''.$disks) . $fileModel->path,
                'path' => $fileModel->path
            ];
        }

        // 实例化 Image 对象
        if($type == 'image'){
            $image = Image::make($file->getPathname());
            $width = $image->width();
            $height = $image->height();
        }else{
            // 文件无宽度属性。默认 0
            $width = 0;
            $height = 0;
        }
        $dir = config('filesystems.disks.'.$disks.'.root').DIRECTORY_SEPARATOR.$folder_name;
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        $fileName = $this->createRandomCode(24) . '.' . $extension;
         // 将图片移动到我们的目标存储路径中 或 云存储中
        if(!($path = $file->storeAs($folder_name, $fileName, $disks))){
            throw new ApiException('文件存储失败', 500);
        }
        $fileModel = $this->saveFile($path, $disks, $type, $folder, $title, $mimeType, $md5, $size, $width, $height);
        if($fileModel){
            return [
                'url' => config('filesystems.disks.'.$disks.'.url').$fileModel['path'],
                'path' => $fileModel['path']
            ];
        }else{
            Storage::disk($disks)->delete($path);
            throw new ApiException('文件信息存储数据库失败', 500);
        }
    }


    /**
     * 检查文件是否已存在
     * @param $md5
     * @param $type
     * @param $folder
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function checkFile($md5, $type, $folder){
        $where = [
            ['md5', '=', $md5],
            ['type','=',$type],
            ['folder', '=', $folder]
        ];
        return $this->FileModel->getFile($where);
    }


    // 保存到数据库
    public function saveFile($path, $disks, $type, $folder, $title, $mime_type, $md5, $size, $width, $height){
        $uid = $this->FileModel->uuid();
        $params = compact('uid', 'type', 'disks', 'path', 'mime_type', 'md5', 'title', 'folder', 'size', 'width', 'height');
        return $this->FileModel->add($params);
    }


    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    public function createRandomCode($length = 6)
    {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
        $len = strlen($str);
        $randStr = '';
        for ($i = 0; $i < $length; $i++) {
            $randStr .= $str[mt_rand(0, $len - 1)];
        }
        return $randStr . time();
    }
}
