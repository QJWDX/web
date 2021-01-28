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
    Route::post('systemConfig/upload', 'SystemConfigController@upload');
});

Route::group(['prefix' => 'log', 'namespace' => 'Log'], function (){
    // 操作日志
    Route::apiResource('operationLog', 'OperationLogController')->only(['index','show','destroy']);
    // 批量删除操作日志
    Route::delete('delOperationLog', 'OperationLogController@delLoginLog');
});

// 消息通知
Route::group(['prefix' => 'Notification', 'namespace' => 'Notifications'], function (){
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
    Route::delete('delNotifications', 'NotificationsController@delNotifications');
    Route::get('getNotificationCount', 'NotificationsController@getNotificationCount');
    Route::post('sendNotification', 'NotificationsController@sendNotification');
    Route::get('notificationType', 'NotificationsController@notificationType');
});

// 文件管理
Route::group(['prefix' => '/File', 'namespace' => 'Files'], function (){
    Route::resource('files', 'FilesController')->only(['index', 'show']);
    Route::delete('delFiles', 'FilesController@del');
    Route::get('typeSelector', 'FilesController@typeSelector');
    Route::get('folderSelector', 'FilesController@folderSelector');
    Route::post('upload', 'FilesController@upload');
    Route::post('download', 'FilesController@download');
    Route::get("getShareLink", "FileShareController@getShareLink");
    Route::put("refreshShareLink", "FileShareController@refreshShareLink");
});

// 数据分析
Route::group(['prefix' => '/Statistics', 'namespace' => 'DataStatistics'], function (){
    //访问分析
    Route::get('/visitData','VisitStatisticsController@visitData');
    //地域分析(总数)
    Route::get('/districtTotalData','VisitDistrictController@districtTotalData');
    //地域分析（国家）
    Route::get('/countryListData','VisitDistrictController@countryListData');
    //地域分析（省份）
    Route::get('/provinceListData','VisitDistrictController@provinceListData');
});


