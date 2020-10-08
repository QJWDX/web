<?php


namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use App\Models\Common\Articles;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    private $M;
    public function __construct(Articles $articles)
    {
        $this->M = $articles;
    }

    public function index(Request $request){
        $title = $request->get('title', false);
        $list = $this->M->getList(compact('title'));
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
