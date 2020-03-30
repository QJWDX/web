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



Route::group(['prefix' => 'auth', 'namespace' => 'Api'], function () {
    Route::post('login', 'LoginController@login');
    Route::post('register', 'LoginController@register');
    Route::get('logout', 'AuthController@logout');
    Route::get('user', 'AuthController@getAuthUser');
});


Route::group(['prefix' => 'example', 'namespace' => 'Example'], function (){
    Route::get('/pinyin', 'PyController@index');
});
