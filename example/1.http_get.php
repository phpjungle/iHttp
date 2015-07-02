<?php
/**
 * Introduction list:
 * 		1.how to send HTTP GET request;
 * 		2.get last error msg;
 * 		3.get response headers(array)
 * 		4.get response cookie(string or array)
 * 		5.get response body(string)
 * 
 * @author PHPJungle
 * @since 2015/07/01 周三(wed)
 */
include '../iHttp.php';
 
$url = 'https://www.google.com/';
// $url = 'https://www.baidu.com/';
// $url = 'http://www.phpjungle.com/';
iHttp::calc_begin();

$iH = new iHttp();

# response body
$html = $iH->get($url);# return:string or false when fails

# last error string
$errormsg = $iH->getLastError();

# response headers(array)
$res_ar_header = $iH->res_ar_header;

# response cookie(string)
$res_cookie = $iH->res_cookie;

# response cookie(array)
$res_ar_cookie = $iH->res_ar_cookie;


var_dump('response headers(array):',$res_ar_header);
echo '<hr>';
var_dump('response cookie(string):',$res_cookie,PHP_EOL,'<hr>');
echo '<hr>';
var_dump('response cookie(array):',$res_ar_cookie);

echo '<hr>';
echo 'response body:',$html,PHP_EOL,'<br/>';
echo '<hr>';
echo 'error msg:',$errormsg,PHP_EOL,'<br/>';

# Time costs:
iHttp::calc_end(few);

# Raw HTTP POST Request

// GET https://www.google.com/ HTTP/1.1
// User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36
// Host: www.google.com
// Accept: */*
// Accept-Encoding: deflate, gzip
// Referer:
// Cookie:

