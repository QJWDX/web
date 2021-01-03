<?php


namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use App\Models\Common\Articles;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function index(Request $request, Articles $articles){
        $title = $request->get('title', false);
        $list = $articles->getList(compact('title'));
        return $this->success($list);
    }

    public function show(){

    }

    public function store(){

    }

    public function update(){

    }

    public function destroy(){

    }
}
