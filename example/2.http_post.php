<?php
/**
 * Introduction list:How to build HTTP POST Request
 * 		1.how to send HTTP POST request(post string);
 * 		2.how to send HTTP POST request(post array)
 * @author iPHPJungle
 * @since 2015/07/01 周三(wed)
 */

include '../iHttp.php';
$iH = new iHttp();

// $iH->enableProxy = true;
// $iH->setProxy('localhost', 7777);

$iH->ar_cookie = array('ckkdk1'=>111,'ck_3i3i'=>2,'ckejofwe'=>'==222=222');

$base_url = 'http://www.yourdomain.com/';# do NOT forget the end slash

# type-1:form
$formdata = array('username'=>'PHPJungle','password'=>'xxxx');

# type-2:form
$formdatastr = http_build_query($formdata);

$html = $iH->post($base_url,$formdata);
$html = $iH->post($base_url,$formdatastr);

echo ($html);


# Raw HTTP POST Request

// POST http://www.yourdomain.com/ HTTP/1.1
// User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36
// Host: www.yourdomain.com
// Accept: */*
// Accept-Encoding: deflate, gzip
// Referer:
// Connection: Keep-Alive
// Cookie: ckkdk1=111; ck_3i3i=2; ckejofwe===222=222
// Content-Length: 32
// Content-Type: application/x-www-form-urlencoded

// username=PHPJungle&password=xxxx
