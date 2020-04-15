<?php
/**
 * token类
 */
class Token {
  private $user;
  private static $header=array(
    'alg'=>'HS256',
    'typ'=>'JWT'
  );
  private static $key='043677';
  // 生成token
  public static function createToken(array $payload){
    if(is_array($payload)){
      $base64header=self::base64UrlEncode(json_encode(self::$header));
      $base64payload=self::base64UrlEncode(json_encode($payload));
      $token=$base64header.'.'.$base64payload.'.'.self::signature($base64header.'.'.$base64payload,self::$key,self::$header['alg']);
      return $token;
    }else{
      return false;
    }
  }
  // 验证token
  public static function verifyToken($Token){
    $tokens = explode('.', $Token);
    if (count($tokens) != 3)
      return false;
    
    list($base64header, $base64payload, $sign) = $tokens;

    $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
    if (empty($base64decodeheader['alg']))
      return false;

    //签名验证
    if (self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign)
      return false;

    $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);
    //签发时间大于当前服务器时间验证失败
    if (isset($payload['iat']) && $payload['iat'] > time())
      return false;

    //过期时间小于当前服务器时间验证失败
    if (isset($payload['exp']) && $payload['exp'] < time())
      return false;

    //该nbf时间之前不接收处理该Token
    if (isset($payload['nbf']) && $payload['nbf'] > time())
      return false;

    return $payload;

  }
  // 编码
  private static function base64UrlEncode(string $input){
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
  }
  // 解码
  private static function base64UrlDecode(string $input){
    $remainder = strlen($input) % 4;
    if ($remainder) {
      $addlen = 4 - $remainder;
      $input .= str_repeat('=', $addlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
  }
  // 签名验证
  private static function signature(string $input, string $key, string $alg = 'HS256'){
    $alg_config=array(
      'HS256'=>'sha256'
    );
    return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key,true));
  }
}
?>