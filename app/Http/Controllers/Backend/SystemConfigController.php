<?php


namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use App\Models\Common\SystemConfig;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{
    /**
     * 获取网站设置
     * @param SystemConfig $systemConfig
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemConfig(SystemConfig $systemConfig){
        $config = $systemConfig->newQuery()->pluck('value', 'key')->toArray();
        return $this->success($config);
    }


    /**
     * 编辑网站配置
     * @param Request $request
     * @param SystemConfig $systemConfig
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSystemConfig(Request $request, SystemConfig $systemConfig){
         $configs = $request->all();
         foreach ($configs as $key => $value){
             $systemConfig->newQuery()->where('key', $key)->update(['value' => $value]);
         }
         return $this->success('设置成功');
    }
}
