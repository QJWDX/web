<?php


namespace App\Models\Base;


use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;

class SystemConfig extends BaseModel
{
    protected $table = 'system_config';
    protected $guarded = [];
}
