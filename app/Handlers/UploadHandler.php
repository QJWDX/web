<?php


namespace App\Handlers;


use App\Models\Common\Files;
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
     * @param $file
     * @param $type
     * @param $folder
     * @return bool|string
     */
    public function storeFile($file, $type, $folder){

        $folder_name = $type."s/$folder/" . date("Ym", time()) . '/'.date("d", time());

        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalName()) ? : 'png';

        // 检查文件后缀是否是规则允许后缀
        if ( ! in_array($extension, config('filesystems.uploader.'.$type.'.allowed_ext')) ) {
            return false;
        }

        // 原始文件名
        $title = $file->getClientOriginalName();

        // 获取文件的 Mime
        $mimeType = $file->getClientMimeType();

        // 获取文件大小
        $size = $file->getSize();

        // 获取文件 MD5 值
        $md5 = md5_file($file->getPathname());

        // 检查文件是否已上传过
        if($fileModel = $this->checkFile($md5, $type, $folder)){
            return config('EXPORT_URL') . $fileModel->path;
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
        // 将图片移动到我们的目标存储路径中 或 云存储中
        if( ! ( $path = $file->store($folder_name)) ) {
            return false;
        }
        $uid = $this->FileModel->uuid();
        $result = $this->saveFile($uid, $type, $path, $mimeType, $md5, $title, $folder, $size, $width, $height, $editor = 0, $status = 1, $disks = null);
        if($result){
            return config('EXPORT_URL') . $result->path;
        }else{
            Storage::delete($path);
        }
    }


    /**
     * 检查文件是否已存在
     * @param $md5
     * @param $type
     * @param $folder
     * @return bool
     */
    public function checkFile($md5, $type, $folder){
        return $this->FileModel->fileIsExists([
            ['md5', '=', $md5],
            ['type','=',$type],
            ['folder', '=', $folder]
        ]);
    }


    /**
     * 保存到数据库
     * @param $uid
     * @param $type
     * @param $path
     * @param $mimeType
     * @param $md5
     * @param $title
     * @param $folder
     * @param $size
     * @param $width
     * @param $height
     * @param int $editor
     * @param int $status
     * @param null $disks
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function saveFile($uid, $type, $path, $mimeType, $md5, $title, $folder, $size, $width, $height, $editor = 0, $status = 1, $disks = null){
        $params = [
            'uid' => $uid,
            'type' => $type,
            'disks' => $disks ?: config('filesystems.default'),
            'path' => $path,
            'mime_type' => $mimeType,
            'md5' => $md5,
            'title' => $title,
            'folder' => $folder,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'editor' => (string)$editor,
            'status' => (string)$status,
        ];
        return $this->FileModel->add($params);
    }
}
