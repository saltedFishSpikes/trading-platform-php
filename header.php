<?php
/**
 * 项目头文件
 */
include_once 'token.php';
include_once 'common.php';

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST,GET');
header('Access-Control-Allow-Headers:*');
//时区
date_default_timezone_set("Asia/Shanghai");
//错误日志
ini_set("display_errors","off");
ini_set("log_errors","on");
ini_set("error_log","../../../logs/php_error.log");
//连接数据库
$conn=new mysqli("localhost","root","","trading_platform",3308);
if($conn->connect_error){
  $res = array("code"=>0,"data"=>$conn->connect_error);
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  die;
}
$conn->query("set character 'utf8'");
$conn->query("set names 'utf8'");

//token
$Token = new Token();

$baseUrl = 'http://localhost:80/trading-platform';