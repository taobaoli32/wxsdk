<?php
function p($var){
    echo '<pre style="background:#ccc;padding: 5px;">';
    print_r($var);
    echo '</pre>';
}

//c('wechat.token')
function c($path){
    $info = explode('.',$path);
//    正确写法   服务器
//    $config = include '/system/config/' . $info[0] . '.php';


    $config = include './system/config/' . $info[0] . '.php';
    return isset($config[$info[1]]) ? $config[$info[1]] : NULL;
}