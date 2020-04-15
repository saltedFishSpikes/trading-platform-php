<?php
/**
 * 下架
 */
include_once '../header.php';
include_once '../verifyToken.php';

$goodsId = _post('goodsId');
$sql = "update goods set goods_status = 5 where goods_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('i',$goodsId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"下架成功"
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