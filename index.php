<?php

require 'src/autoload.php';

use YunLianHui\OAuth2;

$client_id = 'Allen';
$client_secret = 'Allen';
$redirect_uri = 'https://docs.yunlianhui.com/back.php';
$client_private_key = '556696225889555';

$oauth_client = new OAuth2($client_id,$client_secret,$client_private_key,$redirect_uri);

// 普通会员授权--拼接url  basic_info
header('Location: '.$oauth_client->getAuthUrl($redirect_uri,'basic_info points'));

// 商家类型会员授权 basic_info+points
//header('Location: '.$oauth_client->getAuthUrl($redirect_uri,'basic_info+points'));

//将code换取token  (将$code换成普通会员授权或者商家类型会员授权同意之后浏览器重定向的code)
//$code = 'a3c472faef4b6fa1556aa83aa2152320527480b9';
//var_dump($oauth_client->getAccessToken($code));  //82bc130d68e5405b90599c04573a965973ffce30

//基本资料
var_dump($oauth_client->getBasicInfo('f28062b3270b651bc636d882593515bb2d995903'));

//产生积分返还
//var_dump($oauth_client->makePoints(
//    'f28062b3270b651bc636d882593515bb2d995903',
//    '15869473625',
//    '500',// 单位：分
//    '20170824090238790'
//));

//积分返还撤销
//var_dump($oauth_client->revokePoints(
//    'f28062b3270b651bc636d882593515bb2d995903',
//    'OP1503546752159c',
//    'test',
//    '90'
//));

//获取客户端token
//var_dump($oauth_client->getClientCredentials());

//快捷注册
//var_dump($oauth_client->register_fast('e1909cbe7c5f77e9d3a75a86c2f135aef1bee697','18695695128','ylhadmin'));

