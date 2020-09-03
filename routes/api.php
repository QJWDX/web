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

Route::group(['prefix' => 'menus', 'namespace' => 'Admin'], function (){
    Route::get('getVueRoute', 'MenusController@getVueRoute');
    Route::get('getMenuTree', 'MenusController@getMenuTree');
    Route::get('getRoleMenus', 'MenusController@getRoleMenus');
    Route::post('setRoleMenus', 'MenusController@setRoleMenus');
});

Route::group(['prefix' => 'role', 'namespace' => 'Admin'], function (){
    Route::get('getRoleList', 'RoleController@getRoleList');
    Route::post('addRole', 'RoleController@addRole');
    Route::get('getRoleInfo/{id}', 'RoleController@getRoleInfo');
    Route::put('modRole/{id}', 'RoleController@modRole');
    Route::delete('delRole', 'RoleController@delRole');
    Route::get('getRoleTree', 'RoleController@getRoleTree');
});

Route::group(['prefix' => 'notifications', 'namespace' => 'Notifications'], function (){
    Route::get('createNotifications', 'NotificationsController@createNotifications');
    Route::get('getNotifications', 'NotificationsController@getNotifications');
    Route::get('makeRead', 'NotificationsController@makeRead');
    Route::delete('delNotifications', 'NotificationsController@delNotifications');
    Route::get('getUnreadNumber', 'NotificationsController@getUnreadNumber');
});

Route::group(['namespace' => 'Admin'], function (){
    Route::resource('user', 'UserController')->only(['index', 'store', 'show', 'update', 'destroy']);
});

Route::group(['prefix' => 'user', 'namespace' => 'Admin'], function (){
    Route::get('getUserRole/{id}', 'UserController@getUserRole');
    Route::post('setUserRole/{id}', 'UserController@setUserRole');
});


