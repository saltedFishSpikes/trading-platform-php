<?php
/**
 * 用户交易，不传userId则查询当前用户
 */
include_once '../header.php';
include_once '../verifyToken.php';

$userId = _post('userId');
if(empty($userId)){
  $userId = $user['userId'];
}
$pageNum = _post('pageNum');
$pageSize = _post('pageSize');
$sql = "select distinct t.trade_id,t.goods_id,g.goods_name,t.seller_id,t.seller_name,t.buyer_id,u.user_name,t.price,t.change_time,t.trade_status from "
."(select trade.trade_id,trade.goods_id,trade.seller_id as seller_id,trade.buyer_id,user.user_name as seller_name,trade.price,trade.change_time,trade.trade_status "
."from trade inner join user on user.user_id = trade.seller_id) "
."as t inner join user as u on t.buyer_id = u.user_id "
."inner join goods as g on g.goods_id = t.goods_id "
."where t.seller_id = ? or t.buyer_id = ? order by t.change_time desc";
//预处理查询
$p = $conn->prepare($sql);
$p->bind_param('ii',$userId,$userId);
$p->bind_result($tradeId,$goodsId,$goodsName,$sellerId,$sellerName,$buyerId,$buyerName,$price,$changeTime,$tradeStatus);
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
      "tradePrice"=>$price,
      "changeTime"=>$changeTime,
      "tradeStatus"=>$tradeStatus
    );
    if($userId === $buyerId){
      $t['role'] = 0;
    }else if($userId === $sellerId){
      $t['role'] = 1;
    }
    $now = getUnixTimestamp();
    if($tradeStatus == 1 && $now-$changeTime > 300000){
      $t['tradeStatus'] = 5;
    }
    array_push($arr,$t);
  }
  //分页情况
  $page = array(
    "total"=>count($arr),
    "pageNum"=>(int)$pageNum,
    "pageSize"=>(int)$pageSize
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