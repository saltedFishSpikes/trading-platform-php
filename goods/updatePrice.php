<?php
/**
 * 用户修改商品价格
 */
include_once '../header.php';
include_once '../verifyToken.php';

$goodsId = _post("goodsId");
$goodsPrice = floatval(_post("goodsPrice"));
$changeTime = getUnixTimestamp();

$sql = "update goods set goods_price = ?,publish_time = ? where goods_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('dii',$goodsPrice,$changeTime,$goodsId);

if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"修改成功"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else {
  $res = array(
    "code"=>1001,
    "data"=>"提交失败"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}
//关闭连接
$p->free_result();
$p->close();
$conn->close();