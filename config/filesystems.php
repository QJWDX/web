<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'xlsx' => [
            'driver' => 'local',
            'root' => public_path('export/xlsx'),
            'url' => env('APP_URL'). '/export/xlsx/'
        ],

        'upload' => [
            'driver' => 'local',
            'root' => public_path('upload'),
            'url' => env('APP_URL'). '/upload/'
        ]
    ],

    'uploader' => [

        'type' => ['image' => '图片', 'annex' => '附件', 'file' => '文件', 'audio' => '音频', 'video' => '视频'],

        'folder' => [
            'avatar'
        ],

        // 图片
        'image' => [
            'size_limit' => 5242880, // 单位：字节，默认：5MB
            'allowed_ext' => ["png", "jpg", "gif", 'jpeg', 'bmp'],
        ],

        // 附件
        'annex' => [
            'size_limit' => 204857600000, // 单位：字节，默认：5MB (5242880 B)  // 104857600
            'allowed_ext' => ['zip','rar','7z','gz'],
        ],

        // 文件
        'file' => [
            'size_limit' => 5242880, // 单位：字节，默认：5MB
            'allowed_ext' => ['pdf','doc','docx','xls','xlsx','ppt','pptx'],
        ],

        // 音频
        'audio' => [
            'size_limit' => 1048576*200, // 单位：字节，默认：5MB
            'allowed_ext' => ['mp3','wma', 'ogg', 'wav','amr','m4a'],
        ],

        // 视频
        'video' => [
            'size_limit' => 5242880, // 单位：字节，默认：5MB
            'allowed_ext' => ['mp4'],
        ],
    ]

];
