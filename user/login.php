<?php
/**
 * 登录
 */
include_once '../header.php';

$userName = _post('userName');
$password = _post('password');

$sql = "select user_id,user_status,user_img from user where user_name = ? and password = ?;";
$p = $conn->prepare($sql);
$password = substr(md5($password),8,16);
$p->bind_param('ss',$userName,$password);
$p->bind_result($userId,$userStatus,$userImg);
if($p->execute()){
  $p->fetch();
  if(!empty($userId)){
    if($userStatus == 2){
      $res = array(
        "code"=>1001,
        "data"=>"该用户已经被禁用"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }else{
      $payload = array(
        'iss'=>'admin',
        'sub'=>$userName,
        'iat'=>time(),
        'exp'=>time()+3600000,
        'nbf'=>time()+0,
        'name'=>$userName,
        'userId'=>$userId,
        'type'=>$userStatus,
        'userImg'=>(!empty($userImg)?($baseUrl.'/image/'.$userId.'/'.$userImg):"")
      );
      $res = array(
        "code"=>1000,
        "data"=>"登录成功",
        "token"=>$Token->createToken($payload)
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }
  }else{
    $res = array(
      "code"=>1001,
      "data"=>"未查到此用户或密码错误，请检查输入"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
}
$p->free_result();
$p->close();
$conn->close();