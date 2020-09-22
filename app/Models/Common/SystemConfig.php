<?php


namespace App\Models\Common;
use App\Models\BaseModel;

class SystemConfig extends BaseModel
{
    protected $table = 'system_config';
    protected $guarded = [];
    public $timestamps = false;
}
