<?php
namespace YunLianHui;

class Request
{
	/**
	 * CURL操作对象
	 * @var resource
	 */
	public $handler;

	/**
	 * Url地址
	 * @var string
	 */
	public $url;

	/**
	 * 发送内容
	 * @var mixed
	 */
	public $content;

	/**
	 * CurlOptions
	 * @var array
	 */
	public $options = array();

	/**
	 * header头
	 * @var array
	 */
	public $headers = array();

	/**
	 * Cookies
	 * @var array
	 */
	public $cookies = array();

	/**
	 * 保存Cookie文件的文件名
	 * @var string
	 */
	public $cookieFileName = '';

	/**
	 * 失败重试次数
	 * @var int
	 */
	public $retry = 0;

	/**
	 * 是否使用代理
	 * @var bool
	 */
	public $useProxy = false;

	/**
	 * 代理设置
	 * @var array
	 */
	public $proxy = array();

	/**
	 * 是否验证证书
	 * @var bool
	 */
	public $isVerifyCA = false;

	/**
	 * CA根证书路径
	 * @var string
	 */
	public $caCert;

	/**
	 * 连接超时时间，单位：毫秒
	 * @var int
	 */
	public $connectTimeout = 30000;

	/**
	 * 总超时时间，单位：毫秒
	 * @var int
	 */
	public $timeout = 0;

	/**
	 * 下载限速，为0则不限制，单位：字节
	 * @var int
	 */
	public $downloadSpeed;

	/**
	 * 上传限速，为0则不限制，单位：字节
	 * @var int
	 */
	public $uploadSpeed;

	/**
	 * 用于连接中需要的用户名
	 * @var string
	 */
	public $username;

	/**
	 * 用于连接中需要的密码
	 * @var string
	 */
	public $password;

	/**
	 * 请求结果保存至文件的配置
	 * @var mixed
	 */
	public $saveFileOption = array();

	/**
	 * 根据location自动重定向
	 * @var bool
	 */
	public $followLocation = true;

	/**
	 * 最大重定向次数
	 * @var int
	 */
	public $maxRedirects = 10;

	/**
	 * 使用自定义实现的重定向，性能较差。如果不是环境不支持自动重定向，请勿设为true
	 * @var bool
	 */
	public static $customLocation = false;

	/**
	 * 临时目录
	 * @var string
	 */
	public static $tempDir;

	/**
	 * 代理认证方式
	 */
	public static $proxyAuths = array(
		'basic'		=>	CURLAUTH_BASIC,
		'ntlm'		=>	CURLAUTH_NTLM
	);

	/**
	 * 代理类型
	 */
	public static $proxyType = array(
		'http'		=>	CURLPROXY_HTTP,
		'socks4'	=>	CURLPROXY_SOCKS4,
		'socks4a'	=>	6,	// CURLPROXY_SOCKS4A
		'socks5'	=>	CURLPROXY_SOCKS5,
	);

	/**
	 * __construct
	 */
	public function __construct()
	{
		$this->open();
		$this->cookieFileName = tempnam(null === self::$tempDir ? sys_get_temp_dir() : self::$tempDir,'');
	}

	public function __destruct()
	{
		$this->close();
	}

	public function open()
	{
		$this->handler = curl_init();
		$this->retry = 0;
		$this->headers = $this->options = array();
		$this->url = $this->content = '';
		$this->useProxy = false;
		$this->proxy = array(
			'auth'	=>	'basic',
			'type'	=>	'http',
		);
		$this->isVerifyCA = false;
		$this->caCert = null;
		$this->connectTimeout = 30000;
		$this->timeout = 0;
		$this->downloadSpeed = null;
		$this->uploadSpeed = null;
		$this->username = null;
		$this->password = null;
		$this->saveFileOption = array();
	}

	public function close()
	{
		if(null !== $this->handler)
		{
			curl_close($this->handler);
			$this->handler = null;
			if(is_file($this->cookieFileName))
			{
				unlink($this->cookieFileName);
			}
		}
	}

	/**
	 * 创建一个新会话
	 * @return Request
	 */
	public static function newSession()
	{
		return new static;
	}

