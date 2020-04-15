<?php

/**
 * 商品详情
 */
include_once '../header.php';

$goodsId = _post('goodsId');
//goodsId为空时退出
if(empty($goodsId)){
  $err = array(
    "code"=>1001,
    "data"=>"缺少goodsId"
  );
  echo json_encode($err,JSON_UNESCAPED_UNICODE);
  die;
}

$sql = "select g.goods_name,g.goods_price,g.goods_status,g.goods_img,g.seller_id,g.change_time,g.goods_type,g.goods_desc,u.user_name,t.buyer_name,t.trade_id,t.price "
  ."from goods as g "
  ."inner join user as u on u.user_id = g.seller_id "
  ."left join "
  ."(select trade.goods_id as goods_id,trade.trade_id as trade_id,user.user_name as buyer_name,trade.price as price from user,trade where user.user_id = trade.buyer_id) "
  ."as t on t.goods_id = g.goods_id "
  ."where g.goods_id = ?";

$p = $conn->prepare($sql);
$p->bind_param('i',$goodsId);
$p->bind_result($goodsName,$goodsPrice,$goodsStatus,$goodsImg,$sellerId,$changeTime,$goodsType,$goodsDesc,$sellerName,$buyer,$tradeId,$price);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $arr['goodsId'] = $goodsId;
    $arr['goodsName'] = $goodsName;
    $arr['goodsStatus'] = $goodsStatus;
    $arr['sellerId'] = $sellerId;
    $arr['sellerName'] = $sellerName;
    $arr['goodsType'] = $goodsType;
    $arr['goodsDesc'] = $goodsDesc;
    $now = getUnixTimestamp();
    if($goodsStatus == 7 && $now-$changeTime > 300000){
      $arr['goodsStatus'] = 3;
    }
    $img = [];
    if(!empty($goodsImg)){
      $img = explode(',',$goodsImg);
      for($i = 0; $i < count($img); $i++){
        $imgObj = array(
          "id"=>$i,
          "url"=>$baseUrl.'/image/'.$sellerId.'/'.$img[$i]
        );
        $img[$i] = $imgObj;
      }
    }
    $arr['goodsImg'] = $img;
    if($goodsStatus == 4){
      $arr['goodsPrice'] = $price;
      $arr['buyer'] = $buyer;
      $arr['tradeId'] = $tradeId;
    }else{
      $arr['goodsPrice'] = $goodsPrice;
    }
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