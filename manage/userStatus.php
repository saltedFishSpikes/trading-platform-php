<?php
/**
 * 用户状态
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

$userId = _post("userId");
$userStatus = _post("status");

$sql = "update user set user_status = ? where user_id = ?;";
$p = $conn->prepare($sql);
$p->bind_param('ii',$userStatus,$userId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"修改成功"
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