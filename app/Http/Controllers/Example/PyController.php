<?php


namespace App\Http\Controllers\Example;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Overtrue\Pinyin\Pinyin;

class PyController extends Controller
{
    // 中文转拼音示例
    public function index(Request $request){
        $zh = $request->get('zh', false);
        if(!$zh){
            return $this->error(500, '参数有误');
        }
        $Pinyin = new Pinyin();
        $py = $Pinyin->convert($zh);
        return $this->success($py);
    }
}
