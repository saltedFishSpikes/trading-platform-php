<?php
/**
 * 用户更新商品
 */
include_once '../header.php';
include_once '../verifyToken.php';

$goodsId = (int)_post("goodsId");
$goodsName = _post("goodsName");
$goodsImg = _post("goodsImg");
$goodsType = (int)_post("goodsType");
$goodsDesc = _post("goodsDesc");
$changeTime = getUnixTimestamp();

$sql = "update goods set goods_name = ?,goods_img = ?,".
"goods_type = ?,goods_desc = ?,publish_time = ?,goods_status = 1 where goods_id = ?;";

$img = [];
if(!empty($goodsImg)){
  $img = explode(',',$goodsImg);
  for($i = 0; $i < count($img); $i++){
    $img[$i] = explode('/',substr($img[$i],43))[1];
  }
}
$img = implode(',',$img);
$p = $conn->prepare($sql);
$p->bind_param('ssisii',$goodsName,$img,$goodsType,$goodsDesc,$changeTime,$goodsId);
//返回
if($p->execute()){
  $res = array(
    "code"=>1000,
    "data"=>"提交成功"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else{
  $res = array(
    "code"=>1001,
    "data"=>"提交失败"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}

//关闭连接
$p->free_result();
$p->close();
$conn->close();