<?php

namespace app\api\logic\mini;

use app\api\model\Bill;
use app\api\model\Order;
use app\api\model\Score;
use app\api\model\Attention;
use app\api\model\User;
//use app\api\model\IncomeRatio;
use app\api\model\RechangeSet;
use app\api\model\ContestantGift;
use app\api\model\Contestant;
use app\api\model\ContestantStyle;
use app\api\model\ContestantStyleVideo;
use app\api\model\GiftSet;
use app\api\model\Config;
use think\Exception;
use think\Db;

/**
 * 订单-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class ContestantGiftLogic
{

  static public function cmsList($request,$userinfo)
  {
    $map['g.user_uuid'] = ['=',$userinfo['uuid']];
    $map['g.create_time'] = ['between time',[$request['start_time'],$request['end_time'].' 24:00:00']];
    $result = ContestantGift::build()
      ->alias('g')
      ->field('u.avatar,c.name,g.coins,g.create_time,contestant_uuid,c.id_photo')
      ->join('contestant c','c.uuid = g.contestant_uuid','LEFT')
      ->join('user u','u.uuid = c.user_uuid','LEFT')
      ->where($map)
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $result;
  }
  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      $persent = Config::build()->where('key','COINS_PRICE')->value('value');
      $gift_set = GiftSet::build()->findOrFail($request['gift_set_uuid']);
      $contestant = Contestant::build()->where('uuid','=',$request['contestant_uuid'])->where('state','in','2,4')->findOrFail();
      //星豆是否足够
      if($gift_set->coins * $request['qty'] > $userInfo['coins']){
        throw new Exception('星豆不足', 500);
      }
      $order = ContestantGift::build();
      $order->uuid = uuid();
      $order->user_uuid = $userInfo['uuid'];
      $order->contestant_uuid = $request['contestant_uuid'];
      $order->matchs_step_uuid = $request['matchs_step_uuid'];
      $order->qty = $request['qty'];
      $order->gift_set_uuid = $request['gift_set_uuid'];
      $order->gift_set_data = $gift_set;
      $order->coins = $gift_set->coins * $request['qty'];
      if($request['contestant_style_video_uuid']){
        $order->contestant_style_video_uuid = $request['contestant_style_video_uuid'];
        $contestant_style = ContestantStyle::build()->where('uuid',ContestantStyleVideo::build()->where('uuid',$request['contestant_style_video_uuid'])->value('contestant_style_uuid'))->find();
        $order->contestant_style_data = $contestant_style;
        $order->contestant_style_uuid = $contestant_style->uuid;
      }
      $order->save();

      //用户星币余额扣除
      User::build()->where('uuid',$userInfo['uuid'])->setDec('coins',$gift_set->coins * $request['qty']);
      //账单
      $bill = Bill::build();
      $bill->uuid = uuid();
      $bill->coins = $gift_set->coins * $request['qty'];
      $bill->price = $gift_set->coins * $request['qty']*$persent;
      $bill->user_uuid = $userInfo['uuid'];
      $bill->bill_sn = numberCreate();
      $bill->type = 2;
      $bill->contestant_gift_uuid = $order->uuid;
      $bill->create_time = date('Y-m-d H:i:s');
      $bill->update_time = date('Y-m-d H:i:s');
      $bill->save();

      //投票用户获得积分
      $score = Score::build();
      $score->uuid = uuid();
      $score->user_uuid = $userInfo['uuid'];
      //$score->score = floor($gift_set->coins * $request['qty'] * (Config::build()->find('GIFT_PRICE')->value/Config::build()->find('GIFT_SCORE')->value));
      $score->score = $gift_set->score * $request['qty'];
      $score->score_wallet = Score::build()->where('user_uuid',$userInfo['uuid'])->sum('score') + $gift_set->score * $request['qty'];
      $score->score_type = 2;
      $score->contestant_gift_uuid = $order->uuid;
      $res = $score->save();

      //成为亲友团
      if(!Attention::build()->where(['user_uuid'=>$userInfo['uuid'],'contestant_uuid'=>$request['contestant_uuid'],'type'=>1])->count()){
        $atten = Attention::build();
        $atten->uuid = uuid();
        $atten->user_uuid = $userInfo['uuid'];
        $atten->type = 1;
        $atten->contestant_uuid = $request['contestant_uuid'];
        $atten->save();
      }
      //选手分佣
      User::build()->where('uuid',$contestant->user_uuid)->setInc('wallet',round($gift_set->coins * $request['qty']*$gift_set->persent*$persent,2));
      //账单
      $bills = Bill::build();
      $bills->uuid = uuid();
      $bills->coins = $gift_set->coins * $request['qty'];
      $bills->price = round($gift_set->coins * $request['qty']*$gift_set->persent*$persent,2);
      $bills->user_uuid = $contestant->user_uuid;
      $bills->bill_sn = numberCreate();
      $bills->type = 3;
      $bills->contestant_gift_uuid = $order->uuid;
      $bills->create_time = date('Y-m-d H:i:s');
      $bills->update_time = date('Y-m-d H:i:s');
      $bills->save();

      //选手得票
      Contestant::build()->where('uuid',$request['contestant_uuid'])->setInc('gift',$gift_set->coins * $request['qty']);
      if($request['contestant_style_video_uuid']){
        //亚姐值视频得票
        ContestantStyleVideo::build()->where('uuid',$request['contestant_style_video_uuid'])->setInc('gift',$gift_set->coins * $request['qty']);
      }

      //波比收益
      //IncomeRatio::build()->contestant_gift($order->uuid);
      Db::commit();
      return $order->uuid;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }
  //修改为支付成功
  static public function miniEdit($pay_type,$order_sn)
  {
    try {
      Db::startTrans();
      $order = Order::build()->where('order_sn',$order_sn)->findOrFail();
      $order->pay_time = date('Y-m-d H:i:s');
      $order->status = 1;
      $order->save();
      if($order->type == 1){
        //用户星币增加
        $coins = RechangeSet::build()->where('uuid',$order->rechange_set_uuid)->value('coins');
        User::build()->where('uuid',$order->user_uuid)->setInc('cions',$coins*$order->qty);
      }
      if($order->type == 2){
        //选手获得礼物
        $gift = GiftSet::build()->where('uuid',$order->gift_set_uuid)->findOrFail();
        $contestant_gift = ContestantGift::build();
        $contestant_gift->user_uuid = $order->user_uuid;
      }
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  // static public function miniDelete($id, $userInfo)
  // {
  //   try {
  //     Db::startTrans();
  //     User::build()->where('uuid', $id)->update(['is_deleted' => 2]);
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //     Db::rollback();
  //     throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
