<?php
/**
 * 加入购物车
 */
include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];
$goodsId = _post('goodsId');
// 查询是否已经存在
$sql = "select goods_id as count from shopping_car where user_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('i',$userId);
$p->bind_result($goods);
$p->execute();
$arr = array();
while($p->fetch()){
  if($goodsId == $goods){
    $res = array(
      "code"=>1004,
      "data"=>"该二手品已经存在购物车"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    $p->free_result();
    $p->close();
    $conn->close();
    die();
  }
  array_push($arr,$goods);
}
$p->free_result();
$p->close();
if(count($arr)>=20){
  $res = array(
    "code"=>1004,
    "data"=>"购物车内二手品数量超过上限"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  $conn->close();
  die();
}
// 添加

$sql = "insert into shopping_car (user_id,goods_id) ".
"values (?,?)";

$p = $conn->prepare($sql);
$p->bind_param('ii',$userId,$goodsId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>array("type"=>1,"data"=>"添加成功")
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