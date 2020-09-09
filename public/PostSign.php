<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-27 18:00:08
 * @LastEditTime: 2020-08-19 08:28:41
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 签名公共方法
 * @FilePath: \www.rhtapi.com\public\PostSign.php
 */

//调用curl方式
require("w000.php");

function post_sign($method,$bizcontent){

//版本
$version='1.0';
//appkey
$appkey = "11616600";
//AppeSecret
$AppSecret = "9e5c0fc937b843baae8597e27d1fceda";
//请求URL
$url = "http://open.jackyun.com/open/openapi/do";

// 将除sign和contextid参数除外的所有“参数+参数值”进行字典排序生成字符串。
$date_string="appkey".$appkey."bizcontent".$bizcontent."contenttype"."json"."method".$method
."timestamp".date('Y-m-d H:i:s', time())."version".$version;
echo "<pre>";
print_r($date_string);//die;

// 将AppSecret加到该字符串的首尾转小写并进行MD5加密
$date_md5 = strtolower($AppSecret.$date_string.$AppSecret);
echo "<pre>";
print_r($date_md5);//die;

// md5加密生成sign签名
$sign = md5($date_md5);
echo "<pre>"."----签名-----"."<br>";
print_r($sign);//die;

// POST内容
$postfields = "timestamp=".date('Y-m-d H:i:s', time())."&appkey=".$appkey."&bizcontent=".$bizcontent."&contenttype=json&method=".$method.
"&sign=".$sign."&version=".$version;

// 发送请求
$apires = httpRequest($url,$postfields,true);

$json_array=json_decode($apires,true);
// echo "<pre>"."返回结果--"."<br>";
// print_r($json_array);//die;
return  $json_array;

}

?>