<?php


namespace App\Handlers;
use App\Models\Files\ShareFile;
use App\Models\Files\Files;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class FileShareHandler
{
    const SHARE_CACHE_PREFIX = 'share:';


    /**
     * 生成分享码和对应文件并存入DB和Redis
     * @param String $resourceType
     * @param Int $resourceId
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function generateShareCodeAndFiles(String $resourceType, Int $resourceId)
    {
        // 生成文件分享码
        $shareCode = $this->generateShareCode();

        // 为分享码添加对应标题和文件
        $files = $this->getFilesNameAndPath($resourceType, $resourceId);
        $shareContent = [
            'title' => ShareFile::$resourceTypesTitleMap[$resourceType],
            'files' => $files,
        ];

        // 将分享码和文件存入redis
        $key = self::SHARE_CACHE_PREFIX . $shareCode;
        Cache::put($key, $shareContent, now()->addDays(7));

        // 将分享码和文件存入DB
        return $this->saveShareContentToDB($resourceType, $resourceId, $shareCode, $shareContent);
    }


    /**
     * 获取要分享的文件名和路径
     * @param $resourceType
     * @param Int $resourceId
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getFilesNameAndPath(String $resourceType, Int $resourceId)
    {
        $files = [];
        switch ($resourceType) {
            case 'file' :
                $file = app('App\Models\Files\Files')->row($resourceId);
                $files[] = [
                    'name' => $file['title'],
                    'path' => config('app.file_host') . $file['disks'] . "/" . $file['path']
                ];
                break;
        }
        return $files;
    }


    /**
     * 将分享内容保存到数据库
     * @param String $resourceType 资源类型
     * @param Int $resourceId 资源ID
     * @param String $shareCode 分享码
     * @param array $shareContent 分享内容
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function saveShareContentToDB(String $resourceType, Int $resourceId, String $shareCode, Array $shareContent)
    {
        $shareFile = ShareFile::query()->make([
            'share_code' => $shareCode,
            'share_content' => $shareContent,
        ]);

        switch ($resourceType) {
            case 'file':
                $shareFile->resourceable()->associate(Files::query()->find($resourceId));
                break;
            default:
                throw new \Exception('该资源类型暂不支持分享!');
        }
        $shareFile->save();
        return $shareFile;
    }

    // 生成文件分享码
    public function generateShareCode()
    {
        do {
            $shareCode = Uuid::uuid4()->getHex();
            $key = self::SHARE_CACHE_PREFIX . $shareCode;
        } while (Cache::has($key));

        return $shareCode;
    }


    /**
     * 统一文件数组的格式
     * @param array $files
     * @return array
     */
    public function handleAuthenticationFilesFormat(Array &$files)
    {
        foreach ($files as &$file) {
            $file['name'] = $file['file_name'];
            $file['path'] = $file['file_path'];
            unset($file['file_name']);
            unset($file['file_path']);
        }
        return $files;
    }
}
