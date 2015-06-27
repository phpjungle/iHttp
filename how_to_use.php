<?php
// 接口文档：(API Document)
// 1.实例化 (1.new objct)
$iH = new iHttp();

// 2.使用代理[如果使用的话](set proxy if necessary)
$iH->enableProxy = true; // 默认为false (Default value is false)
$iH->setProxy('127.0.0.1', 7777);

// 3.设置Cookie,UserAgent,Origin,RequestHeader (set Cookie,UserAgent,Origin,other Request header infos)
$iH->cookie = 'a=1; b=2'; // 方式1：字符串类型，如果有多个请用'; '分开(method-1:you can build cookie with string)
$iH->ar_cookie = array(); // 方式2:数组类型，键值对形式(method-2:build cookie with array)
$iH->origin = ''; // string
$iH->userAgent = ''; // string
$iH->requestHeader = array(); // array=>自定义Header内容，包含了上面几行定义的几个属性，键值对形式(you customized headers,format is array(key=>value) )

// Demo params
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
// Demo start
// 4.1 http_get
$iH->get($url_get); // !important: return string or false

// 4.2 multi-http_get
$iH->gets($urls_get); //默认异步方式请求，return array (default send http request with asyn)
$iH->gets($urls_get,false); //同步方式请求，return array(if you want to send http request with sync ,set the second param to false)

// 5.1 http_post
{
	// init OPs
	$iH->reffer = 'https://passport.weibo.cn/signin/login?entry=mweibo&res=wel&wm=3349&r=http%3A%2F%2Fm.weibo.cn%2F';
	$iH->origin = 'https://passport.weibo.cn'; // 或者下面的2选1
	$iH->requestHeader = array('Origin'=>'https://passport.weibo.cn');
}
$back = $iH->post($url_post,$post_data); // return string or false
echo $iH->getLastError(); // 打印错误信息(print last error msg)

// 5.2 multi-http_post
$back = $iH->posts($urls_post,$urls_post_data); // 异步方式：return array (asyn method)
$back = $iH->posts($urls_post,$urls_post_data,false); // 同步方式：return array (sync method)

// 6. 笔者在iHttp中添加了静态方法，方便大家查看执行相同任务时同步和异步的性能差别，调用方法：(test the time your code cost with calc_begin and calc_end )
$iH::calc_begin();
//@todo add your codes :)
$iH::calc_end();

// 7. 设置连接超时时间（set_connect_timeout）
$iH->set_connect_timeout(10); // set_connect_timeout to 10secs

// 8. 获取响应头信息(get response header informations)
$iH->res_cookie; #　Cookie-string-format
$iH->res_ar_cookie;# Cookie-array-format

$iH->res_ar_header;# Header-string-format
$iH->res_headerstr;# Header-array-format


