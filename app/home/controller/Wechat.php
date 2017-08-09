<?php
namespace app\home\controller;

use houdunwang\wechat\Wechat as Wx;
use Curl\Curl;

class Wechat {
    public function handle() {

        //微信验证服务器
        Wx::validate();
        //如果是订阅
        if ( Wx::subscribe() ) {
            //回复订阅成功
            Wx::responseMsg( '订阅成功' );
        }

        $data = [
            [
                'title'       => '快速调试Vue的利器-devtools',
                'description' => '操作VUE的插件，非常好用',
                'picUrl'      => 'http://wx.nickblog.cn/resource/images/vue.png',
                'url'         => 'http://www.nickblog.cn/article/2306.html'
            ],
            [
                'title'       => 'mac安装composer',
                'description' => '苹果系统如何安装composer',
                'picUrl'      => 'http://wx.nickblog.cn/resource/images/xiaotu.jpeg',
                'url'         => 'http://www.nickblog.cn/article/2277.html'
            ],
            [
                'title'       => 'phpstorm支持es6的语法',
                'description' => 'es6的语法，增加了很多新特性',
                'picUrl'      => 'http://wx.nickblog.cn/resource/images/xiaotu2.jpg',
                'url'         => 'http://www.nickblog.cn/article/2232.html'
            ]
        ];

        //关键词回复
        switch ( Wx::getContent() ) {
            case "1":
                Wx::responseMsg( '1是美女，你回复2试试' );
            case "2":
                Wx::responseMsg( '2是帅哥，你回复3试试' );
            case "3":
                Wx::responseMsg( '3是罗玉凤' );
            case "电话":
                Wx::responseMsg( '18612694053' );
            case "马老师":
                Wx::responseMsg( '18612694053' );
            case "技术文章":
                wx::responseNews( $data );
        }
        //自定义菜单的点击之后的key回复
        switch (Wx::getKey()){
            case 'msg':
                wx::responseMsg('https://github.com/taobaoli32/work89.git');
            case 'frame':
                wx::responseMsg('https://github.com/taobaoli32/hd.git');
        }

        //默认回复
        Wx::responseMsg( $this->getTuling( Wx::getContent() ) );


    }

    /**
     * 获取图灵接口的数据
     *
     * @param string $content
     *
     * @return mixed
     */
    public function getTuling( $content = '北京天气' ) {
        $url = "http://www.tuling123.com/openapi/api?key=8fd4b055a27744579fbb1e4e594d7c61&info=" . $content;
        //curl方式请求，不要用file_get_contents，比较low
        $curl = new Curl();
        $data = $curl->get( $url );
        $arr  = json_decode( $data->response, true );

//        echo $arr['text'];
        return $arr['text'];
    }


    /**
     * 获取access_token
     */
    public function handleAccessToken() {
        $accessToken = Wx::getAccessToken();
        echo $accessToken;
    }

    /**
     * 获取微信服务器的IP，需要access_token票据
     */
    public function getIp() {
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=";
        $url .= Wx::getAccessToken();
        //调取接口
        $curl = new Curl();
        $data = $curl->get( $url );
        $data = json_decode( $data->response );
        //输出ip地址，微信服务器很多ip地址
        foreach ( $data->ip_list as $ip ) {
            echo $ip . '<br/>';
        }
    }

    /**
     * 创建菜单
     */
    public function createMenu() {
        $data = [
            'button' => [
                [
                    "type" => "view",
                    "name" => "小米官网",
                    "url"  => "http://www.lovelanhua.com/xiaomi/index.html"
                ],

                [
                    "name"       => "项目",
                    "sub_button" => [
                        [
                            "type" => "view",
                            "name" => "留言板",
                            "url"  => "http://www.lovelanhua.com/liuyanban/public/index.php"
                        ],
                        [
                            "type" => "view",
                            "name" => "学生管理系统",
                            "url"  => "http://www.lovelanhua.com/xueshengguanli/public/index.php"
                        ],
                    ]
                ],
                [
                    "name"       => "后台项目",
                    "sub_button" => [
                        [
                            "type" => "view",
                            "name" => "微信hssdk",
                            "url"  => "http://www.lovelanhua.com/wxchat/?s=home/jssdk/index"
                        ],
                        [
                            "type" => "view",
                            "name" => "框架",
                            "url"  => "https://github.com/taobaoli32/hd.git"
                        ],

                        [
                            "type" => "view",
                            "name" => "小米",
                            "url"  => "http://xiaomi.lovelanhua.com"
                        ]
                    ]
                ]

            ]
        ];
        $res = Wx::createMenu($data);
        p($res);
    }

    /**
     * 获得菜单
     * 可以在chrom装一个jsonview插件，以后查看json比较方便
     */
    public function getMenu(){
        $data = Wx::getMenu();
        p($data);
    }

    public function delMenu(){
        $res = Wx::removeMenu();
        p($res);
    }
}