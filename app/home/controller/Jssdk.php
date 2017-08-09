<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/9
 * Time: 9:28
 */

namespace app\home\controller;
use houdunwang\view\View;
use houdunwang\wechat\Wechat;
use Curl\Curl;
class Jssdk{
    public function index(){
//        时间戳
        $time = time();
//        随机字符串32
        $nonceStr = md5(microtime(true));
//        p($nonceStr);
//        获取 信息
//        p($_SERVER);exit;

//        组合路径
//           [HTTP_HOST] => www.lovelanhua.com
//           [REQUEST_URI] => /?s=home/jssdk/index
        $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// 生成签名之前必须先了解一下jsapi_ticket，jsapi_ticket是公众号用于调用微信js接口的临时票据
        //jsapi_ticket
        $jsapiTicket = $this->getTicket();
//        p($jsapiTicket);exit;
        //算signature
        $asd = mestamp;
        $str = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$time}&url={$url}";

        //        $str = "jsapi_ticket=" . $jsapiTicket . '&noncestr=' . $nonceStr . '&timestamp=' . $time . '&url=' . $url;
//        p($str);exit;

        //sha1加密
        $signature = sha1($str);

        return View::make()->with(compact('time','signature','nonceStr'));
    }

    /**
     * 获取jsApiTicket
     * @return mixed
     * 固定写法
     */
    public function getTicket(){
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.Wechat::getAccessToken().'&type=jsapi';
        $data = (new Curl())->get($url);
        //p($data);exit;
        $data = json_decode($data->response,true);
        return $data['ticket'];
    }
}