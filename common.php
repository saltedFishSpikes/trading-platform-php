<?php
/**
 * 项目公共方法
 */

/**
 * 13位时间戳
 * @return float
 */
function getUnixTimestamp(){
  list($s1, $s2) = explode(' ', microtime());
  return (float)sprintf('%.0f',(floatval($s1) + floatval($s2)) * 1000);
}

/**
 * 接收前端post过来的参数
 * @param mixed $str 参数名
 * @return mixed
 */
function _post($str){
  return !empty($_POST[$str]) ? $_POST[$str] : "";
}
/**
 * 接收文件
 */
function _file($str){
  $s=!empty($_FILES[$str]) ? $_FILES[$str] : null;
  return $s;
}
/**
 * 模糊搜索参数处理
 * @param string $paramName 参数名（参数需要在bind_param中添加%，不要在这添加）
 * @param mixed $param 参数值
 * @return string
 */
function _likeParam($paramName,$param){
  return " and (".(!empty($param) ? ($paramName." like ?)") : ($paramName." like ? or 1 = 1)"));

}
/**
 * 精确搜索参数处理
 * @param string $paramName 参数名
 * @param mixed $param 参数值
 * @return string
 */
function _rangeParam($paramName,$param){
  if(stripos($paramName,'trade_status')!=false && $param == 5){
    $now = getUnixTimestamp();
    $str = " and (".$paramName." = ? and ".explode('.',$paramName)[0].".change_time <= ".($now - 300000).")";
    return $str;
  }else if(stripos($paramName,'trade_status')!=false && $param == 1){
    $now = getUnixTimestamp();
    $str = " and (".$paramName." = ? and ".explode('.',$paramName)[0].".change_time > ".($now - 300000).")";
    return $str;
  }
  return " and (".(!empty($param) ? ($paramName." = ?)") : ($paramName." = ? or 1 = 1)"));
}

/**
 * 范围搜索参数处理(保证大小值同时存在或同时不存在)，$small<$big为true，否则false
 * @param string $paramName 参数名
 * @param mixed $big 大值
 * @param mixed $small 小值
 * @return string
 */
function _betweenParam($paramName,$small,$big){
  return " and ( ".((($small === 0 || !empty($big)) && $small < $big) ? ($paramName." between ? and ?)"):($paramName." between ? and ? or 1 = 1)"));
}
/**
 * 修改时参数为空处理
 * @param string $paramName 参数名
 * @param mixed $param 参数值
 * @param boolean $isLast 是否为最后一个参数
 */
function _updateParam($paramName,$param,$isLast){
  return  $paramName.(!$isLast?" = ?, ":" = ? ");
}
/**
 * 字符串型范围参数处理，类似(100~1000，100以上)
 * @param string $param 参数值
 * @return array
 */
function _stringParam($param){
  if(empty($param)){
    return array(2,1);
  }else{
    $res = array();
    $i = strpos($param,'~');
    $len = strlen($param);
    if($i === false){
      array_push($res,(int)substr($param,0,stripos($param,'以上')));
      array_push($res,999999999);
    }else {
      array_push($res,(int)substr($param,0,$i));
      array_push($res,(int)substr($param,$i+1,$len-$i));
    }
    return $res;
  }
}
