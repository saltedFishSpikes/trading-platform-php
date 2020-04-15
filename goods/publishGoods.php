<?php
/**
 * 发布新商品
 */
include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];
$goodsName = _post("goodsName");
$goodsPrice = floatval(_post("goodsPrice"));
$goodsImg = _post("goodsImg");
$goodsType = (int)_post("goodsType");
$goodsDesc = _post("goodsDesc");

$sql = "insert into goods "
."(goods_name,goods_img,goods_price,seller_id,goods_type,publish_time,change_time,goods_status,goods_desc) "
."values (?,?,?,?,?,?,?,?,?);";
$img = [];
if(!empty($goodsImg)){
  $img = explode(',',$goodsImg);
  for($i = 0; $i < count($img); $i++){
    $index = strripos($img[$i],'/');
    $img[$i] = substr($img[$i],$index+1);
  }
}

$img = implode(',',$img);
$publishTime = (int)getUnixTimestamp();
$status = (int)1;
$p = $conn->prepare($sql);
$p->bind_param('ssdiiiiis',$goodsName,$img,$goodsPrice,$userId,$goodsType,$publishTime,$publishTime,$status,$goodsDesc);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"提交成功"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else {
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}

//关闭连接
$p->free_result();
$p->close();
$conn->close();