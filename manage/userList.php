<?php
/**
 * 用户列表（管理）
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
$userName = _post("userName");
$status = _post("status");
$successNum = _post("successNum");
$pageSize = _post("pageSize");
$pageNum = _post("pageNum");

//范围字符串的参数处理
$successArr = _stringParam($successNum);

$sql = "select u.user_id,u.user_name,u.success_num,u.register_time,u.address,u.user_status "
."from (select us.user_id,us.user_name,us.address,us.sell_num + ifnull(b.buy_num,0) as success_num,us.register_time,us.user_status from "
."(select user.user_id,user.user_name,user.address,ifnull(s.sell_num,0) as sell_num,user.register_time,user.user_status from user left join "
."(select seller_id,count(*) as sell_num from trade where trade_status = 4 group by seller_id) as s on user.user_id = s.seller_id) as us "
."left join (select buyer_id,count(*) as buy_num from trade where trade_status = 4 group by buyer_id) as b on us.user_id = b.buyer_id "
.")as u where user_status <> 3 "
._likeParam('u.user_name',$userName)
._rangeParam('u.user_status',$status)
._betweenParam('u.success_num',$successArr[0],$successArr[1])
."order by u.user_status;";
//模糊查询的参数处理
$userName = '%'.$userName.'%';
$p = $conn->prepare($sql);
$p->bind_param('siii',$userName,$status,$successArr[0],$successArr[1]);
$p->bind_result($userId,$userName,$successNum,$registerTime,$address,$status);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "userId"=>$userId,
      "userName"=>$userName,
      "successNum"=>$successNum,
      "registerTime"=>$registerTime,
      "status"=>$status,
      "address"=>$address
    );
    array_push($arr,$t);
  }
  //分页情况
  $page = array(
    "total"=>count($arr),
    "pageNum"=>$pageNum,
    "pageSize"=>$pageSize
  );
  $res = array(
    "code"=>1000,
    "data"=>array_slice($arr,($pageNum-1) * $pageSize,$pageSize),
    "page"=>$page
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