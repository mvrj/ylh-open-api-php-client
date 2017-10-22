
 # 云联惠开放平台SDK(PHP)

 # 简介
 
云联惠开放平台SDK(PHP)提供快速接入能力
 # 安装
  composer require mwkj/ylh-open-api-php-client
 # 说明
 - 普通会员/商家类型会员授权得到的token与客户端token需要理解区分一下不同点
 - 普通会员/商家类型会员授权得到的token是获取普通会员/商家类型会员的资源(基本信息，账户信息，发放积分操作[限商家类型会员]等操作)
 - 客户端token是客户端自身的资源(目前有：快捷注册[提供手机号，填写推荐人，将自动产生一个会员])

 # SDK配置
```php
<?php
$config = [
    //应用ID
    'client_id' => 'Client',
    //应用密钥
    'client_secret' => 'ClientSecret',
    //应用回调地址
    'redirect_uri' => 'https://example.com/callback',
    //应用私钥，您的原始格式RSA私钥
    'client_private_key' => "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALXQr1Rowgyh+UtXY26RK/FXDEpYNN0S3tUZgctXwqCR6WFnzUPSFfDeWcmheRADT/6griac6rBJjsb8yQmg1dUteqGHym9fRkmb0GgrbIyw1k99UzuPq8Gqw4HTWaykTHf09AwIlmJCRpxmZAeIaCW/3gbFrB/10/wziaV1HF1RAgMBAAECgYBmkER3z2i4C58/+xoiQ06QpFRQlBWuKHj/qZXoiadHQUhwJEhM2/R4nlX0QlyyxcuYjjfvnFFgBP7ADdWy0sIMvhMqEUp8BrQngpAq4njhEajzYGBqPW9a05osFvX7IJl3h1KFHo354Jh5SS5znbZWnkoUfoGDPMrW02DTS8/T6QJBAOMP3XHeFXKmPBExh7EvxUKEZa53GJk/eU6qaQhIpUGVD68bhZPL07t/nuZYmAAycrTvbsT7aFb6alFKGRIXth8CQQDM/Jh8UcXQbWCvsmFTJKSuDaFXLX7dtIgLhGpXsIAP9tJBGGFVUY3w29V6j/AiJHgHCRv68WFdzN92+czAuh6PAkEAyDfMH8UiCne1DcAsE7y451+Rvda8tR04XXp8pVZRilPjgZf2II4iBPqS5jEGz12ssglTFpVNuyyTJVz+YGrSJwJANYKq+6kNDn+/AZ57MY0bQCRmva8uswlxijAi4ok8pO41rLCEmBUWDI4WiEwSz5bdjlieaT+hvy7AFvrWrGjpmQJAb0tiaqAPlJMpGQl6uEx/TSCT2oDsxehyKOCDOnyIUEnY9apDSBvlEXavc7TNHmrp0r3GEkYEW4Ba9nKKEKFhpg==",
    //同步跳转
    'return_url' => 'https://example.com/example/return_url.php',
    //异步通知地址
    'notify_url' => 'https://example.com/example/notify_url.php',
    //云联惠API网关
    'gatewayUrl' => "https://openapi.yunlianhui.com",
    //云联惠开放平台收银台
    'gatewayPay' => "https://openpay.yunlianhui.com",
    //云联惠公钥,查看地址：https://docs.yunlianhui.com/keyManage
    'alipay_public_key' => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCRTiNWa8g/MQvbQg9z6Y6L2fe8Pu5ytoEGf1JBbCkr9GYC+tiH8cqLb6GoewYCaXoNzu5TCSZfFTkYOn28pOG4aMHn7WDHPpjAlXG2iYUfwsRnlQ+xci3g3hfrgQAzWj+QYqYxoxvkkfad7NrkWf4PClfqWzkz+TI7N1wyGm326QIDAQAB",

];
```
 # 基本用法

 ```php
 <?php 
     require_once 'vendor/autoload.php';
     
     use YunLianHui\OAuth2;
     
     $config = [
         //应用ID
         'client_id' => 'Client',
         //应用密钥
         'client_secret' => 'ClientSecret',
         //应用回调地址
         'redirect_uri' => 'https://example.com/callback',
         //应用私钥，您的原始格式RSA私钥
         'client_private_key' => "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALXQr1Rowgyh+UtXY26RK/FXDEpYNN0S3tUZgctXwqCR6WFnzUPSFfDeWcmheRADT/6griac6rBJjsb8yQmg1dUteqGHym9fRkmb0GgrbIyw1k99UzuPq8Gqw4HTWaykTHf09AwIlmJCRpxmZAeIaCW/3gbFrB/10/wziaV1HF1RAgMBAAECgYBmkER3z2i4C58/+xoiQ06QpFRQlBWuKHj/qZXoiadHQUhwJEhM2/R4nlX0QlyyxcuYjjfvnFFgBP7ADdWy0sIMvhMqEUp8BrQngpAq4njhEajzYGBqPW9a05osFvX7IJl3h1KFHo354Jh5SS5znbZWnkoUfoGDPMrW02DTS8/T6QJBAOMP3XHeFXKmPBExh7EvxUKEZa53GJk/eU6qaQhIpUGVD68bhZPL07t/nuZYmAAycrTvbsT7aFb6alFKGRIXth8CQQDM/Jh8UcXQbWCvsmFTJKSuDaFXLX7dtIgLhGpXsIAP9tJBGGFVUY3w29V6j/AiJHgHCRv68WFdzN92+czAuh6PAkEAyDfMH8UiCne1DcAsE7y451+Rvda8tR04XXp8pVZRilPjgZf2II4iBPqS5jEGz12ssglTFpVNuyyTJVz+YGrSJwJANYKq+6kNDn+/AZ57MY0bQCRmva8uswlxijAi4ok8pO41rLCEmBUWDI4WiEwSz5bdjlieaT+hvy7AFvrWrGjpmQJAb0tiaqAPlJMpGQl6uEx/TSCT2oDsxehyKOCDOnyIUEnY9apDSBvlEXavc7TNHmrp0r3GEkYEW4Ba9nKKEKFhpg==",
         //同步跳转
         'return_url' => 'https://example.com/example/return_url.php',
         //异步通知地址
         'notify_url' => 'https://example.com/example/notify_url.php',
         //云联惠API网关
         'gatewayUrl' => "https://openapi.yunlianhui.com",
         //云联惠开放平台收银台
         'gatewayPay' => "https://openpay.yunlianhui.com",
         //云联惠公钥,查看地址：https://docs.yunlianhui.com/keyManage
         'alipay_public_key' => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCRTiNWa8g/MQvbQg9z6Y6L2fe8Pu5ytoEGf1JBbCkr9GYC+tiH8cqLb6GoewYCaXoNzu5TCSZfFTkYOn28pOG4aMHn7WDHPpjAlXG2iYUfwsRnlQ+xci3g3hfrgQAzWj+QYqYxoxvkkfad7NrkWf4PClfqWzkz+TI7N1wyGm326QIDAQAB",
     
     ];
     $oauth_client = new OAuth2($config['gatewayUrl'],$config['client_id'],$config['client_secret'],$config['client_private_key'],$config['redirect_uri']);

 ```

 ## 普通会员授权

