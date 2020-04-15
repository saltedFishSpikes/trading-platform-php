<?php
/**
 * 注册
 */
include_once '../header.php';

$userName = _post('userName');
$password = _post('password');
$address = _post("address");

// 查询已有用户
$sql = "select user_id from user where user_name = ?;";
$p = $conn->prepare($sql);
$p->bind_param('s',$userName);
$p->bind_result($userId);
if($p->execute()){
  $p->fetch();
  if(!empty($userId)){
    $res = array(
      "code"=>1001,
      "data"=>"该用户名已经注册"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    $p->free_result();
    $p->close();
    $conn->close();
  }else{
    $p->free_result();
    $p->close();
     // 注册新用户
    $sql = "insert into user (user_name,password,register_time,address,user_status) values (?,?,?,?,?);";
    $registerTime = getUnixTimestamp();
    $userStatus = 1;
    $password = substr(md5($password),8,16);
    $p = $conn->prepare($sql);
    $p->bind_param('ssisi',$userName,$password,$registerTime,$address,$userStatus);
    if($p->execute()){
      $res = array(
        "code"=>1000,
        "data"=>"注册成功"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }else{
      $res = array(
        "code"=>1001,
        "data"=>"注册失败"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
      $p->free_result();
      $p->close();
      $conn->close();
    }
  }
}else {
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  $p->free_result();
  $p->close();
  $conn->close();
}