<?php


namespace App\Http\Controllers\System;


use App\Http\Controllers\Controller;
use App\Models\Common\SystemConfig;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{

    /**
     * 获取系统参数
     * @param SystemConfig $systemConfig
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemConfig(SystemConfig $systemConfig){
        $config = $systemConfig->getConfig();
        if(!$config){
            return $this->error('系统参数获取失败');
        }
        return $this->success($config);
    }


    /**
     * 设置系统参数
     * @param Request $request
     * @param SystemConfig $systemConfig
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSystemConfig(Request $request, SystemConfig $systemConfig){
        $params = $request->only([
             'system_name',
             'system_url',
             'system_logo',
             'system_version',
             'system_icp',
             'system_copyright',
             'system_watermark',
             'technical_support',
             'system_remark'
         ]);
        $params['system_logo'] = str_replace(config('app.url'), '', $params['system_logo']);
        $params['system_watermark'] = str_replace(config('app.url'), '', $params['system_watermark']);
         $result = $systemConfig->setConfig($params);
         if(!$result){
             return $this->error('系统参数设置失败');
         }
         return $this->success('系统参数设置成功');
    }

    /**
     * 系统配置相关图片上传
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request){
        $file = $request->file('file');
        //处理图片
        if ($file) {
            $disk_path = $file->store('', 'system');
            //去除根节点
            $path = str_replace(public_path(), '', config("filesystems.disks.system.root")) . '/' . $disk_path;
            return $this->success([
                'path' => $path,
                'full_path' => env("app.url") . $path
            ], 200, '上传成功');
        }
        return $this->error('上传失败');
    }

    // 水印图片
    public function getSystemWatermarkAttribute($val)
    {
        return config('app.url').'/'.$val;
    }

    // logo
    public function getSystemLogoAttribute($val)
    {
        return config('app.url').'/'.$val;
    }
}
