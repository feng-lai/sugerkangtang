<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/11/17
 * Time: 17:21
 *
 */
namespace app\common\tools;

use think\Db;
use think\Exception;

class WechatSign
{
  /**
   * 进行接口签名
   * @return  json的数据
   */
  public function getSign($url){
    try {
      $token = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.config('wechat.AppID').'&secret='.config('wechat.AppSecret'));
      $token = json_decode($token,true);
      if(!isset($token['access_token'])){
        throw new Exception($token['errmsg'], 500);
      }
      $ticket = file_get_contents('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$token['access_token'].'&type=jsapi');
      $ticket = json_decode($ticket,true);
      if(!isset($ticket['ticket'])){
        throw new Exception($ticket['errmsg'], 500);
      }
      $data = array(
        'jsapi_ticket'        => $ticket['ticket'],
        'timestamp'    => (string) time(),
        'noncestr'    => self::getNonceStr(),
        'url'    => $url
      );
      return ['signature'=>self::makeSign($data),'timestamp'=>$data['timestamp'],'nonceStr'=>$data['noncestr'],'appid'=>config('wechat.AppID')];
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }
  /**
   *
   * 产生随机字符串，不长于32位
   * @param int $length
   * @return 产生的随机字符串
   */
  protected function getNonceStr($length = 32) {
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str ="";
    for ( $i = 0; $i < $length; $i++ )  {
      $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $str;
  }
  /**
   * 生成签名
   * @return 签名
   */
  protected function makeSign($data){
    // 去空
    $data=array_filter($data);
    //签名步骤一：按字典序排序参数
    ksort($data);
    $string_a=http_build_query($data);
    $string_a=urldecode($string_a);
    //签名步骤二：sha1加密
    $sign = sha1($string_a);
    return $sign;
  }
}