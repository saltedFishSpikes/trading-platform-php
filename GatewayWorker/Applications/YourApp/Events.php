<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    // public static function onConnect($client_id)
    // {
    //     // 向当前client_id发送数据 
    //     Gateway::sendToClient($client_id, "Hello $client_id\r\n");
    //     // 向所有人发送
    //     Gateway::sendToAll("$client_id login\r\n");
    // }
    public static function onWebSocketConnect($client_id, $data)
    {
        if(!Gateway::isUidOnline($data['get']['userId'])){
            // 没有绑定则进行绑定
            $_SESSION['uid']=$data['get']['userId'];
            Gateway::bindUid($client_id, $data['get']['userId']);// 绑定发送人uid
        }
    }
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {    
        // 心跳检测
        if($message == 'heart'){
            // Gateway::sendToGroup($groupName,'heart');
            return 0;
        }
        $data = json_decode($message,true);
        // Gateway::sendToAll($message);
        if(!Gateway::isUidOnline($data['from'])){
            // 没有绑定则进行绑定
            Gateway::bindUid($client_id, $data['from']);// 绑定发送人uid
        }
        $from_client_id = Gateway::getClientIdByUid($data['from']); //发送人client_id数组
        $to_client_id = Gateway::getClientIdByUid($data['to']); //接收人client_id数组
        $groupArr = Gateway::getAllGroupIdList();  // 所有组

        $groupName = '';
        if(array_search($data['from'].'-'.$data['to'],$groupArr) != false){
            // from-to的组已经存在，且二人至少一人在组中
            $groupName = $data['from'].'-'.$data['to'];
        }else if(array_search($data['to'].'-'.$data['from'],$groupArr) != false){
            // to-from的组已经存在，且二人至少一人在组中
            $groupName = $data['to'].'-'.$data['from'];
        }else{
            // 组不存在，创建
            $groupName = $data['from'].'-'.$data['to'];
        }

        $group_client_list =  Gateway::getClientIdListByGroup($groupName); // 组内client数组
        if(!empty($from_client_id)){
            foreach($from_client_id as $value){
                if(array_search($value,$group_client_list) == false){
                    Gateway::joinGroup($value,$groupName);
                }
            }
        }
        if(!empty($to_client_id)){
            foreach($to_client_id as $value){
                if(array_search($value,$group_client_list) == false){
                    Gateway::joinGroup($value,$groupName);
                }
            }
        }
        // // 初次发消息，固定空字符串，用于将两个用户在一开始就加入一个group，
        // // 否则即使都在线，用户发的第一个消息也不能及时收到
        // if(empty($data['msg'])){
        //     return 0;
        // }

        list($s1, $s2) = explode(' ', microtime());
        $time = (float)sprintf('%.0f',(floatval($s1) + floatval($s2)) * 1000);

        $content = array(
            "content"=>$data['msg'],
            "chatTime"=>$time,
            "userFromId"=>$data['from'],
            "userToId"=>$data['to'],
            "userToName"=>$data['toName'],
            "userFromName"=>$data['fromName']
        );
        // 组内广播
        Gateway::sendToGroup($groupName,json_encode($content,JSON_UNESCAPED_UNICODE));
        // 写入文件
        $fileBase = '../chatRoom/chattingRecords/';
        
        if(!is_dir($fileBase)){
            mkdir($fileBase,777);
        }
        
        $file = null;
        if(file_exists($fileBase.$data['from'].'-'.$data['to'].'.txt')){
            $file = fopen($fileBase.$data['from'].'-'.$data['to'].'.txt',"a") or die();
        }else if (file_exists($fileBase.$data['to'].'-'.$data['from'].'.txt')) {
            $file = fopen($fileBase.$data['to'].'-'.$data['from'].'.txt',"a") or die();
        }else {
            $file = fopen($fileBase.$data['from'].'-'.$data['to'].'.txt',"a") or die();
        }

        fwrite($file,json_encode($content,JSON_UNESCAPED_UNICODE)."\r\n");
        fclose($file);

   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
        include '../header.php';
        // 更新用户断开连接时间
        $userId = $_SESSION['uid'];
        $sql = "update user set offline_time = ? where user_id = ?;";
        $now = getUnixTimestamp();
        $p = $conn->prepare($sql);
        $p->bind_param('ii',$now,$userId);
        
        $p->execute();
        $p->free_result();
        $p->close();
        $conn->close();
   }
}
