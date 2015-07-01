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

iHttp::calc_end();
