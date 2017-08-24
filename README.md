
 # 云联惠开放平台SDK(PHP)

 # 简介

云联惠开放平台SDK(PHP)提供快速接入能力

 # 说明

 -  测试环境和正式环境的API_PREFIX不同，需要更改OAuth2.php中的API_PREFIX
 - 普通会员/商家类型会员授权得到的token与客户端token需要理解区分一下不同点
 - 普通会员/商家类型会员授权得到的token是获取普通会员/商家类型会员的资源(基本信息，账户信息，发放积分操作[限商家类型会员]等操作)
 - 客户端token是客户端自身的资源(目前有：快捷注册[提供手机号，填写推荐人，将自动产生一个会员])

 # 用法


 # 基本用法

 ```
 require 'OAuth2.php';
 require 'Request.php';
 require 'Response.php';

 $client_id = 'YLH'; //需要更换
 $client_secret = 'YLH123';////需要更换
 $redirect_uri = 'https://docs.yunlianhui.cn';////需要更换

 $oauth_client = new OAuth2($client_id,$client_secret,$redirect_uri);
 ```

 ## 普通会员授权

```
header('Location: '.$oauth_client->getAuthUrl($redirect_uri,'basic_info'));
```
 ## 商家类型会员授权

 ```
 header('Location: '.$oauth_client->getAuthUrl($redirect_uri,'basic_info+points'));
 ```
 ## 将普通会员授权/商家类型会员授权同意得到的code换取成token

 ```
 $code = 'a3c472faef4b6fa1556aa83aa2152320527480b9';
 var_dump($oauth_client->getAccessToken($code)); //82bc130d68e5405b90599c04573a965973ffce33
 ```
 ## 获取会员的基本资料

```
//使用带basic_info的token,这里可以是普通会员也可以是商家类型会员
var_dump($oauth_client->getBasicInfo('82bc130d68e5405b90599c04573a965973ffce33'));
```
 ## 产生基本返还

```
//使用带points的token，这里只能是商家类型会员
var_dump($oauth_client->makePoints(
    '82bc130d68e5405b90599c04573a965973ffce33', //token
    '15869473625',
    '500',// 单位：分
    '20170824090238790'
));
```
 ## 积分返还撤销

```
//使用带points的token，这里只能是商家类型会员
var_dump($oauth_client->revokePoints(
    '82bc130d68e5405b90599c04573a965973ffce33',//token
    'OP1503546752159c',
    'test',
    '90'
));
```
------------

 ## 获取客户端token

```
var_dump($oauth_client->getClientCredentials()); //e1909cbe7c5f77e9d3a75a86c2f135aef1bee699
```
 ## 快捷注册

 ```
 //通过获取客户端token访问，推荐人需要是存在的会员id
 var_dump($oauth_client->register_fast('e1909cbe7c5f77e9d3a75a86c2f135aef1bee699','18600001111','ylhadmin'));
 ```
