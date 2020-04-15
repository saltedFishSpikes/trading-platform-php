<?php
/**
 * 评论帖子
 */
include_once '../header.php';
include_once '../verifyToken.php';

$postId = _post("postId");
$replyContent = _post("content");
$replyerId = $user['userId'];
$publishTime = getUnixTimestamp();
$sql = "insert into reply (post_id,replyer_id,content,publish_time) "
."values (?,?,?,?)";

$p = $conn->prepare($sql);
$p->bind_param('iisi',$postId,$replyerId,$replyContent,$publishTime);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"评论成功"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else {
  $res = array(
    "code"=>1000,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}

//关闭连接
$p->free_result();
$p->close();
$conn->close();