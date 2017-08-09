<?php
namespace app\home\controller;
use houdunwang\view\View;

class Entry {
    public function index(){
        $content = isset($_GET['content']) ? $_GET['content'] : '北京天气';
        $result = (new Wechat())->getTuling($content);

        return View::make()->with(compact('result','content'));
    }
}