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

// jwt权限认证
Route::group(['prefix' => 'auth', 'namespace' => 'Authorize'], function () {
    // 登录
    Route::post('login', 'LoginController@login');
    // 注册
    Route::post('register', 'LoginController@register');
    // 退出登录
    Route::get('logout', 'AuthController@logout');
    // 获取认证用户信息
    Route::get('user', 'AuthController@getAuthUser');
});

Route::group(['prefix' => 'role', 'namespace' => 'Role'], function (){
    Route::get('getMenusAndRoute', 'RoleController@getMenusAndRoute');
    Route::get('getRoleList', 'RoleController@getRoleList');
    Route::post('addRole', 'RoleController@addRole');
    Route::get('getRoleInfo', 'RoleController@getRoleInfo');
    Route::put('modRole/{id}', 'RoleController@modRole');
    Route::delete('delRole', 'RoleController@delRole');
});

// 示例接口
Route::group(['prefix' => 'example', 'namespace' => 'Example'], function (){
    // 中文转拼音
    Route::get('pinyin', 'PyController@index');
    // redis缓存示例
    Route::get('redis', 'ExampleController@redis');
    // 缓存示例
    Route::get('cache', 'ExampleController@cache');
    // excel导出
    Route::get('excel', 'ExampleController@excel');
    // pdf导出
    Route::get('pdf', 'ExampleController@pdf');
    // 获取图形验证码
    Route::post('captcha', 'ExampleController@captcha');
    // 验证图形验证码
    Route::get('checkCaptcha', 'ExampleController@checkCaptcha');
    // 邮件发送
    Route::get('mail', 'ExampleController@mail');
    // baseTable
    Route::get('baseTable', 'ExampleController@baseTable');
    // 角色列表
    Route::get('getRoleList', 'ExampleController@getRoleList');
});

Route::group(['prefix' => 'notifications', 'namespace' => 'Notifications'], function (){
    Route::get('createNotifications', 'NotificationsController@createNotifications');
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
});
