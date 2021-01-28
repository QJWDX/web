<?php

namespace App\Models\Files;

use Illuminate\Database\Eloquent\Model;

class ShareFile extends Model
{
    protected $table = 'share_files';

    public static $resourceTypesTitleMap = [
        'file' => '文件管理资料',
    ];

    protected $guarded = [];

    protected $casts = [
      'share_content' => 'json',
    ];

    public function resourceable()
    {
        return $this->morphTo();
    }
}