	/**
	 * 设置Url
	 * @param mixed $url
	 * @return Request
	 */
	public function url($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * 设置发送内容，requestBody的别名
	 * @param  $content
     * @return $this
     */
	public function content($content)
	{
		return $this->requestBody($content);
	}

	/**
	 * 设置参数，requestBody的别名
	 * @param mixed $params
	 * @return $this
	 */
	public function params($params)
	{
		return $this->requestBody($params);
	}


    /**
     * @param $requestBody
     * @return $this
     */
    public function requestBody($requestBody)
	{
		$this->content = $requestBody;
		return $this;
	}

	/**
	 * 批量设置CURL的Option
	 * @param array $options
	 * @return $this
	 */
	public function options($options)
	{
		foreach($options as $key => $value)
		{
			$this->options[$key] = $value;
		}
		return $this;
	}

	/**
	 * 设置CURL的Option
	 * @param int $option
	 * @param mixed $value
	 * @return $this
	 */
	public function option($option, $value)
	{
		$this->options[$option] = $value;
		return $this;
	}


    /**
     * 批量设置CURL的Option
     * @param $headers
     * @return $this
     */
    public function headers($headers)
	{
		$this->headers = array_merge($this->headers, $headers);
		return $this;
	}

	/**
	 * 设置CURL的Option
	 * @param  $header
	 * @param  $value
	 * @return $this
	 */
	public function header($header, $value)
	{
		$this->headers[$header] = $value;
		return $this;
	}

	/**
	 * 设置Accept
	 * @param string $accept
	 * @return $this
	 */
	public function accept($accept)
	{
		$this->headers['Accept'] = $accept;
		return $this;
	}

	/**
	 * 设置Accept-Language
	 * @param string $acceptLanguage
	 * @return $this
	 */
	public function acceptLanguage($acceptLanguage)
	{
		$this->headers['Accept-Language'] = $acceptLanguage;
		return $this;
	}

	/**
	 * 设置Accept-Encoding
	 * @param string $acceptEncoding
	 * @return $this
	 */
	public function acceptEncoding($acceptEncoding)
	{
		$this->headers['Accept-Encoding'] = $acceptEncoding;
		return $this;
	}

	/**
	 * 设置Accept-Ranges
	 * @param string $acceptRanges
	 * @return $this
	 */
	public function acceptRanges($acceptRanges)
	{
		$this->headers['Accept-Ranges'] = $acceptRanges;
		return $this;
	}

	/**
	 * 设置Cache-Control
	 * @param string $cacheControl
	 * @return $this
	 */
	public function cacheControl($cacheControl)
	{
		$this->headers['Cache-Control'] = $cacheControl;
		return $this;
	}

	/**
	 * 设置Cookies
	 * @param array $headers
	 * @return $this
	 */
	public function cookies($headers)
	{
		$this->cookies = array_merge($this->cookies, $headers);
		return $this;
	}

	/**
	 * 设置Cookie
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function cookie($name, $value)
	{
		$this->cookies[$name] = $value;
		return $this;
	}

	/**
	 * 设置Content-Type
	 * @param string $contentType
	 * @return $this
	 */
	public function contentType($contentType)
	{
		$this->headers['Content-Type'] = $contentType;
		return $this;
	}

	/**
	 * 设置Range
	 * @param string $range
	 * @return $this
	 */
	public function range($range)
	{
		$this->headers['Range'] = $range;
		return $this;
	}

	/**
	 * 设置Referer
	 * @param  $referer
	 * @return $this
	 */
	public function referer($referer)
	{
		$this->headers['Referer'] = $referer;
		return $this;
	}

	/**
	 * 设置User-Agent
	 * @param string $userAgent
	 * @return $this
	 */
	public function userAgent($userAgent)
	{
		$this->headers['User-Agent'] = $userAgent;
		return $this;
	}

	/**
	 * 设置User-Agent，userAgent的别名
	 * @param string $userAgent
	 * @return $this
	 */
	public function ua($userAgent)
	{
		return $this->userAgent($userAgent);
	}

	/**
	 * 设置失败重试次数，状态码非200时重试
	 * @param $retry
	 * @return $this
	 */
	public function retry($retry)
	{
		$this->retry = $retry<0?0:$retry;   //至少请求1次，即重试0次
		return $this;
	}

	/**
	 * 代理
	 * @param string $server
	 * @param int $port
	 * @param string $type
	 * @param string $auth
	 * @return $this
	 */
	public function proxy($server, $port, $type = 'http', $auth = 'basic')
	{
		$this->useProxy = true;
		$this->proxy = array(
			'server'	=>	$server,
			'port'		=>	$port,
			'type'		=>	$type,
			'auth'		=>	$auth,
		);
		return $this;
	}

	/**
	 * 设置超时时间
	 * @param int $timeout 总超时时间，单位：毫秒
	 * @param int $connectTimeout 连接超时时间，单位：毫秒
	 * @return $this
	 */
	public function timeout($timeout = null, $connectTimeout = null)
	{
		if(null !== $timeout)
		{
			$this->timeout = $timeout;
		}
		if(null !== $connectTimeout)
		{
			$this->connectTimeout = $connectTimeout;
		}
		return $this;
	}

	/**
	 * 限速
	 * @param int $download 下载速度，为0则不限制，单位：字节
	 * @param int $upload 上传速度，为0则不限制，单位：字节
	 * @return $this
	 */
	public function limitRate($download = 0, $upload = 0)
	{
		$this->downloadSpeed = $download;
		$this->uploadSpeed = $upload;
		return $this;
	}

	/**
	 * 设置用于连接中需要的用户名和密码
	 * @param string $username
	 * @param string $password
	 * @return $this
	 */
	public function userPwd($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		return $this;
	}

	/**
	 * 保存至文件的设置
	 * @param string $filePath
	 * @param string $fileMode
	 * @return $this
	 */
	public function saveFile($filePath, $fileMode = 'w+')
	{
		$this->saveFileOption['filePath'] = $filePath;
		$this->saveFileOption['fileModel'] = $fileMode;
		return $this;
	}

	/**
	 * 获取文件保存路径
	 * @return string
	 */
	public function getSavePath()
	{
		return $this->saveFileOption['savePath'];
	}

	/**
	 * 发送请求
	 * @param string $url
	 * @param array $requestBody
     * @param $method
	 * @return Response
	 */
	public function send($url = null, $requestBody = array(), $method = 'GET')
	{
		if(null !== $url)
		{
			$this->url = $url;
		}
		if(!empty($requestBody))
		{
			if(is_array($requestBody))
			{
				$this->content = http_build_query($requestBody);
			}
			else
			{
				$this->content = $requestBody;
			}
		}
		curl_setopt_array($this->handler, array(
			// 请求方法
			CURLOPT_CUSTOMREQUEST	=> $method,
			// 返回内容
			CURLOPT_RETURNTRANSFER	=> true,
			// 返回header
			CURLOPT_HEADER			=> true,
			// 发送内容
			CURLOPT_POSTFIELDS		=> $this->content,
			// 保存cookie
			CURLOPT_COOKIEFILE		=> $this->cookieFileName,
			CURLOPT_COOKIEJAR		=> $this->cookieFileName,
			// 自动重定向
			CURLOPT_FOLLOWLOCATION	=> self::$customLocation ? false : $this->followLocation,
			// 最大重定向次数
			CURLOPT_MAXREDIRS		=> $this->maxRedirects,
		));
		$this->parseCA();
		$this->parseOptions();
		$this->parseProxy();
		$this->parseHeaders();
		$this->parseCookies();
		$this->parseNetwork();
		$count = 0;
        $httpCode = '';
        $response = null;
		do{
			curl_setopt($this->handler, CURLOPT_URL, $url);
			for($i = 0; $i <= $this->retry; ++$i)
			{
				$response = new Response($this->handler, curl_exec($this->handler));
				$httpCode = $response->httpCode();
				// 状态码为5XX或者0才需要重试
				if(!(0 === $httpCode || (5 === (int)($httpCode/100))))
				{
					break;
				}
			}
			if(self::$customLocation && (301 === $httpCode || 302 === $httpCode) && ++$count <= $this->maxRedirects)
			{
				$url = $response->headers['Location'];
			}
			else
			{
				break;
			}
		}while(true);
		// 关闭保存至文件的句柄
		if(isset($this->saveFileOption['fp']))
		{
			fclose($this->saveFileOption['fp']);
			$this->saveFileOption['fp'] = null;
		}
		return $response;
	}

	/**
	 * GET请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function get($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'GET');
	}

	/**
	 * POST请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function post($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'POST');
	}

	/**
	 * HEAD请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function head($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'HEAD');
	}

	/**
	 * PUT请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function put($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'PUT');
	}

	/**
	 * PATCH请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function patch($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'PATCH');
	}

	/**
	 * DELETE请求
	 * @param string $url
	 * @param array $requestBody
	 * @return Response
	 */
	public function delete($url = null, $requestBody = array())
	{
		return $this->send($url, $requestBody, 'DELETE');
	}

	/**
	 * 直接下载文件
	 * @param string $fileName
	 * @param string $url
	 * @param array $requestBody
	 * @param string $method
	 * @return Response
	 */
	public function download($fileName, $url = null, $requestBody = array(), $method = 'GET')
	{
		$result = $this->saveFile($fileName)->send($url, $requestBody, $method);
		$this->saveFileOption = array();
		return $result;
	}

	/**
	 * 处理Options
	 */
	protected function parseOptions()
	{
		curl_setopt_array($this->handler, $this->options);
		// 请求结果保存为文件
		if(isset($this->saveFileOption['filePath']) && null !== $this->saveFileOption['filePath'])
		{
			curl_setopt_array($this->handler, array(
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => false,
			));
			$filePath = $this->saveFileOption['filePath'];
			$last = substr($filePath, -1, 1);
			if('/' === $last || '\\' === $last)
			{
				// 自动获取文件名
				$filePath .= basename($this->url);
			}
			$this->saveFileOption['savePath'] = $filePath;
			$this->saveFileOption['fp'] = fopen($filePath, isset($this->saveFileOption['fileMode']) ? $this->saveFileOption['fileMode'] : 'w+');
			curl_setopt($this->handler, CURLOPT_FILE, $this->saveFileOption['fp']);
		}
	}

	/**
	 * 处理代理
	 */
	protected function parseProxy()
	{
		if($this->useProxy)
		{
			curl_setopt_array($this->handler, array(
				CURLOPT_PROXYAUTH	=> self::$proxyAuths[$this->proxy['auth']],
				CURLOPT_PROXY		=> $this->proxy['server'],
				CURLOPT_PROXYPORT	=> $this->proxy['port'],
				CURLOPT_PROXYTYPE	=> 'socks5' === $this->proxy['type'] ? (defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : self::$proxyType[$this->proxy['type']]) : self::$proxyType[$this->proxy['type']],
			));
		}
	}

	/**
	 * 处理Headers
	 */
	protected function parseHeaders()
	{
		curl_setopt($this->handler, CURLOPT_HTTPHEADER, $this->parseHeadersFormat());
	}

	/**
	 * 处理Cookie
	 */
	protected function parseCookies()
	{
		$content = '';
		foreach($this->cookies as $name => $value)
		{
			$content .= "{$name}={$value}; ";
		}
		curl_setopt($this->handler, CURLOPT_COOKIE, $content);
	}

	/**
	 * 处理成CURL可以识别的headers格式
	 * @return array
	 */
	protected function parseHeadersFormat()
	{
		$headers = array();
		foreach($this->headers as $name => $value)
		{
			$headers[] = $name . ':' . $value;
		}
		return $headers;
	}

	/**
	 * 处理证书
	 */
	protected function parseCA()
	{
		if($this->isVerifyCA)
		{
			curl_setopt_array($this->handler, array(
				CURLOPT_SSL_VERIFYPEER	=> true,
				CURLOPT_CAINFO			=> $this->caCert,
				CURLOPT_SSL_VERIFYHOST	=> 2,
			));
		}
		else
		{
			curl_setopt_array($this->handler, array(
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_SSL_VERIFYHOST	=> 0,
			));
		}
	}

	/**
	 * 处理网络相关
	 */
	protected function parseNetwork()
	{
		// 用户名密码处理
		if('' != $this->username)
		{
			$userPwd = $this->username . ':' . $this->password;
		}
		else
		{
			$userPwd = '';
		}
		curl_setopt_array($this->handler, array(
			// 连接超时
			CURLOPT_CONNECTTIMEOUT_MS		=> $this->connectTimeout,
			// 总超时
			CURLOPT_TIMEOUT_MS				=> $this->timeout,
			// 下载限速
			CURLOPT_MAX_RECV_SPEED_LARGE	=> $this->downloadSpeed,
			// 上传限速
			CURLOPT_MAX_SEND_SPEED_LARGE	=> $this->uploadSpeed,
			// 连接中用到的用户名和密码
			CURLOPT_USERPWD					=> $userPwd,
		));
	}
}
