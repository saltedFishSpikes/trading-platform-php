<?php
/**
 * 修改用户信息
 */

include_once '../header.php';
include_once '../verifyToken.php';

$userId = $user['userId'];
$address = _post("address");
$userImg = _post("userImg");
$userName = _post("userName");
$password = _post("password");
$oldPassword = _post("oldPassword");

if(!empty($userName)){
  $sql = "select user_id from user where user_name = ?";
  $p = $conn->prepare($sql);
  $p->bind_param('s',$userName);
  $p->bind_result($id);
  if($p->execute()){
    $p->fetch();
    if(!empty($id)){
      $res = array(
        "code"=>1001,
        "data"=>"该用户名已经存在"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
      $p->free_result();
      $p->close();
      $conn->close();
      die();
    }else{
      $p->free_result();
      $p->close();
      $sql2 = "update user set user_name = ? where user_id = ?;";
      $p2 = $conn->prepare($sql2);
      $p2->bind_param('si',$userName,$userId);
      if($p2->execute()){
        $payload = array(
          'iss'=>'admin',
          'sub'=>$userName,
          'iat'=>time(),
          'exp'=>time()+3600000,
          'nbf'=>time()+0,
          'name'=>$userName,
          'userId'=>$user['userId'],
          'type'=>$user['type'],
          'userImg'=>$user['userImg']
        );
        $res = array(
          "code"=>1000,
          "data"=>"修改成功",
          "token"=>$Token->createToken($payload)
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
      }else {
        $res = array(
          "code"=>1001,
          "data"=>"未知错误"
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
      }
      $p2->free_result();
      $p2->close();
      $conn->close();
      die();
    }
  }
}else{
  $sql2 = '';
  $p2 = null;
  if(!empty($userImg)){
    //获取不包括路径的图片名称
    $index = strripos($userImg,'/');
    $userImg = substr($userImg,$index+1);

    $sql2 = "update user set user_img = ? where user_id = ?";
    $p2 = $conn->prepare($sql2);
    $p2->bind_param('si',$userImg,$userId);
  }
  if(!empty($password) && !empty($oldPassword)){
    // 验证旧密码
    $oldPassword = substr(md5($oldPassword),8,16);
    $sql3 = "select user_name from user where user_id = ? and password = ?;";
    $p3 = $conn->prepare($sql3);
    $p3->bind_param('is',$userId,$oldPassword);
    $p3->bind_result($userName);
    if($p3->execute()){
      $p3->fetch();
      if(empty($userName)){
        $res = array(
          "code"=>1001,
          "data"=>"旧密码不正确"
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
        $p3->free_result();
        $p3->close();
        $conn->close();
        die();
      }else{
        $p3->free_result();
        $p3->close();
      }
    }else{
      $res = array(
        "code"=>1001,
        "data"=>"未知错误"
      );
      echo json_encode($res,JSON_UNESCAPED_UNICODE);
      $p3->free_result();
      $p3->close();
      $conn->close();
      die();
    }
    // 修改密码
    $password = substr(md5($password),8,16);
    $sql2 = "update user set password = ? where user_id = ?";
    $p2 = $conn->prepare($sql2);
    $p2->bind_param('si',$password,$userId);
  }
  if(!empty($address)){
    $sql2 = "update user set address = ? where user_id = ?";
    $p2 = $conn->prepare($sql2);
    $p2->bind_param('si',$address,$userId);
  }
  if($p2->execute()){
    $payload = array(
      'iss'=>'admin',
      'sub'=>$userName,
      'iat'=>time(),
      'exp'=>time()+3600000,
      'nbf'=>time()+0,
      'name'=>$user['name'],
      'userId'=>$user['userId'],
      'type'=>$user['type'],
      'userImg'=>empty($userImg)?$user['userImg']:($baseUrl.'/image/'.$userId.'/'.$userImg)
    );
    $res = array(
      "code"=>1000,
      "data"=>"修改成功",
      "token"=>$Token->createToken($payload)
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }else {
    $res = array(
      "code"=>1001,
      "data"=>"未知错误"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
  $p2->free_result();
  $p2->close();
  $conn->close();
}
