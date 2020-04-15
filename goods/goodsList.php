<?php
/**
 * 商品列表查询
 */

include_once '../header.php';

$pageNum = _post('pageNum');
$pageSize = _post('pageSize');
$goodsName = _post('goodsName');
$startTime = _post('startTime');
$endTime = _post('endTime');
$goodsType = _post('goodsType');
$sellerName = _post('sellerName');
$successNum = _post('successNum');
$sellerName = _post('sellerName');
//范围字符串的参数处理

$successArr = _stringParam($successNum);

$sql = 'select g.goods_id,g.goods_name,g.goods_price,g.goods_type,g.publish_time,g.change_time,g.goods_status,g.goods_img,u.user_id,u.user_name,u.success_num,u.user_img from '
  .'goods as g inner join '
  .'(select us.user_id,us.user_name,us.user_img,us.sell_num + ifnull(b.buy_num,0) as success_num from '
  .'(select user.user_id,user.user_name,user.user_img,ifnull(s.sell_num,0) as sell_num from user left join '
  .'(select seller_id,count(*) as sell_num from trade where trade_status = 4 group by seller_id) as s on user.user_id = s.seller_id) as us '
  .'left join (select buyer_id,count(*) as buy_num from trade where trade_status = 4 group by buyer_id) as b on us.user_id = b.buyer_id '
  .')as u on g.seller_id = u.user_id where (g.goods_status = 3 or g.goods_status = 7) '
  ._likeParam('g.goods_name',$goodsName)
  ._rangeParam('g.goods_type',$goodsType)
  ._betweenParam('g.publish_time',$startTime,$endTime)
  ._likeParam('u.user_name',$sellerName)
  ._betweenParam('u.success_num',$successArr[0],$successArr[1])
  ." order by g.publish_time desc;";
//模糊查询的参数处理
$goodsName = '%'.$goodsName.'%';
$sellerName = '%'.$sellerName.'%';
//预处理查询
$p = $conn->prepare($sql);
$p->bind_param(
  'siiisii',
  $goodsName,
  $goodsType,
  $startTime,
  $endTime,
  $sellerName,
  $successArr[0],
  $successArr[1]
);
$p->bind_result($id,$name,$price,$type,$time,$changeTime,$goodsStatus,$img,$userId,$userName,$success,$userImg);
if($p->execute()){
  //查询结果
  $arr = array();
  while($p->fetch()){
    $now = getUnixTimestamp();
    if($goodsStatus == 7 && $now-$changeTime <= 300000){
      continue;
    }
    $user = array(
      "name"=>$userName,
      "successNum"=>$success
    );
    $user['userImg'] = (!empty($userImg)?($baseUrl.'/image/'.$userId.'/'.$userImg):"");
    if(!empty($img)){
      $img = $baseUrl.'/image/'.$userId.'/'.explode(',',$img)[0];
    }else {
      $img = "";
    }
    $t = array(
      "goodsId"=>$id,
      "goodsName"=>$name,
      "goodsPrice"=>$price,
      "goodsType"=>$type,
      "seller"=>$user,
      "goodsImg"=>$img
    );
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