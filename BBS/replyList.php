<?php
/**
 * 评论/回复列表
 */

include_once '../header.php';

$postId = _post("postId");
$pageNum = _post("pageNum");
$pageSize = _post("pageSize");
//postId为空时退出
if(empty($postId)){
  $err = array(
    "code"=>1001,
    "data"=>"缺少postId"
  );
  echo json_encode($err,JSON_UNESCAPED_UNICODE);
  die;
}

$sql = "select commit_id,commit_time,commit_content,commiter_id,commit_to_id,reply_id,post_id,replyer_id,reply_content,reply_time,replyer_name,commiter_name,user.user_name as commit_to_name from "
."(select a.*,user.user_name as commiter_name from "
."((select commit_id,commit.publish_time as commit_time,commit.content as commit_content,commiter_id,commit_to_id,reply.reply_id as reply_id,post_id,replyer_id,reply.content as reply_content,reply.publish_time as reply_time,user.user_name as replyer_name "
."from reply left join commit on reply.reply_id = commit.reply_id left join user on user.user_id = replyer_id) "
."union"
."(select commit_id,commit.publish_time as commit_time,commit.content as commit_content,commiter_id,commit_to_id,reply.reply_id as reply_id,post_id,replyer_id,reply.content as reply_content,reply.publish_time as reply_time,user.user_name as replyer_name "
."from reply right join commit on reply.reply_id = commit.reply_id left join user on user.user_id = replyer_id)) as a "
."left join user on user.user_id = a.commiter_id) as b "
."left join user on user.user_id = b.commit_to_id where post_id = ? "
."order by reply_id,reply_time,commit_time";



$p = $conn->prepare($sql);
$p->bind_param("i",$postId);
$p->bind_result($commitId,$commitTime,$commitContent,$commiterId,$commiToId,$replyId,$postId,$replyerId,$replyContent,$replyTime,$replyerName,$commiterName,$commitToName);
if($p->execute()){
  //查询结果
  $arr = array();
  $x = 0;
  while($p->fetch()){
    //第一行
    if(empty($arr)){
      array_push($arr,array("replyId"=>$replyId));
    }
    if($arr[$x]['replyId'] != $replyId){
      //不属于同一个评论
      $x = $x + 1;
      array_push($arr,array("replyId"=>$replyId));
    }

      $arr[$x]["replyId"] = $replyId;
      $arr[$x]["replyContent"] = $replyContent;
      $arr[$x]["replyerName"] = $replyerName;
      $arr[$x]["replyerId"] = $replyerId;
      $arr[$x]["replyTime"] = $replyTime;
      if(empty($arr[$x]["commit"])){
        //添加回复数组
        $arr[$x]["commit"] = array();
      }
    
    if($commitId != null){
      //回复
      if(empty($arr[$x]["commit"])){
        //添加回复数组
        $arr[$x]["commit"] = array();
      }
      $t = array(
        "replyId"=>$replyId,
        "commitId"=>$commitId,
        "commiterName"=>$commiterName,
        "commiterId"=>$commiterId,
        "commitContent"=>$commitContent,
        "commitTime"=>$commitTime
      );
      if($commiterId != $commiToId) {
        $t["commitOtherId"] = $commiToId;
        $t["commitOtherName"] = $commitToName;
      }
      array_push($arr[$x]["commit"],$t);
    }
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