<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::options('/{all}', function (\Illuminate\Http\Request $request) {
    return response('Ok');
})->where(['all' => '([a-zA-Z0-9-]|/)+'])->middleware("cross");


// 系统参数配置
Route::group(['prefix' => 'system', 'namespace' => 'System'], function (){
    Route::get('getSystemConfig', 'SystemConfigController@getSystemConfig');
    Route::put('setSystemConfig', 'SystemConfigController@setSystemConfig');
});



// 消息通知
Route::group(['prefix' => 'notifications', 'namespace' => 'Notification'], function (){
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
    Route::delete('delNotifications', 'NotificationsController@delNotifications');
    Route::get('getNotificationCountStatistics', 'NotificationsController@getNotificationCountStatistics');
});

// 文件管理
Route::group(['prefix' => 'files', 'namespace' => 'File'], function (){
    Route::get('list', 'FilesController@index');
    Route::get('show/{id}', 'FilesController@show');
    Route::post('download/{id}', 'FilesController@download');
    Route::get('typeSelect', 'FilesController@typeSelect');
    Route::get('folderSelect', 'FilesController@folderSelect');
    Route::post('upload', 'FilesController@upload');
});

// 文章管理
Route::group(['prefix' => 'articles', 'namespace' => 'Backend'], function (){
    Route::get('list', 'ArticlesController@index');
    Route::get('show/{id}', 'ArticlesController@show');
});

