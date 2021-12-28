<?php
namespace app\controller;

use support\Env;
use support\Request;

class Index
{
    public function index(Request $request)
    {
        var_dump(Env::get());
        return response('hello webman');
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function json(Request $request)
    {
        return json(['code' => 0, 'msg' => 'ok']);
    }

}
