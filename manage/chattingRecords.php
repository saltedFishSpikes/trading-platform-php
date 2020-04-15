<?php
/**
 * 沟通列表（管理）
 */
include_once '../header.php';
$sellerId = _post("sellerId");
$buyerId = _post("buyerId");

$fileUrl = "";
if(file_exists('../chatRoom/chattingRecords/'.$buyerId.'-'.$sellerId.'.txt')){
  $fileUrl = '../chatRoom/chattingRecords/'.$buyerId.'-'.$sellerId.'.txt';
}else if (file_exists('../chatRoom/chattingRecords/'.$sellerId.'-'.$buyerId.'.txt')) {
  $fileUrl = '../chatRoom/chattingRecords/'.$sellerId.'-'.$buyerId.'.txt';
}else {
  $res = array(
    "code"=>1001,
    "data"=>"交易双方未进行沟通"
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
    "type"=>$temp['userFromId'] == $sellerId ? 1 : 0
  );
  array_push($chatList,$records);
}
$res = array(
  "code"=>1000,
  "data"=>$chatList
);
echo json_encode($res,JSON_UNESCAPED_UNICODE);
fclose($file);