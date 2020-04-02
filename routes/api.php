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


// jwt权限认证
Route::group(['prefix' => 'auth', 'namespace' => 'Api'], function () {
    // 登录
    Route::post('login', 'LoginController@login');
    // 注册
    Route::post('register', 'LoginController@register');
    // 退出登录
    Route::get('logout', 'AuthController@logout');
    // 获取认证用户信息
    Route::get('user', 'AuthController@getAuthUser');
});

// 示例接口
Route::group(['prefix' => 'example', 'namespace' => 'Example'], function (){
    // 中文转拼音
    Route::get('pinyin', 'PyController@index');
    // redis缓存示例
    Route::get('redis', 'ExampleController@redis');
    // 缓存示例
    Route::get('cache', 'ExampleController@cache');
});
