<?php
namespace houdunwang\wechat;
class Wechat {
    public static function __callStatic( $name, $arguments ) {
        return call_user_func_array([new Base(),$name],$arguments);
    }
}