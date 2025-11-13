<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
namespace app\common\tools;
use app\api\model\Order;

use think\Exception;

class IosPay
{
  public static  function validate_apple_pay($receipt_data,$order_sn){
    // 验证参数
    if (strlen($receipt_data)<20){
      $result=array(
        'msg'=>'非法参数'
      );
      return $result;
    }
    // 请求验证
    $html = acurl($receipt_data);
    $data = json_decode($html,true);

    // 如果是沙盒数据 则验证沙盒模式
    if($data['status']=='21007'){
      // 请求验证
      $html = acurl($receipt_data, 1);
      $data = json_decode($html,true);
      $data['sandbox'] = '1';
    }

    //if (isset($_GET['debug'])) {
      //exit(json_encode($data));
    //}

    // 判断是否购买成功
    if(intval($data['status'])===0){
      $order = Order::build()->where('order_sn', $order_sn)->find();
      $save_data = ['status' => 1, 'trade_no' => $receipt_data, 'pay_time' => date('Y-m-d H:i:s'), 'pay_type'=>5];
      if ($order->where('order_sn', $order_sn)->update($save_data) !== false) {
        $order->pay_success($order);
        return true;
      }
    }else{
      $result=array(
        'msg'=>'购买失败 status:'.$data['status']
      );
      return $result;
    }

  }
}
