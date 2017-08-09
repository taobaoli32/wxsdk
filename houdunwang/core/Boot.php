<?php
/**
 * Created by PhpStorm.
 * User: mazhenyu
 * Date: 07/08/2017
 * Time: 16:05
 */
namespace houdunwang\core;
class Boot {
    /**
     * 执行框架
     */
    public static function run(){
        self::init();
        self::appRun();
    }

    private static function appRun(){
        $s = isset($_GET['s']) ? strtolower($_GET['s']) : 'home/entry/index';
        $info = explode('/',$s);

        //定义组合模板的常量
        define('APP',$info[0]);
        define('CONTROLLER',$info[1]);
        define('ACTION',$info[2]);


//        错误写法
//        错误原因 ：
//        $className = "/app/{$info[0]}/controller/" . ucfirst($info[1]);
        $className = "\app\\{$info[0]}\controller\\" . ucfirst($info[1]);
        echo call_user_func_array([new $className,$info[2]],[]);
    }

    /**
     * 初始化框架
     */
    private static function init(){
        session_id() || session_start();
        date_default_timezone_set('PRC');
        define('IS_POST',$_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
    }
}