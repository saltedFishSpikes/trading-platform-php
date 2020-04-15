<?php
/**
 * 模糊搜索商品名称
 */
include_once '../header.php';
include_once '../verifyToken.php';

$goodsName = _post("goodsName");
$sql = "select goods_name,goods_id,goods_img,seller_id from goods where (goods_status = 3 or goods_status = 4 or goods_status = 7) ".
_likeParam("goods_name",$goodsName) .
"order by goods_id;";
$goodsName = "%".$goodsName."%";
$p = $conn->prepare($sql);
$p->bind_param("s",$goodsName);
$p->bind_result($goodsName,$goodsId,$goodsImg,$sellerId);
if($p->execute()){
  $data = array();
  while($p->fetch()){
    $t = array(
      "goodsId"=>$goodsId,
      "goodsName"=>$goodsName
    );
    if(!empty($goodsImg)){
      $goodsImg = $baseUrl.'/image/'.$sellerId.'/'.explode(',',$goodsImg)[0];
    }else {
      $goodsImg = "";
    }
    $t["goodsImg"] = $goodsImg;
    array_push($data,$t);
  }
  $res = array(
    "code"=>1000,
    "data"=>$data
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}else{
  $res = array(
    "code"=>1001,
    "data"=>"未知错误"
  );
  echo json_encode($res,JSON_UNESCAPED_UNICODE);
}