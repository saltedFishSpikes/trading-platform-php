<?php

/**
 * 用户详情
 */
include_once '../header.php';

$userId = _post('userId');
if(empty($userId)){
  include_once '../verifyToken.php';
  $userId = $user['userId'];
}
$sql = "select user_id,user_name,register_time,address,ifnull(s.sell_num,0),ifnull(b.buy_num,0),user_status,user_img "
  ."from user left join ".
  "(select seller_id,count(*) as sell_num from trade where trade_status = 4 and seller_id = ?) as s ".
  "on user.user_id = s.seller_id left join ".
  "(select buyer_id,count(*) as buy_num from trade where trade_status = 4 and buyer_id = ?) as b ".
  "on user.user_id = b.buyer_id where user_id = ?";
$p = $conn->prepare($sql);
$p->bind_param('iii',$userId,$userId,$userId);
$p->bind_result($userId,$userName,$registerTime,$address,$sellNum,$buyNum,$status,$userImg);
if($p->execute()){
  //查询结果
  
  $p->fetch();
  if(empty($userName)){
    $res = array(
      "code"=>1001,
      "data"=>"没有找到该用户"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }else{
    $arr = array(
      "userId"=>$userId,
      "userName"=>$userName,
      "registerTime"=>$registerTime,
      "address"=>$address,
      "successNum"=>$sellNum + $buyNum,
      "status"=>$status,
      "userImg"=>(!empty($userImg)?($baseUrl.'/image/'.$userId.'/'.$userImg):"")
    );
    $res = array(
      "code"=>1000,
      "data"=>$arr
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
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