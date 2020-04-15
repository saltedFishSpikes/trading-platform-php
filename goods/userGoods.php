<?php
/**
 * 用户商品，不传userId则查询当前用户
 */
include_once '../header.php';


$userId = _post('userId');
if(empty($userId)){
  include_once '../verifyToken.php';
  $userId = $user['userId'];
}
$pageNum = _post('pageNum');
$pageSize = _post('pageSize');
$sql = "select goods_id,goods_name,goods_price,goods_type,publish_time,change_time,goods_status,reason from goods where seller_id = ?";
//预处理查询
$p = $conn->prepare($sql);
$p->bind_param('i',$userId);
$p->bind_result($goodsId,$goodsName,$goodsPrice,$goodsType,$publishTime,$changeTime,$goodsStatus,$reason);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName,
      "goodsType"=>$goodsType,
      "goodsStatus"=>$goodsStatus,
      "publishTime"=>$publishTime,
      "goodsPrice"=>$goodsPrice
    );
    $now = getUnixTimestamp();
    if($goodsStatus == 7 && $now-$changeTime > 300000){
      $t['goodsStatus'] = 3;
    }
    if($goodsStatus == 2 || $goodsStatus == 6){
      $t['reason'] = $reason;
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