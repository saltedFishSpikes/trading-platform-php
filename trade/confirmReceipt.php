<?php
/**
 * 确认收货
 */

include_once '../header.php';
include_once '../verifyToken.php';

$tradeId = _post('tradeId');

$sql = "update trade set trade_status = 4,change_time = ? where trade_id = ?;";
$p = $conn->prepare($sql);
$changeTime = getUnixTimestamp();
$p->bind_param('is',$changeTime,$tradeId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"确认收货成功"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else{
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}
$p->free_result();
$p->close();
$conn->close();