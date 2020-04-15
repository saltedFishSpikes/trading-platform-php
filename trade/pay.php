<?php
/**
 * 模拟支付
 */
include_once '../header.php';
include_once '../verifyToken.php';

$tradeId = _post("tradeId");
//查询交易
$sql = "select trade_id,goods_id,change_time,trade_status from trade where trade_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('s',$tradeId);
$p->bind_result($tradeId,$goodsId,$changeTime,$tradeStatus);
if($p->execute()){
  $p->fetch();
  if(!empty($tradeId)){
    $now = getUnixTimestamp();
    $p->free_result();
    $p->close();
    if($tradeStatus == 1 && $now-$changeTime < 300000){
      $sql2 = "update trade set trade_status = 2,change_time = ? where trade_id = ?;";
      $p2 = $conn->prepare($sql2);
      $p2->bind_param('is',$now,$tradeId);
      if($p2->execute()){
        $p2->free_result();
        $p2->close();
        $sql3 = "update goods set goods_status = 4,change_time = ? where goods_id = ?;";
        $p3 = $conn->prepare($sql3);
        $p3->bind_param('ii',$now,$goodsId);
        if($p3->execute()){
          $res = array(
            "code"=>1000,
            "data"=>"支付成功"
          );
          echo json_encode($res,JSON_UNESCAPED_UNICODE);
        }
        $p3->free_result();
        $p3->close();
      }else{
        $res = array(
          "code"=>1001,
          "data"=>"支付失败"
        );
        $p2->free_result();
        $p2->close();
      }
    }else if($tradeStatus == 2){
      $res = array(
        "code"=>1004,
        "data"=>"该交易已经支付成功"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }else if($tradeStatus == 4){
      $res = array(
        "code"=>1004,
        "data"=>"该交易已经完成"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }else if($tradeStatus == 3){
      $res = array(
        "code"=>1004,
        "data"=>"该交易被用户取消"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }else if($tradeStatus == 1 && $now-$changeTime > 300000){
      $res = array(
        "code"=>1004,
        "data"=>"由于超时未支付，该交易被取消"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }
  }else{
    $res = array(
      "code"=>1001,
      "data"=>"该交易不存在"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
}