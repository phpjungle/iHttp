<?php
/**
 * Introduction list:How to Build http request header
 * 		1.how to add cookie,referer,origin for http request headers;
 * 		2.how to add other http request headers;
 * @author PHPJungle
 * @since 15.07.02 周四(Thursday)
 * @abstract Some times we need build http request with cookie,referer,Origin,etc,
 * 	or we will not get the target http response
 */

include '../iHttp.php';

$iH = new iHttp ();

# set PORT
# $iH->port= '8888';
$base_url = 'http://www.demo.com:8080';


# enable proxy:ture
$iH->enableProxy = true;
$iH->setProxy('localhost', 7777);

# set request cookie with string
$iH->cookie='a=1; b=2';# method-1:build cookie with string(delimiter is "; ")

# set request cookie with array
$ar_cookie = array('a'=>1,'b'=>2,'c'=>3);
$iH->ar_cookie=$ar_cookie;# method-2:build cookie with array

# set referer
$iH->reffer = 'http://www.referer.com';

# set Origin[method-1]
$iH->origin = 'http://www.phpjungle.com';

# set Origin[method-2]
$iH->requestHeader['Origin'] = 'http://www.phpjungle.com';

# set other header with requestHeader
$iH->requestHeader['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';


$html = $iH->get($base_url);

// the Raw HTTP Request like this

// GET http://www.demo.com:8080/ HTTP/1.1
// User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36
// Host: www.demo.com:8080
// Accept-Encoding: deflate, gzip
// Referer: http://www.referer.com
// Proxy-Connection: Keep-Alive
// Cookie: a=1; b=2; c=3
// Origin: http://www.phpjungle.com
// Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8