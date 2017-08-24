<?php
namespace YunLianHui;

class OAuth2
{
    /**
     * http请求类
     * @var Request
     */
    public $http;
    /**
     * 应用的唯一标识。
     * @var string
     */
    public $client_id;

    /**
     * client_id对应的密钥
     * @var string
     */
    public $client_secret;

    /**
     * 回调地址
     * @var string
     */
    public $redirect_uri;

    /**
     * state值
     * @var string
     */
    public $state;

    /**
     * 授权权限列表
     * @var array
     */
    public $scope;

    /**
     * 接口调用结果
     * @var array
     */
    public $result;

    /**
     * access_token，调用相应方法后可以获取到
     * @var string
     */
    public $access_token;

    /**
     * user_id，调用相应方法后可以获取到
     * @var string
     */
    public $user_id;

    /**
     * api前缀
     * 测试站： https://opentest.yunlianhui.com
     * 正式站： https://open.yunlianhui.com
     */
    //const API_PREFIX = 'https://open.yunlianhui.com'; //正式站
    const API_PREFIX = 'https://opentest.yunlianhui.com'; //测试站

    /**
     * 构造方法
     * @param string $client_id
     * @param string $client_secret
     * @param string $redirect_uri
     */
    public function __construct($client_id, $client_secret, $redirect_uri)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->http = new Request();
    }


	/**
	 * 拼接url方法
	 * @param string $name 跟在域名后的文本
	 * @param array $params GET参数
	 * @return string
	 */
	public function getUrl($name, $params = array())
	{
		return static::API_PREFIX . '/' . $name . (empty($params) ? '' : ('?' . \http_build_query($params)));
	}
    /**
     * 获取state值
     * @param string $state
     * @return string
     */
    protected function getState($state = null)
    {
        if(null === $state)
        {
            if(null === $this->state)
            {
                $this->state = md5(\uniqid('', true));
            }
        }
        else
        {
            $this->state = $state;
        }
        return $this->state;
    }


    /**
     * 签名
     * @param $params
     * @param $secretKey
     * @return string
     */
    protected function generateSign($params, $secretKey)
    {
        ksort($params);
        $stringToBeSigned = $secretKey;
        foreach ($params as $k => $v)
        {
            if(is_string($v) && "@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $secretKey;
        return strtoupper(md5($stringToBeSigned));
    }
	/**
	 *  1） 拼接授权url
	 * @param string $redirect_uri  指的是应用发起请求时，所传的回调地址参数
	 * @param string $scope 访问的权限，可以多个叠加。如： scope=A+B
	 * @return string
	 */
	public function getAuthUrl($redirect_uri = null, $scope = 'basic_info')
	{
		return $this->getUrl('token/authorize', array(
			'client_id'			=>	$this->client_id,
			'redirect_uri'		=>	null === $redirect_uri ? $this->redirect_uri : $redirect_uri,
			'scope'				=>	$scope,
			'response_type' => 'code',
			'state'				=>	$this->getState(),
		));
	}

	 /**
	 *  2）引导用户登录授权
	 *  引导用户通过浏览器访问以上授权url，将弹出如下登录页面。用户输入账号、密码点“登录”按钮，即可进入授权页面。
	 *  （页面操作，无代码）
	 */

	 /**
	 *  3）获取code
	 *  若用户点“授权”按钮后，云联惠开放平台会将授权码code 返回到了回调地址上，应用可以获取并使用该code去换取access_token。
	 *  测试情况下手动将浏览器重定向后的code获取
	 *  （手动操作，无代码）
	 */



    /**
     * 4） 换取access_token
     * @param $code  $redirectUri地址中传过来的code，为null则通过get参数获取
     * @throws ApiException
     * @return mixed
     */
	public function getAccessToken($code)
	{
		$this->result = json_decode($this->http->post($this->getUrl('token/authorize/accesstoken'),
            [
			'client_id'		=>	$this->client_id,
			'client_secret'	=>	$this->client_secret,
			'grant_type'	=>	'authorization_code',
			'code'			=>	$code,
			'redirect_uri'	=>	$this->redirect_uri,
            ])->body, true);

		//return $this->result;
		if(isset($this->result['error']))
		{
			throw new ApiException($this->result['error'], $this->result['error_description']);
		}
		else
		{
			return $this->result['access_token'];
		}
	}

	/**
	 * 获取基本信息
	 * @param string $access_token
     * @throws ApiException
	 * @return mixed
	 */
	public function getBasicInfo($access_token = null)
	{

	    $params = [
	        'client_id' => $this->client_id,
            'access_token' => null === $access_token ? $this->access_token : $access_token,
            'timestamp' => date('Y-m-d H:i:s',time()+8*3600), //取东八区
        ];
        $params['sign'] = $this->generateSign($params,$this->client_secret);

		$this->result = json_decode($this->http->post($this->getUrl('api/basic_info'),$params)->body, true);
		if($this->result['error_code'] !== 0)
		{
			throw new ApiException($this->result['error'], $this->result['error_code']);
		}
		else
		{
			return $this->result;
		}
	}
	/**
	 * 产生积分返还
	 * @param string $access_token
     * @param string $buyer_mobile
     * @param string $money
     * @param string $order_msg
     * @throws ApiException
	 * @return array
	 */
	public function makePoints($access_token = null,$buyer_mobile,$money,$order_msg)
	{
        $params = [
            'client_id' => $this->client_id,
            'access_token' => null === $access_token ? $this->access_token : $access_token,
            'buyer_mobile' => $buyer_mobile,
            'money' => $money,
            'order_msg' => $order_msg,
            'timestamp' => date('Y-m-d H:i:s',time()+8*3600), //取东八区
        ];
        $params['sign'] = $this->generateSign($params,$this->client_secret);

		$this->result = json_decode($this->http->post($this->getUrl('api/points'),$params)->body, true);
        if($this->result['error_code'] !== 0)
        {
            throw new ApiException($this->result['error'], $this->result['error_code']);
        }
        else
        {
            return $this->result;
        }
	}
	/**
	 * 积分返还撤销
     * @param string $access_token
     * @param string $order_id
     * @param string $reason
     * @param string $money
     * @throws ApiException
	 * @return array
	 */
	public function revokePoints($access_token = null,$order_id,$reason,$money)
	{
        $params = [
            'client_id' => $this->client_id,
            'access_token' => null === $access_token ? $this->access_token : $access_token,
            'order_id' => $order_id,
            'reason' => $reason,
            'money' => $money,
            'timestamp' => date('Y-m-d H:i:s',time()+8*3600), //取东八区
        ];
        $params['sign'] = $this->generateSign($params,$this->client_secret);

        $this->result = json_decode($this->http->post($this->getUrl('api/points_revoke'),$params)->body, true);
        if($this->result['error_code'] !== 0)
        {
            throw new ApiException($this->result['error'], $this->result['error_code']);
        }
        else
        {
            return $this->result;
        }
	}


    /**
     * 客户端获取访问令牌
     * @param string $scope
     * @return mixed
     * @throws ApiException
     */
    public function getClientCredentials($scope='register_fast')
    {
        $this->result = json_decode($this->http->post($this->getUrl('token/client_credentials'),
            [
                'client_id'		=>	$this->client_id,
                'client_secret'	=>	$this->client_secret,
                'grant_type'	=>	'client_credentials',
                'scope'			=>	$scope
            ])->body, true);

        //return $this->result;
        if(isset($this->result['error']))
        {
            throw new ApiException($this->result['error'], $this->result['error_description']);
        }
        else
        {
            return $this->result['access_token'];
        }
    }
    /**
     * 快捷注册，产生临时会员
     * @param string $access_token
     * @param string $mobile
     * @param string $rcm_id
     * @throws ApiException
     * @return mixed
     */
    public function register_fast($access_token = null,$mobile,$rcm_id)
    {
        $params = [
            'client_id' => $this->client_id,
            'access_token' => null === $access_token ? $this->access_token : $access_token,
            'mobile' => $mobile,
            'rcm_id' => $rcm_id,
            'timestamp' => date('Y-m-d H:i:s',time()+8*3600), //取东八区
        ];
        $params['sign'] = $this->generateSign($params,$this->client_secret);

        $this->result = json_decode($this->http->post($this->getUrl('api/register_fast'),$params)->body, true);
        if($this->result['error_code'] !== 0)
        {
            throw new ApiException($this->result['error'], $this->result['error_code']);
        }
        else
        {
            return $this->result;
        }
    }

	/**
	 * 刷新AccessToken
	 * @return bool
	 */
	public function refreshToken()
	{
		// 暂时不支持刷新
		return false;
	}


}
