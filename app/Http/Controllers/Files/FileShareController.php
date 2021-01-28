<?php


namespace App\Http\Controllers\Files;


use App\Handlers\FileShareHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShareFileRequest;
use App\Models\Files\ShareFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FileShareController extends Controller
{

    /**
     * @param ShareFileRequest $request
     * @param ShareFile $shareFile
     * @param FileShareHandler $fileShareHandler
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getShareLink(ShareFileRequest $request, ShareFile $shareFile, FileShareHandler $fileShareHandler)
    {
        $resourceType = $request->input('resource_type');

        $resourceId = $request->input('resource_id');

        $shareFile = $shareFile->newQuery()->where([
            ['resourceable_type', $resourceType],
            ['resourceable_id', $resourceId],
        ])->first();
        // 未分享过的资源则首次生成
        if (empty($shareFile)) {
            $shareFile = $fileShareHandler->generateShareCodeAndFiles($resourceType, $resourceId);
        }
        $shareFile = $shareFile->toArray();
        // 格式化返回文件列表
        $files = [];
        foreach ($shareFile['share_content']['files'] as $file) {
            $files[] = $file['name'];
        }

        return $this->success([
            'url' => config('app.file_host') . 'files/' . $shareFile['share_code'],
            'files' => $files,
        ]);
    }

    public function refreshShareLink(ShareFileRequest $request, ShareFile $shareFile, FileShareHandler $fileShareHandler)
    {
        $resourceType = $request->input('resource_type');
        $resourceId = $request->input('resource_id');
        $shareFile = $shareFile->newQuery()->where([
            ['resourceable_type', $resourceType],
            ['resourceable_id', $resourceId],
        ])->first();
        $oldShareCode = $shareFile->share_code;
        $oldKey = FileShareHandler::SHARE_CACHE_PREFIX . $oldShareCode;
        $newShareCode = $fileShareHandler->generateShareCode();
        $newKey = FileShareHandler::SHARE_CACHE_PREFIX . $newShareCode;
        if (Cache::has($oldKey)) {
            // 如果缓存中的分享码还未过期，则用新分享码替换掉它
            Cache::put($newKey, Cache::pull($oldKey), now()->addDays(7));
        } else {
            // 如果缓存中的分享码已经不存在，则直接新增一条缓存记录
            $shareContent = $shareFile->share_content;
            Cache::put($newKey, $shareContent, now()->addDays(7));
        }
        // 更新最新的分享码到数据库
        $shareFile->share_code = $newShareCode;
        $shareFile->save();
        return $this->success([
            'url' => config('app.file_host') . 'files/' .$newShareCode,
        ]);
    }



    public function shareFilesIndex(Request $request)
    {
        $link = $request->route('share_code');
        $key = FileShareHandler::SHARE_CACHE_PREFIX . $link;

        if (! Cache::has($key)) {
            abort(403, '文件分享链接无效!');
        }

        $shareContent = Cache::pull($key);

        return view('file_share.index', compact('shareContent'));

    }
}
