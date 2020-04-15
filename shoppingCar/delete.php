<?php
/**
 * 购物车删除
 */
include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];
$goodsId = (int)_post('goodsId');

$sql = "delete from shopping_car where user_id = ? and goods_id = ?;";

$p = $conn->prepare($sql);
$p->bind_param('ii',$userId,$goodsId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"删除成功"
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