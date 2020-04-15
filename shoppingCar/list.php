<?php
/**
 * 购物车列表
 */
include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];

$sql = "select g.goods_id,g.goods_name,g.goods_price,g.goods_img,g.seller_id from "
."(select goods_id,user_id from shopping_car group by goods_id,user_id) as s "
."inner join "
."(select goods_id,goods_name,goods_price,goods_img,seller_id from goods"
.") as g on g.goods_id = s.goods_id "
."where s.user_id = ?";
$p = $conn->prepare($sql);
$p->bind_param('i',$userId);
$p->bind_result($goodsId,$goodsName,$goodsPrice,$goodsImg,$sellerId);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName,
      "price"=>$goodsPrice,
      "goodsImg"=>(!empty($goodsImg)?($baseUrl.'/image/'.$sellerId.'/'.explode(',',$goodsImg)[0]):"")
    );
    array_push($arr,$t);
  }
  $res = array(
    "code"=>1000,
    "data"=>$arr
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