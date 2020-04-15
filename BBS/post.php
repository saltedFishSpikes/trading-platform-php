<?php
/**
 * 发帖
 */
include_once '../header.php';
include_once '../verifyToken.php';

$postTitle = _post('postTitle');
$postContent = _post('postContent');

$sql = "insert into post (poster_id,title,content,publish_time) values (?,?,?,?);";

$publishTime = getUnixTimestamp();
$posterId = $user['userId'];

$p = $conn->prepare($sql);
$p->bind_param('issi',$posterId,$postTitle,$postContent,$publishTime);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"发帖成功"
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