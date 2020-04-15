<?php
/**
 * 生成订单
 */
include_once '../header.php';
include_once '../verifyToken.php';


$goodsId = _post('goodsId');
//查询商品
$sql = "select goods_id,goods_name,goods_price,seller_id,goods_status,change_time from goods where goods_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('i',$goodsId);
$p->bind_result($goodsId,$goodsName,$goodsPrice,$sellerId,$goodsStatus,$changeTime);
if($p->execute()){
  $p->fetch();
  if(!empty($goodsId)){
    $p->free_result();
    $p->close();
    $goods = array(
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName,
      "goodsPrice"=>$goodsPrice,
      "sellerId"=>$sellerId,
      "goodsStatus"=>$goodsStatus,
      "changeTime"=>$changeTime
    );

    //生成订单
    $sql2 = "insert into trade (trade_id,goods_id,seller_id,buyer_id,price,change_time,trade_status) ".
      "values (?,?,?,?,?,?,?);";
    $tradeId = date("YmdHis").$goods['goodsId'];
    $p2 = $conn->prepare($sql2);
    $buyerId = $user['userId'];
    $changeTime = getUnixTimestamp();
    $tradeStatus = (int)1;
    $p2->bind_param('siiidii',$tradeId,$goods['goodsId'],$goods['sellerId'],$buyerId,$goods['goodsPrice'],$changeTime,$tradeStatus);
    if($p2->execute()){
      $p2->free_result();
      $p2->close();
      //改变商品状态
      $changeTime = getUnixTimestamp();
      $sql3 = "update goods set goods_status = 7, change_time = ? where goods_id = ?;";
      $p3 = $conn->prepare($sql3);
      $p3->bind_param('ii',$changeTime,$goods['goodsId']);
      if($p3->execute()){
        $res = array(
          "code"=>1000,
          "data"=>"订单生成成功"
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
      }else{
        $res = array(
          "code"=>1001,
          "data"=>"订单创建失败"
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
      }
      $p3->free_result();
      $p3->close();
    }else{
      $p2->free_result();
      $p2->close();
      $res = array(
        "code"=>1001,
        "data"=>"生成订单失败"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }
  }else{
    $p->free_result();
    $p->close();
    $res = array(
      "code"=>1001,
      "data"=>"未找到该商品"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
  
}else{
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  $p->free_result();
  $p->close();
}


$conn->close();