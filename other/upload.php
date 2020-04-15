<?php
/**
 * 上传图片
 */

include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];
$root = iconv("UTF-8", "GBK", "../image");
if(!is_dir($root)){
  mkdir($root,777);
}
$dir = iconv("UTF-8", "GBK", "../image/".$userId);
if(!is_dir($dir)){
  mkdir($dir,777);
}

$file = _file('file');
//有相同图片直接返回
foreach(scandir($dir) as $t){
  if($t == "." || $t == ".."){
    continue;
  }else{
    if(sha1_file($dir.'/'.$t) === sha1_file($file['tmp_name'])){
      $res = array(
        "code"=>1000,
        "data"=>$baseUrl."/image/".$userId.'/'.$t
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
      $conn->close();
      die();
    }
  }
}
//相同图片不存在则添加
$t = substr($file['name'],strripos($file['name'],'.'));

$filename = date("YmdHis").$t;//确定上传的文件名

if(!file_exists("../image/".$userId.$filename)){ 
	move_uploaded_file($file['tmp_name'],"../image/".$userId.'/'.$filename);
}

$res = array(
  "code"=>1000,
  "data"=>$baseUrl."/image/".$userId.'/'.$filename
);
echo json_encode($res,JSON_UNESCAPED_UNICODE);

$conn->close();