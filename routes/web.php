<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/view/index.html');
});

Route::get('/mqtt', function () {
    return view('mqtt');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


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
