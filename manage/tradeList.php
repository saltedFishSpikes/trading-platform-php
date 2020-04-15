<?php
/**
 * 交易列表（管理）
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

$tradeId = _post("tradeId");
$sellerName = _post("sellerName");
$sellerId = _post("sellerId");
$buyerName = _post("buyerName");
$buyerId = _post("buyerId");
$goodsName = _post("goodName");
$tradeStatus = _post("tradeStatus");
$pageSize = _post("pageSize");
$pageNum = _post("pageNum");

$sql = "select t.trade_id,t.goods_id,goods.goods_name,t.seller_id,user.user_name as seller_name,"
."t.buyer_id,t.buyer_name,t.price,t.change_time,t.trade_status from "
."(select trade_id,goods_id,seller_id,buyer_id,user.user_name as buyer_name,price,change_time,trade.trade_status "
."from trade inner join user on trade.buyer_id = user.user_id) as t "
."inner join user on user.user_id = t.seller_id "
."inner join goods on goods.goods_id = t.goods_id "
."where 1 = 1 "
._rangeParam('t.trade_id',$tradeId)
._likeParam('t.buyer_name',$buyerName)
._rangeParam('t.buyer_id',$buyerId)
._likeParam('user.user_name',$sellerName)
._rangeParam('t.seller_id',$sellerId)
._likeParam('goods.goods_name',$goodsName)
._rangeParam('t.trade_status',$tradeStatus)
."order by t.trade_status,t.change_time";

//模糊查询的参数处理
$sellerName = '%'.$sellerName.'%';
$buyerName = '%'.$buyerName.'%';
$goodsName = '%'.$goodsName.'%';

if($tradeStatus == 5){
  $tradeStatus = 1;
}

$p = $conn->prepare($sql);
$p->bind_param('isisisi',$tradeId,$buyerName,$buyerId,$sellerName,$sellerId,$goodsName,$tradeStatus);
$p->bind_result($tradeId,$goodsId,$goodsName,$sellerId,$sellerName,$buyerId,$buyerName,$price,$changeTime,$status);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "tradeId"=>$tradeId,
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName,
      "sellerId"=>$sellerId,
      "sellerName"=>$sellerName,
      "buyerId"=>$buyerId,
      "buyerName"=>$buyerName,
      "price"=>$price,
      "changeTime"=>$changeTime,
      "tradeStatus"=>$status
    );
    $now = getUnixTimestamp();
    if($status == 1 && $now-$changeTime > 300000){
      $t['tradeStatus'] = 5;
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