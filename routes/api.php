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
    Route::get('getRoleInfo/{id}', 'RoleController@getRoleInfo');
    Route::put('modRole/{id}', 'RoleController@modRole');
    Route::delete('delRole', 'RoleController@delRole');
});

Route::group(['prefix' => 'notifications', 'namespace' => 'Notifications'], function (){
    Route::get('createNotifications', 'NotificationsController@createNotifications');
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
    Route::delete('delNotifications', 'NotificationsController@delNotifications');
    Route::get('getUnreadNumber', 'NotificationsController@getUnreadNumber');
});
