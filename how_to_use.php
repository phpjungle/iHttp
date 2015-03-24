<?php
// 接口文档：
// 1.实例化 
$iH = new iHttp();

// 2.使用代理[如果使用的话]
$iH->enableProxy = true; // 默认为false
$iH->setProxy('127.0.0.1', 7777);

// 3.设置Cookie,UserAgent,Origin,RequestHeader
$iH->cookie = 'a=1; b=2'; // 方式1：字符串类型，如果有多个请用'; '分开
$iH->ar_cookie = array(); // 方式2:数组类型，键值对形式
$iH->origin = ''; // string
$iH->userAgent = ''; // string
$iH->requestHeader = array(); // array=>自定义Header内容，包含了上面几行定义的几个属性，键值对形式

// Demo参数
{	
	$num = 10; // 测试次数
	$ar_null = array_fill(0,$num,null);
	$url_get = 'http://www.baidu.com/s?wd=PHPJungle'; // 模拟百度Get搜索
	$tpl_get = 'http://www.baidu.com/s?wd=%s';
	$urls_get = array_map(function($i){global $tpl_get;return sprintf($tpl_get,rand(0, 100));}, $ar_null);
	
	$url_post = 'https://passport.weibo.cn/sso/login'; // 模拟sinaPOST登陆
	$post_data = array('username'=>'shaxitao@sina.cn','password'=>'2vfhfuewew');
	$urls_post = array_fill(0,$num,$url_post);
	$urls_post_data = array_fill(0,$num,$post_data);
}
// Demo 开始
// 4.1 http_get
$iH->get($url_get); // return string

// 4.2 multi-http_get
$iH->gets($urls_get); //默认异步方式请求，return array
$iH->gets($urls_get,false); //同步方式请求，return array

// 5.1 http_post
{
	// 初始化工作[登陆sina微博必要的一些header信息]
	$iH->reffer = 'https://passport.weibo.cn/signin/login?entry=mweibo&res=wel&wm=3349&r=http%3A%2F%2Fm.weibo.cn%2F';
	$iH->origin = 'https://passport.weibo.cn'; // 或者下面的2选1
	$iH->requestHeader = array('Origin'=>'https://passport.weibo.cn');
}
$back = $iH->post($url_post,$post_data); // return string
echo $iH->getLastError(); // 打印错误信息

// 5.2 multi-http_post
$back = $iH->posts($urls_post,$urls_post_data); // 异步方式：return array
$back = $iH->posts($urls_post,$urls_post_data,false); // 同步方式：return array
