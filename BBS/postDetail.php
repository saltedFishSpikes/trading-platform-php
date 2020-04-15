<?php
/**
 * 帖子详情
 */

include_once '../header.php';

$postId = _post("postId");
//postId为空时退出
if(empty($postId)){
  $err = array(
    "code"=>1001,
    "data"=>"缺少postId"
  );
  echo json_encode($err,JSON_UNESCAPED_UNICODE);
  die;
}

$sql = "select post.post_id,post.poster_id,post.title,post.content,user.user_name,user.user_img from post "
."inner join user on user.user_id = post.poster_id "
."where post_id = ?;";

$p = $conn->prepare($sql);
$p->bind_param("i",$postId);
$p->bind_result($postId,$posterId,$title,$content,$posterName,$posterImg);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $arr['postId'] = $postId;
    $arr['postTitle'] = $title;
    $arr['postContent'] = $content;
    $arr['posterName'] = $posterName;
    $arr['posterId'] = $posterId;
    $arr['posterImg'] = (!empty($posterImg)?($baseUrl.'/image/'.$posterId.'/'.$posterImg):"");
  }
  $res = array(
    "code"=>1000,
    "data"=>$arr
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
