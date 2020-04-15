<?php
/**
 * 回复评论
 */
include_once '../header.php';
include_once '../verifyToken.php';

$replyId = _post("replyId");//评论
$commiterId = $user['userId'];//回复人
$commitToId = _post("commitToId");//被回复人
$content = _post("content");
$publishTime = getUnixTimestamp();
$sql = "insert into commit (reply_id,publish_time,content,commiter_id,commit_to_id) "
."values (?,?,?,?,?)";

if($commitToId == ""){
  $commitToId = $commiterId;
}
$p = $conn->prepare($sql);
$p->bind_param('iisii',$replyId,$publishTime,$content,$commiterId,$commitToId);
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"评论成功"
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