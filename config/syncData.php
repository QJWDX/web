<?php
return [
    'table' => [
        'camera_official_account' => [
            'model' => \App\Models\Common\LoginLog::class,
            'action' => 'handleSyncData',
            'pk' => 'uid',
        ]
    ]
];
