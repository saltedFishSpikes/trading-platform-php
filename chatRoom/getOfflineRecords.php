<?php
/**
 * 获取
 */
include_once '../header.php';
include_once '../verifyToken.php';
$sql = "select user_id,offline_time from user where user_id = ?";
$p = $conn->prepare($sql);
$p->bind_param('i',$user['userId']);
$p->bind_result($userId,$offlineTime);
if($p->execute()){
  $p->fetch();
  $path = 'chattingRecords/';
  if(!file_exists($path)){
    if(!is_dir($path)){
      mkdir($path,777);
    }
    $res = array(
      "code"=>1009,
      "data"=>"没有聊天记录"
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }else{
    $handle = opendir($path);
    $fileItem = [];
    if($handle) {
      while(($file = readdir($handle)) !== false) {
        if( $file != ".." && $file != "." && strpos($file,(string)$userId) != false){
          if(filemtime($path.$file)*1000 >= $offlineTime){
            $fp = fopen($path.$file,"r");
            $str = fgets($fp);
            $str2 = substr($str,0,strlen($str)-1);
            $data = json_decode($str2,true);
            if($data['userFromId'] == $user['userId']){
              $u = array(
                'userId'=>$data['userToId'],
                'userName'=>$data['userToName']
              );
              array_push($fileItem,$u);
              fclose($fp);
            }else if($data['userToId'] == $user['userId']){
              $u = array(
                'userId'=>$data['userFromId'],
                'userName'=>$data['userFromName']
              );
              array_push($fileItem,$u);
              fclose($fp);
            }else{
              fclose($fp);
            }
          }
        }
      }
    }
    closedir($handle);
    $res = array(
      "code"=>1000,
      "data"=>$fileItem
    );
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
  }
}else{
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}
$p->free_result();
$p->close();
$conn->close();