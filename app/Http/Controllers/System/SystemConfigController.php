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
         $result = $systemConfig->setConfig($params);
         if(!$result){
             return $this->error('系统参数设置失败');
         }
         return $this->success('系统参数设置成功');
    }
}
