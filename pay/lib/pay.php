<?php
require_once("epay.config.php");
require_once("EpayCore.class.php");
require_once 'api.config.php';

// 支付网关地址标准化，避免因为漏协议或漏斜杠导致 submit.php 打不开。
$baseUrl = trim((string)($baseUrl ?? ''));
if ($baseUrl !== '' && !preg_match('#^https?://#i', $baseUrl)) {
    $baseUrl = 'https://' . $baseUrl;
}
$baseUrl = $baseUrl === '' ? '' : rtrim($baseUrl, '/') . '/';

if ($baseUrl === '') {
    exit('<script>alert("支付网关地址未配置，请先在后台支付设置中填写。");history.back();</script>');
}

if (trim((string)($merchantId ?? '')) === '' || trim((string)($secreKey ?? '')) === '') {
    exit('<script>alert("商户ID或商户密钥未配置，请先在后台支付设置中完善。");history.back();</script>');
}

// 彩虹易支付配置
$epay_config['apiurl'] = $baseUrl;

//商户ID
$epay_config['pid'] = $merchantId;

//商户密钥
$epay_config['key'] = $secreKey;

/**************************请求参数**************************/
$path_notify = "/pay/notify_url.php";


$path_return = "/pay/return_url.php";


$callbackBase = trim((string)($callbackUrl ?? ''));
if ($callbackBase === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $callbackBase = $host !== '' ? $scheme . '://' . $host : '';
}
$callbackBase = rtrim($callbackBase, '/');

$notify_url = $callbackBase . $path_notify;
//需http://格式的完整路径，不能加?id=123这类自定义参数 网址/pay/notify_url.php

//页面跳转同步通知页面路径
$return_url = $callbackBase . $path_return;
//需http://格式的完整路径，不能加?id=123这类自定义参数 网址/pay/return_url.php

//商户订单号
$out_trade_no = $_POST['WIDout_trade_no'];
//商户网站订单系统中唯一订单号，必填

//支付方式（可传入alipay,wxpay,qqpay,bank,jdpay）
$type = $_POST['payment_method'];
//商品名称
$name = $_POST['WIDsubject'];
//付款金额
$money = $_POST['money'];


/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
	"pid" => $epay_config['pid'],
	"type" => $type,
	"notify_url" => $notify_url,
	"return_url" => $return_url,
	"out_trade_no" => $out_trade_no,
	"name" => $name,
	"money"	=> $money,
);

//建立请求
$epay = new EpayCore($epay_config);
$html_text = $epay->pagePay($parameter);
echo $html_text;



 // 关闭数据库连接
 $conn->close();
?>
