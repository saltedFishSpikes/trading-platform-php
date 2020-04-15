<?php
/**
 * 获取聊天记录
 */

include_once '../header.php';
include_once '../verifyToken.php';

$from = $user['userId'];
$to = _post('userId');

$fileUrl = "";
if(file_exists('chattingRecords/'.$from.'-'.$to.'.txt')){
  $fileUrl = 'chattingRecords/'.$from.'-'.$to.'.txt';
}else if (file_exists('chattingRecords/'.$to.'-'.$from.'.txt')) {
  $fileUrl = 'chattingRecords/'.$to.'-'.$from.'.txt';
}else {
  $res = array(
    "code"=>1000,
    "data"=>array()
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
  die();
}
$file = fopen($fileUrl,'r');
$content = fread($file,filesize($fileUrl));
$recordsArr = explode("\r\n",$content);
array_pop($recordsArr);
$chatList = array();
foreach($recordsArr as $item){
  $temp = json_decode($item,JSON_UNESCAPED_UNICODE);
  $records = array(
    "chatTime"=>$temp['chatTime'],
    "userFromId"=>$temp['userFromId'],
    "userToId"=>$temp['userToId'],
    "content"=>trim($temp['content'],'"'),
    "type"=>$temp['userFromId'] == $from ? 0 : 1
  );
  array_push($chatList,$records);
}
$res = array(
  "code"=>1000,
  "data"=>$chatList
);
echo json_encode($res,JSON_UNESCAPED_UNICODE);
fclose($file);