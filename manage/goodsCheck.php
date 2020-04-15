<?php
/**
 * 商品审核
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

$goodsStatus = _post("status");
$reason = _post("reason");
$goodsId = _post("goodsId");

$sql = "update goods set reason = ?,goods_status = ? where goods_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('sii',$reason,$goodsStatus,$goodsId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"操作成功"
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