<?php
/**
 * 发送HTTP/HTTPS请求的类[支持批量异步GET(POST)请求,支持服务器代理]
 *
 * @author PHPJungle
 * @since 2015/03/20 周五
 * @abstract
 * 	1.希望对gets，posts方法添加回调函数支持 :)
 */
class iHttp{
	const HTTP_MEHTOD_GET = 1;
	const HTTP_MEHTOD_POST = 2;
	const CONTEYT_TYPE_FORM = 'application/x-www-form-urlencoded; charset=UTF-8'; // Content-Type:
	
	public $enableProxy = false;
	private $_proxyIP = '127.0.0.1';
	private $_proxyPort = '7777';
	
	public $ar_cookie = array(); // cookie 数组形式
	public $cookie ='';			// cookie 字符串形式
	private $_cookie = '';

	public $reffer = '';		// reffer 字符串
	public $post = array();		// post 数组键值对
	private $_poststr = '';

	public $userAgent = '';		// userAgent字符串
	public $origin = '';		// Origin
	public $port = 80;
	public $requestHeader = array(); // request header(非标准模式)
	private $_requestHeader = array(); // request header(标准模式)
	private $method = self::HTTP_MEHTOD_GET;

	private $config = array(	// 默认参数
			'userAgent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
			''
	);

	private $_lastInfo = array();
	private $_error = '';
	private $_requestUrl = '';
	private $_isHTTPS = false;

	public $res_headerstr = '';	// response header string
	public $res_ar_header = array();// response header array
	public $res_cookie = ''; // response cookie string 
	public $res_ar_cookie ='';// response cookie array 
	public $results = array();
	public $httpCode = ''; // response响应代码

	//if:重定向
	public $redirect = ''; // 最后一次重定向地址
	private $maxRedirect = 2; // 允许的最大重定向次数
	
	//tick-tock:时间校验[内部使用]
	private static $time_begin,$time_end;
	
	// time_out
	private $_connect_timeout = 120; // 连接超时时间(s)
	
	// show header info or not 
	private $_return_header = false;
	
	public function __construct(){
		$this->__init();
	}

	private function __init(){
		$this->userAgent = $this->config['userAgent'];
	}
	
	/**
	 * The number of seconds to wait while trying to connect. 
	 * 
	 * @since 2015/05/18 周一
	 * @param int $sec
	 */
	public function set_connect_timeout($sec = 0){
		if(is_numeric($sec)){
			$this->_connect_timeout = $sec;
		}
	}

	/**
	 * http_get
	 *
	 * @since 2015/03/21 周六
	 * @author Hand::PHPJungle
	 * @param string $url
	 * @return string
	 */
	public function get($url) {
		$this->method = self::HTTP_MEHTOD_GET; // 不能直接用于set_opt_array
		$this->__setRequestURL($url);

		$this->_return_header = true; # set true to return the response header after curl_exec;(add@15.06.28 周日)

		$ch = curl_init ( $this->_requestUrl );
		curl_setopt_array ( $ch, $this->__optSets () );
		$response = curl_exec($ch); // CURLOPT_RETURNTRANSFER设置为true时,
	
		$body = false;
		// get header and body(add@15.06.28 周日)
		if(false !== $response){
			$ar_back= explode(PHP_EOL.PHP_EOL, $response,2);
			if($ar_back){
				$this->res_headerstr = $ar_back['0'];
				$body = $ar_back['1'];
				
				# parse response header infos(eg.cookie etc)
				$this->parse_response_header($this->res_headerstr);
			}
		}
	
		// ERROR CHECK:
		{
			$this->_error = curl_error($ch); // 如果没错误就是空字符串
		}
		// 善后处理
		{
			$info = curl_getinfo($ch);
			$this->__setResponseInfo($info);
			$this->__summaryResponse($response);
		}
		curl_close ( $ch );
		return $body;
	}

	/**
	 * 同步/异步(默认)多个http_get
	 *
	 * @author Hand::PHPJungle
	 * @since 2015/03/21 周六
	 * @param array $urls
	 * @param bool $asyn
	 * @return array
	 */
	public function gets($urls = array(),$asyn=true){
		if (false === (is_array ( $urls ) && count ( $urls ) > 0))
			return array ();
		// asyn or not?
		$exit = array ();
		$exit = (true === $asyn) ? $this->asyn_gets ( $urls ) : array_map ( function ($e) {
			return $this->get ( $e );
		}, $urls );
		return $exit;
	}

