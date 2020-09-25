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

// auth 登录认证
Route::group(['prefix' => 'auth', 'namespace' => 'Backend\Auth'], function () {
    // 登录
    Route::post('login', 'LoginController@login');
    // 注册
    Route::post('register', 'LoginController@register');
    // 退出登录
    Route::get('logout', 'AuthController@logout');
    // 获取认证用户信息
    Route::get('user', 'AuthController@getAuthUser');
    // 获取图形验证码
    Route::post('getCaptcha', 'CaptchaController@getCaptcha');
    // 获取rsa加密key
    Route::post('getRsaPublicKey', 'LoginController@getRsaPublicKey');
});

// 菜单管理
Route::group(['prefix' => 'menus', 'namespace' => 'Backend'], function (){
    Route::get('list', 'MenusController@index');
    Route::get('show/{id}', 'MenusController@show');
    Route::post('store', 'MenusController@store');
    Route::put('update/{id}', 'MenusController@update');
    Route::delete('destroy/{id}', 'MenusController@destroy');
    Route::get('getVueRoute', 'MenusController@getVueRoute');
    Route::get('getVueRoute', 'MenusController@getVueRoute');
    Route::get('getMenuTree', 'MenusController@getMenuTree');
    Route::get('getRoleMenus', 'MenusController@getRoleMenus');
    Route::post('setRoleMenus', 'MenusController@setRoleMenus');
    Route::get('menuSelect', 'MenusController@menuSelect');
});


// 角色管理
Route::group(['prefix' => 'role', 'namespace' => 'Backend'], function (){
    Route::get('list', 'RoleController@index');
    Route::get('show/{id}', 'RoleController@show');
    Route::post('store', 'RoleController@store');
    Route::put('update/{id}', 'RoleController@update');
    Route::delete('delRole', 'RoleController@delRole');
    Route::get('getRoleTree', 'RoleController@getRoleTree');
});

// 用户管理
Route::group(['prefix' => 'user', 'namespace' => 'Backend'], function (){
    Route::get('list', 'UserController@index');
    Route::get('show/{id}', 'UserController@show');
    Route::post('store', 'UserController@store');
    Route::put('update/{id}', 'UserController@update');
    Route::delete('destroy/{id}', 'UserController@destroy');
    Route::get('getUserRole/{id}', 'UserController@getUserRole');
    Route::post('setUserRole/{id}', 'UserController@setUserRole');
    Route::post('uploadAvatar/{id}', 'UserController@uploadAvatar');
    Route::post('modPassword/{id}', 'UserController@modPassword');
});

// 系统参数配置
Route::group(['prefix' => 'system', 'namespace' => 'Backend'], function (){
    Route::get('getSystemConfig', 'SystemConfigController@getSystemConfig');
    Route::put('setSystemConfig', 'SystemConfigController@setSystemConfig');
});

// 登录日志
Route::group(['prefix' => 'loginLog', 'namespace' => 'Backend'], function (){
    Route::get('list', 'LoginLogController@index');
    Route::get('show/{id}', 'LoginLogController@show');
    Route::delete('delLoginLog', 'LoginLogController@delLoginLog');
});

// 消息通知
Route::group(['prefix' => 'notifications', 'namespace' => 'Backend'], function (){
    Route::get('createNotifications', 'NotificationsController@createNotifications');
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
    Route::delete('delNotifications', 'NotificationsController@delNotifications');
    Route::get('getNotificationCountStatistics', 'NotificationsController@getNotificationCountStatistics');
});

