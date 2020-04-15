<?php
/**
 * token校验
 */

$userToken = _post('token');
$user = null;
if(empty($userToken) || !$Token->verifyToken($userToken)){
  $res = array("code"=>1001,"data"=>"请先登录");
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  die;
}else {
  $user = $Token->verifyToken($userToken);
}