	/**
	 * 异步多个http_get请求
	 * 
	 * @since 2015/03/23 周一
	 * @param array $urls
	 * @return array
	 */
	private function asyn_gets($urls){
		$this->method = self::HTTP_MEHTOD_GET; // 不能直接用于set_opt_array

		$chs = array ();
		$mh = curl_multi_init (); // init
		foreach ( $urls as $url ) {
			$this->__setRequestURL ( $url ); // 可能是http+https,必须遍历
			$ch = curl_init ( $this->_requestUrl );
			$chs[] = $ch;
			curl_setopt_array ( $ch, $this->__optSets () );
			curl_multi_add_handle ( $mh, $ch ); // add handle
		}

		// exec
		$active = null;
		do {
			$mrc = curl_multi_exec ( $mh, $active );// int A reference to a flag to tell whether the operations are still running.
		} while ( $active >0); // 表明还有活做

		// multi-get-contents
		$back = array();
		foreach($chs as $ch)
			$back[] = curl_multi_getcontent($ch) AND curl_multi_remove_handle($mh, $ch); // if:fail,return empty string

		// 善后处理:
		// ERROR CHECK:
		{
			//@todo 如何获取错误信息？
		}
		curl_multi_close($mh);
		return $back;
	}

	/**
	 *
	 * 返回结果解析
	 *
	 * @since 2015/03/24 周二
	 * @param string $response
	 * @return void
	 */
	private function __summaryResponse($response){
		if(false === $response)
			return false;
	}

	/**
	 * @since 2015/03/24 周二
	 * @todo Bug to fix=>多级cookie(e.g.,PREF=ID=4d47e86586dc7a8a:FF=0:TM=1426080773:LM=1426080773:S=BrtabF1uwPp_Bb5C)
	 */
	private function __setCookie() {
		// bug1.1:数组会覆盖字符串中的cookie
		// bug1.2:需要考虑多级cookie的情况
		
		if(empty($this->ar_cookie)){ // 临时解决bug1.1问题=>just use cookie string
			$this->_cookie = $this->cookie;
			return ;
		}
		
		$ar_cookie_raw = empty($this->cookie)?array():explode ( ';', $this->cookie ); // 返回array('a=b','c=d') or array()
		$ar_cookie = array();
		foreach($ar_cookie_raw as $link){
			$kv = explode ( '=', $link,2);#budFixed-20150702-修复了多级cookie解析过程中导致cookie缺失的问题@15.07.02 周四byiPHPJungle
			if(isset($kv['1'])){ // illega?yes
				$ar_cookie[trim($kv['0'])] = $kv['1'];
			}
		}
		
		$cks_raw = array_merge ( $ar_cookie, $this->ar_cookie );
		if(!is_array($cks_raw)) 
			return;
		
		$cks = array();
		foreach($cks_raw as $k=>$v){
			$cks[] = sprintf('%s=%s',$k,$v);
		}
		$this->_cookie = implode ( '; ', $cks );
	}

	private function __setRequestURL($url,$forms=array()) {
		$this->_poststr = is_array($forms)?http_build_query($forms):$forms; // bug fixed:$forms 可能为字符串@2015/03/26 周四
		$this->_requestUrl = $url;

		$info = parse_url ( $url );
		$scheme = $info ['scheme'];
		
		!isset($info['port']) or $this->port = $info['port']; // #bugFixed-20150522:url中可能带有端口号@2015/05/22 周五
		
		$this->_isHTTPS = 'https' === $scheme ? true : false;

		// ext-config
		$issetOrigin = isset($this->requestHeader['origin'])||isset($this->requestHeader['Origin']);
		if(false === $issetOrigin)
			$this->requestHeader['Origin'] = $this->origin;
		
		// last todo 
		$this->__setRequestHeader();
	}
	
	private function __setRequestHeader(){
		$this->_requestHeader = array(); // init
		if(is_array($this->requestHeader)){
			foreach($this->requestHeader as $key=>$val){
				$this->_requestHeader[] = sprintf('%s: %s',$key,$val);
			}
		}
	}

