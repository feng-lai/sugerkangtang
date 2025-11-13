<?php

namespace app\api\logic\mini;

use app\api\model\BankCard;
use app\api\model\Bill;
use app\api\model\CashOut;
use app\api\model\Config;
use app\api\model\Retail;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 提现-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class CashOutLogic
{


    static public function Add($request, $userInfo)
    {
        try {
            Db::startTrans();
            $retail = Retail::build()->where('user_uuid', $userInfo['uuid'])->findOrFail();
            $bank_card = BankCard::build()->where('uuid', $request['bank_card_uuid'])->where('user_uuid', $userInfo['uuid'])->findOrFail();
            if (!$retail->cash_out_persent) {
                $retail->cash_out_persent = Config::build()->where('key', 'CashOutPersent')->value('value');
            }
            if (!$retail->cash_out_low) {
                $retail->cash_out_low = Config::build()->where('key', 'CashOutMin')->value('value');
            }
            if ($request['price'] > $retail->wallet) {
                return ['msg' => '余额不足'];
            }
            if($request['price'] < $retail->cash_out_low){
                return ['msg'=>'最低提现金额为'.$retail->cash_out_low.'元'];
            }
            $commission = $request['price'] * $retail->cash_out_persent * 0.01;
            $data = [
                'user_uuid' => $userInfo['uuid'],
                'cash_out_id'=>'CA'.getOrderNumber(),
                'uuid' => uuid(),
                'commission' => $commission,
                'price' => $request['price'],
                'real_price' => $request['price'] - $commission,
                'retail_uuid'=>$retail->uuid,
                'bank_number'=>$bank_card->number,
                'bank_name'=>$bank_card->card_name,
                'name'=>$bank_card->name,
                'phone'=>$bank_card->phone,
                'site_id'=>$request['site_id'],
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ];
            CashOut::build()->insert($data);
            //钱包
            $retail->setDec('wallet',$request['price']);
            //账单
            Bill::build()->insert([
                'uuid' => uuid(),
                'bill_id'=>'BX'.getOrderNumber(),
                'price'=>-$request['price'],
                'type'=>3,
                'cash_out_id'=>$data['cash_out_id'],
                'user_uuid' => $userInfo['uuid'],
                'wallet'=>$retail->wallet,
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ]);
            Db::commit();
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request, $userInfo){
        try {
            $data = CashOut::build()
                ->field('cash_out_id,price,commission,real_price,create_time,status,note')
                ->where('user_uuid', $userInfo['uuid'])
                ->where('is_deleted',1)
                ->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']]);
            return $data;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($id, $userInfo)
    {
        try {
            $data = CashOut::build()->where('cash_out_id', $id)->where('user_uuid', $userInfo['uuid'])->findOrFail();
            return $data;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
