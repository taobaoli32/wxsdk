<?php
/**
 * Created by PhpStorm.
 * User: mazhenyu
 * Date: 07/08/2017
 * Time: 16:20
 */

namespace houdunwang\wechat;
use Curl\Curl;


class Base {

    private $wxObj;

    public function __construct() {
        $this->setWxObj();
    }

    /**
     * 处理微信对象
     */
    private function setWxObj() {
        if ( isset( $GLOBALS['HTTP_RAW_POST_DATA'] ) ) {
            //1.接收微信服务器推送过来的消息(xml格式,字符串类型)
            $wxXML = $GLOBALS['HTTP_RAW_POST_DATA'];
            file_put_contents('./xml.php',$wxXML);
            //2.处理消息类型，把xml格式变成一个对象
            $this->wxObj = simplexml_load_string( $wxXML );
        }

        //下面是测试代码，为了调错误
        /*
                $wxXML       = <<<str
        <xml>
        <ToUserName><![CDATA[111]]></ToUserName>
        <FromUserName><![CDATA[222]]></FromUserName>
        <CreateTime>123456789</CreateTime>
        <MsgType><![CDATA[event]]></MsgType>
        <Event><![CDATA[subscribe]]></Event>
        </xml>
        str;
                $this->wxObj = simplexml_load_string( $wxXML );
        */

    }

    /**
     * 微信验证
     */
    public function validate() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];
        $token     = c( 'wechat.token' );
        $tmpArr    = array( $token, $timestamp, $nonce );
        sort( $tmpArr, SORT_STRING );
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if ( $tmpStr == $signature && isset( $_GET['echostr'] ) ) {
            echo $_GET["echostr"];
            exit;
        }
    }

    /**
     * 是否是订阅
     * @return bool
     */
    public function subscribe() {
        if ( strtolower( $this->wxObj->MsgType ) == 'event' ) {
            if ( strtolower( $this->wxObj->Event ) == 'subscribe' ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获得用户发送的内容
     * @return string
     */
    public function getContent() {
        return strtolower( $this->wxObj->Content );
    }

    public function getKey(){
        return strtolower($this->wxObj->EventKey);
    }


    public function responseMsg( $text ) {
        //刚才是用户发给我们，现在是我们发给用户，所以反一下
        //我们变成发送者FromUserName，用户变为接收者ToUserName
        $FromUserName = $this->wxObj->ToUserName;
        $ToUserName   = $this->wxObj->FromUserName;
        $CreateTime   = time();
        $MsgType      = 'text';
        $Content      = $text;
        //组合要回复的模板
        $template = <<<str
				<xml>
				<ToUserName><![CDATA[{$ToUserName}]]></ToUserName>
				<FromUserName><![CDATA[{$FromUserName}]]></FromUserName>
				<CreateTime>{$CreateTime}</CreateTime>
				<MsgType><![CDATA[{$MsgType}]]></MsgType>
				<Content><![CDATA[{$Content}]]></Content>
				</xml>
str;
        echo $template;
        exit;
    }

    /**
     * 回复图文消息
     *
     * @param $data
     */
    public function responseNews( $data ) {
        $toUser   = $this->wxObj->FromUserName;
        $fromUser = $this->wxObj->ToUserName;
        $time     = time();
        //文章总数
        $total = count( $data );

        $str = <<<str
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>{$total}</ArticleCount>
<Articles>
str;
        //组合文章字符串
        foreach ( $data as $v ) {
            $str .= <<<str
<item>
<Title><![CDATA[{$v['title']}]]></Title> 
<Description><![CDATA[{$v['description']}]]></Description>
<PicUrl><![CDATA[{$v['picUrl']}]]></PicUrl>
<Url><![CDATA[{$v['url']}]]></Url>
</item>
str;
        }

        $str .= <<<str
</Articles>
</xml>
str;
        echo $str;
        exit;


    }

    /**
     * 获取通行票据
     */
    public function getAccessToken() {
        //请求地址
        $url = "https://api.weixin.qq.com/cgi-bin/token";
        //获取access_token填写client_credential
        $grant_type = 'client_credential';
        //第三方用户唯一凭证
        $appid = c( 'wechat.appid' );
        //第三方用户唯一凭证密钥，即appsecret
        $secret = c( 'wechat.appsecret' );
        //最终地址
        $url .= "?grant_type={$grant_type}&appid={$appid}&secret={$secret}";

        //保存accessToken的文件目录
        $path = './storage/data.php';
        //第一次返回为空数组
        $arrToken = include $path;
        if ( ! $arrToken || $arrToken['endtime'] <= time() ) {
            //请求
            $json = file_get_contents( $url );
            //把返回的json转为数组
            $arrToken = json_decode( $json, true );
            //计算过期时间
            $arrToken['endtime'] = time() + 7200;
            //写入到文件保存，为了不用重复的获取access_token，因为获取access_token是每天2000次
            file_put_contents( $path, "<?php return " . var_export( $arrToken, true ) . "?>" );
        }


//        p( $arrToken['access_token']);exit;
//        2VkiTpI1Hnb3snTvo5ooT086aj349rMv97aluAjTUACZh20XoSqgeyNOMKmnxf8r-Ixc-2evHVm-Mub1CVy4XuW-tNyPQaVYV087TfY16qNFrlzLyW-wgr7gey52lattDKEeAFAJQK
        return $arrToken['access_token'];


    }

    /**
     * 创建菜单
     * @param $data
     *
     * @return mixed
     */
    public function createMenu($data){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=". $this->getAccessToken();

        $json = json_encode($data,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//        p($json);exit;
        //使用curl
        $curl = new Curl();
        //执行post请求
        $data = $curl->post($url,$json);
//        p($data);exit;
//        p(json_decode($data->response,true));exit;
        return json_decode($data->response,true) ;
    }

    /**
     * 获得菜单
     * @return mixed
     */
    public function getMenu(){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->getAccessToken();
        $curl = new Curl();
        $data = $curl->get($url);
        return json_decode($data->response,true);
    }

    /**
     * 删除菜单
     * @return mixed
     */
    public function removeMenu(){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $this->getAccessToken();
        $data = (new Curl())->get($url);
        return json_decode($data->response,true);
    }
}