	private function __setPost($ar_form = array()){
		$this->_poststr = http_build_query($ar_form);
	}
	/**
	 * 获取curl_setopt()参数
	 *
	 * @since 2015/03/21 周六
	 * @return array
	 */
	private function __optSets(){
		$this->__setCookie();
		// default:GET
		$options = array(
				CURLOPT_HEADER => $this->_return_header,  // true:显示头信息,false不显示头信息
				CURLOPT_RETURNTRANSFER => true,// 如果成功只将结果返回，不自动输出任何内容。
				CURLOPT_PORT => $this->port, // An alternative port number to connect to.
				CURLOPT_REFERER => $this->reffer, // hearder:referer
				CURLOPT_USERAGENT => $this->userAgent,
				CURLOPT_ENCODING =>'', //  If an empty string, "", is set, a header containing all supported encoding types is sent.
				CURLOPT_COOKIE =>$this->_cookie,//multiple cookies are separated with a semicolon followed by a space (e.g., "fruit=apple; colour=red")
				CURLOPT_HTTPHEADER =>$this->_requestHeader, // 自定义header信息,格式:字符串数组(e.g.,"Content-type:text/html")

				CURLOPT_HTTPGET => true, // 默认是http_get方式请求
				CURLOPT_POST => false,
				CURLOPT_CONNECTTIMEOUT => $this->_connect_timeout //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		);

		// if:POST
		if(self::HTTP_MEHTOD_POST === $this->method){
			$options[CURLOPT_HTTPGET] = false;
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $this->_poststr; ////default type:application/x-www-from-urlencoded
		}
			
		// if:HTTPS
		if ($this->_isHTTPS) { // 拒绝使用证书的方式获取https协议的值
			$options [CURLOPT_PORT] = 443; // 端口很重要，问题就在这:{error:140770FC:SSL routines:SSL23_GET_SERVER_HELLO:unknown protocol，就是端口问题}
			$options [CURLOPT_SSL_VERIFYPEER] = false;
			$options [CURLOPT_SSL_VERIFYHOST] = false;
		}
		
		// if:enableProxy
		if(true === $this->enableProxy){
			$options [CURLOPT_PROXY] = $this->_proxyIP;
			$options [CURLOPT_PROXYPORT] = $this->_proxyPort;
		}
		
		return $options;
	}

	private function __setResponseInfo($mix){
		$this->_lastInfo = $mix;
		$this->httpCode = $mix['http_code'];
		$this->redirect = $mix['redirect_url'];
	}

	/**
	 * runtime info
	 *
	 * @return array
	 */
	public function getResponseInfo(){
		return $this->_lastInfo; //redirect_url=''表明没有重定向
	}
	
	/**
	 * error in ch
	 * 
	 * @return string
	 */
	public function getLastError(){
		return $this->_error;
	}
	
	/**
	 * set proxy
	 * 
	 * @param string $ip
	 * @param int $port
	 */
	public function setProxy($ip,$port){
		$this->_proxyIP = $ip;
		$this->_proxyPort = $port;
	}

	/**
	 * http_post
	 *
	 * @since 2015/03/24 周二
	 * @param string $url
	 * @param array $forms
	 * @return string
	 */
	public function post($url,$forms=array()){
		$this->method = self::HTTP_MEHTOD_POST;
		$this->__setRequestURL($url,$forms);

		$this->_return_header = true; # set true to return the response header after curl_exec;(add@15.06.28 周日)

		$ch = curl_init ( $this->_requestUrl );
		curl_setopt_array ( $ch, $this->__optSets () );
		$response = curl_exec($ch); // CURLOPT_RETURNTRANSFER设置为true时,
	
		$body = false;
		// get header and body
		if(false !== $response){
			$ar_back= explode(PHP_EOL.PHP_EOL, $response,2);
			if($ar_back){
				$this->res_headerstr = $ar_back['0'];
				$body = $ar_back['1'];
		
				$this->parse_response_header($this->res_headerstr);
			}
		}
	
		// ERROR CHECK:
		{
			$this->_error = curl_error($ch); // 如果没错误就是空字符串
		}
		// 善后处理
		{
			$info = curl_getinfo($ch);
			$this->__setResponseInfo($info);
			$this->__summaryResponse($response);
		}
		curl_close ( $ch );
		return $body;
	}

	/**
	 * 同步/异步(默认)多个http_post请求
	 *
	 * @since 2015/03/24 周二
	 * @param mix $urls
	 * @param array $forms
	 * @param bool $asyn
	 */
	public function posts($urls,$forms=array(),$asyn=true){
		// asyn or not?
		$exit = array ();
		$exit = (true === $asyn) ? $this->asyn_posts ( $urls ,$forms) : array_map ( function ($u,$f) {
			return $this->post ( $u ,$f);
		}, $urls,$forms );
		return $exit;
	}

	/**
	 * 异步多个http_post请求
	 *
	 * @since 2015/03/24 周二
	 * @param array $urls
	 * @param array $forms
	 * @param array $callBack
	 * @return array
	 * @abstract 回调功能说明：添加回调函数来对返回的html代码进行进一步加工<hr>
	 * 			参数个数:只有一个<hr>
	 * 			$callBack string=>回调函数是普通函数<hr>
	 * 			$callBack array=>回调函数是类成员函数<hr>
	 */
	private function asyn_posts($urls,$forms=array(),$callBack=null){
		if (false === (is_array ( $urls ) && count ( $urls ) > 0)&&count($urls)==count($forms))
			return array ();

		$this->method = self::HTTP_MEHTOD_POST;
		$chs = array ();
		$mh = curl_multi_init (); // init
		foreach ( $urls as $url ) {
			$form_e = current($forms) AND next($forms);  // BugFixed:可能$urls索引无规律
			$inner_p = isset($form_e)?$form_e:'';
			$this->__setRequestURL ( $url, $inner_p); // 可能是http+https,必须遍历
			$ch = curl_init ( $this->_requestUrl );
			$chs[] = $ch;
			curl_setopt_array ( $ch, $this->__optSets () );
			curl_multi_add_handle ( $mh, $ch ); // add handle
		}
		reset($forms);

		// exec
		$active = null;
		do {
			$mrc = curl_multi_exec ( $mh, $active );// int A reference to a flag to tell whether the operations are still running.
		} while ( $active >0); // 表明还有活做

		// multi-get-contents
		$back = array();
		foreach($chs as $ch)
			$back[] = curl_multi_getcontent($ch) AND curl_multi_remove_handle($mh, $ch); // if:fail,return empty string

		// 善后处理:
		{
			//@todo 如何获取错误信息？
		}
		curl_multi_close($mh);
		return $back;
	}
	
	static function calc_begin(){
		self::$time_begin = time();
	}
	
	static function calc_end(){
		self::$time_end = time();
		$tpl ='<div style="font-family:consolas;border:2px solid #eee;padding:5px;">It cost %s s</div>';
		$timespam =  self::$time_end-self::$time_begin;
		echo sprintf($tpl,$timespam);
	}
	
	/**
	 * parse response cookie
	 * 
	 * @since 15.06.27 周六
	 * @param string $headerstr
	 * @return void 
	 */
	private function parse_response_cookie(){
		$ar_header = $this->res_ar_header;
		if($ar_header){
			$pattern = '/Set-Cookie: (.*?)=(.*?);/'; # Set-Cookie: ASP.NET_SessionId=oszgh4r5yrbjxudfedythelj;
			foreach ($ar_header as $line){
				if(0 === strpos($line, 'Set-Cookie: ')){
					$num= preg_match_all($pattern, $line, $matches);
					if($matches['1'] AND $matches['2']){
						$this->res_ar_cookie[$matches['1']['0']] = $matches['2']['0'];
					}
				}
			}
			if($this->res_ar_cookie){
				$cks = array();
				foreach($this->res_ar_cookie as $k=>$v){
					$cks[] = sprintf('%s=%s',$k,$v);
				}
				$this->res_cookie = implode ( '; ', $cks );
			}
		}
	}
	
	/**
	 * parse response header
	 *
	 * @since 15.06.27 周六
	 * @param string $headerstr
	 * @return void
	 */
	private function parse_response_header($headerstr){
		if(empty($headerstr))
			return ;
		
		$ar_header = explode ( PHP_EOL, $headerstr );
		if($ar_header){
			$this->res_ar_header = $ar_header;
			$this->parse_response_cookie();
		}
		
		return ;
	}
}