```php
header('Location: '.$oauth_client->getAuthorizationUrl($redirect_uri,'basic_info'));
```
 ## 商家类型会员授权

 ```php
 //多个scope时，空格隔开
 header('Location: '.$oauth_client->getAuthorizationUrl($redirect_uri,'basic_info points'));
 ```
 ## 将普通会员授权/商家类型会员授权同意得到的code换取成token

 ```php
 $code = 'a3c472faef4b6fa1556aa83aa2152320527480b9';
 $access_token = $oauth_client->getAccessToken($code);
 ```
 ## 获取会员的基本资料

```php
<?php

try{
    $member_basic_info = $oauth_client->sendAnResourceRequest([
    'client_id' => $config['client_id'],
    'access_token' => '00010b63924acada924e2a5fc25573682cda851b',
    'timestamp' => (string)time(),
    //sign 会自动签名
    ],'api/v2/returnPoints');
}catch (\YunLianHui\ApiException $exception){
    print_r('接口请求失败<br> '.'错误信息是:'.$exception->getMessage());
}

```
 ## 积分全返

```php
<?php

try{
    $pointsAllReturn = $oauth_client->sendAnResourceRequest([
        'client_id' => $config['client_id'],
        'access_token' => '00010b63924afaed984e2a5fc25575682cda853b',
        'buyer_mobile' => '18695695128',
        'total_amount' => '98',
        'out_trade_no' => '2017000000000000001',
        'body' => 'Iphone X 128 黑色',
        'subject' => 'Iphone X 128G',
        'timestamp' => (string)time(),
    ],'api/v2/returnPoints');
}catch (\YunLianHui\ApiException $exception){
    print_r('接口请求失败<br> '.'错误信息是:'.$exception->getMessage());
}
````

## 红积分/预付款支付demo
 1. 参考 https://github.com/mwkj/ylh-open-pay-php-demo
 

 
