<?php


namespace App\Http\Controllers\Example;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Overtrue\Pinyin\Pinyin;

class PyController extends Controller
{
    public function index(Request $request){
        $zh = $request->get('zh');
        $Pinyin = new Pinyin();
        $abbr = '';
        foreach ($Pinyin->convert($zh) as $value){
            $abbr.= $value[0];
        }
        $abbr = strtoupper($abbr);
    }
}
