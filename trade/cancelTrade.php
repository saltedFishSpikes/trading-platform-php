<?php
/**
 * 取消交易
 */

include_once '../header.php';
include_once '../verifyToken.php';


$tradeId = _post('tradeId');
$goodsId = _post('goodsId');


$sql = "update trade set trade_status = 3,change_time = ? where trade_id = ?;";
$p = $conn->prepare($sql);
$changeTime = getUnixTimestamp();
$p->bind_param('is',$changeTime,$tradeId);
if($p->execute()){
  $p->free_result();
  $p->close();
  $sql2 = "update goods set goods_status = 3,change_time = ? where goods_id = ?;";
  $p2 = $conn->prepare($sql2);
  $changeTime2 = getUnixTimestamp();
  $p2->bind_param('ii',$changeTime2,$goodsId);
  if($p2->execute()){
    $res = array(
      "code"=>1000,
      "data"=>"撤销成功"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
  $p2->free_result();
  $p2->close();
}else{
  $res = array(
    "code"=>1001,
    "data"=>"撤销失败"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  $p->free_result();
  $p->close();
}

$conn->close();