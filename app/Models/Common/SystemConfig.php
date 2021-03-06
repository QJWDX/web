<?php


namespace App\Models\Common;
use App\Models\BaseModel;

class SystemConfig extends BaseModel
{
    protected $table = 'system_config';
    protected $fillable = [
        'system_name',
        'system_url',
        'system_logo',
        'system_version',
        'system_icp',
        'system_copyright',
        'system_watermark',
        'technical_support',
        'system_remark'
    ];


    public function getConfig(){
        $result = $this->newQuery()->first();
        if(!$result){
            return [];
        }
        return $result->toArray();
    }


    public function setConfig($params){
        $config = $this->newQuery()->first();
        if(!$config) return false;
        return $this->newQuery()->where('id', $config['id'])->update($params);
    }

    // 水印图片
    public function getSystemWatermarkAttribute($val)
    {
        return $val;
    }

    // logo
    public function getSystemLogoAttribute($val)
    {
        return $val;
    }

}
