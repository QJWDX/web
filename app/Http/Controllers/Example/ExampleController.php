<?php


namespace App\Http\Controllers\Example;


use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ExampleController extends Controller
{
    public function redis(){
        Redis::connection('default')->set('key', 'username');
        echo Redis::connection('default')->get('key');
    }

    public function cache(){
//        Cache::forget('user');
//        return Cache::store('redis')->tags('user')->get('user');
        return Cache::store('redis')->tags('user')->remember('user', Carbon::now()->addSecond(20), function (){
            return DB::table('users')->get();
        });
    }

}
