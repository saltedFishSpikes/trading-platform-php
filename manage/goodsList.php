<?php
/**
 * 商品列表（管理）
 */

include_once '../header.php';
include_once '../verifyToken.php';

$userType = $user['type'];
if($userType != 3){
  $err = array(
    "code"=>1001,
    "data"=>"该账号不是管理员账号，请重新登录"
  );
  echo json_encode($err,JSON_UNESCAPED_UNICODE);
  die;
}

$goodsName = _post("goodsName");
$sellerName = _post("sellerName");
$sellerId = _post("sellerId");
$goodsStatus = _post("goodsStatus");
$pageSize = _post("pageSize");
$pageNum = _post("pageNum");

$sql = "select goods_id,goods_name,goods_price,seller_id,user.user_name as seller_name,goods_type,publish_time,goods.goods_status,reason "
."from goods inner join user on user.user_id = goods.seller_id where 1 = 1 "
._rangeParam('seller_id',$sellerId)
._likeParam('goods_name',$goodsName)
._likeParam('user.user_name',$sellerName)
._rangeParam('goods.goods_status',$goodsStatus)
."order by goods_status,publish_time;";
//模糊查询的参数处理
$sellerName = '%'.$sellerName.'%';
$goodsName = '%'.$goodsName.'%';
$p = $conn->prepare($sql);
$p->bind_param('issi',$sellerId,$goodsName,$sellerName,$goodsStatus);
$p->bind_result($goodsId,$goodsName,$goodsPrice,$sellerId,$sellerName,$goodsType,$publishTime,$status,$reason);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName,
      "sellerId"=>$sellerId,
      "sellerName"=>$sellerName,
      "price"=>$goodsPrice,
      "publishTime"=>$publishTime,
      "goodsType"=>$goodsType,
      "goodsStatus"=>$status
    );
    $now = getUnixTimestamp();
    if($status == 7 && $now-$changeTime > 300000){
      $t['goodsStatus'] = 3;
    }else if($status == 7 && $now-$changeTime <= 300000){
      $t['goodsStatus'] = 4;
    }
    if($status == 2){
      $t["reason"] = $reason;
    }
    array_push($arr,$t);
  }
  //分页情况
  $page = array(
    "total"=>count($arr),
    "pageNum"=>$pageNum,
    "pageSize"=>$pageSize
  );
  $res = array(
    "code"=>1000,
    "data"=>array_slice($arr,($pageNum-1) * $pageSize,$pageSize),
    "page"=>$page
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