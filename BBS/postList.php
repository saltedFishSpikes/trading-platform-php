<?php
/**
 * 帖子列表
 */
include_once '../header.php';

$pageNum = _post("pageNum");
$pageSize = _post("pageSize");

$sql = "select p.post_id,p.title,p.publish_time,p.poster_id,p.poster_name,p.poster_img,ifnull(r.commit_num,0) as commit_num,ifnull(t.reply_num,0) as reply_num from "
."(select post.post_id as post_id,post.title,post.publish_time,post.poster_id,user.user_name as poster_name,user.user_img as poster_img from "
."post inner join user on post.poster_id = user.user_id "
.") as p "
."left join (select post_id,count(*) as reply_num from reply group by post_id) as t on p.post_id = t.post_id "
."left join (select post_id,count(*) as commit_num from reply right join commit on reply.reply_id = commit.reply_id "
."group by post_id "
.") as r on p.post_id = r.post_id order by p.publish_time desc;";

$p = $conn->prepare($sql);
$p->bind_result($postId,$title,$publishTime,$posterId,$posterName,$posterImg,$commitNum,$replyNum);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $t = array(
      "postId"=>$postId,
      "postTitle"=>$title,
      "posterId"=>$posterId,
      "posterName"=>$posterName,
      "postTime"=>$publishTime,
      "replyNum"=>$replyNum+$commitNum
    );
    $t['posterImg'] = (!empty($posterImg)?($baseUrl.'/image/'.$posterId.'/'.$posterImg):"");
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