<?php

namespace app\api\logic\cms;

use app\api\model\Bill;
use app\api\model\CashOut;
use app\api\model\AdminLog;
use app\api\model\Retail;
use think\Exception;
use think\Db;

/**
 * 提现-逻辑
 */
class CashOutLogic
{
    static public function menu()
    {
        return '财务管理-提现审核';
    }

    static public function setNote($request, $userInfo)
    {
        try {
            $data = CashOut::build()->where('cash_out_id',$request['cash_out_id'])->findOrFail();
            $data->save(['note'=>$request['note'],'update_time'=>now_time(time())]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置备注');
            return true;
        }catch (Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = CashOut::build()->where('cash_out_id',$request['cash_out_id'])->findOrFail();
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['review_time'] = now_time(time());
            $data->save(array_filter($request));
            if($request['status'] == 3){
                //推广员钱包
                $retail = Retail::build()->where('uuid',$data->retail_uuid)->findOrFail();
                $retail->setInc('wallet',$data->price);
                //账单
                Bill::build()->insert([
                    'uuid'=>uuid(),
                    'user_uuid'=>$data->user_uuid,
                    'cash_out_id'=>$request['cash_out_id'],
                    'bill_id'=>'BX'.getOrderNumber(),
                    'type'=>6,
                    'price'=>$data->price,
                    'wallet'=>$retail->wallet,
                    'create_time'=>now_time(time()),
                    'update_time'=>now_time(time()),
                ]);

            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '提现审核:'.$request['status'] == 2?'通过':'不通过');
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function Add($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            CashOut::build()->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '新增收货地址');
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Edit($request, $userInfo)
    {
        try {
            $data = CashOut::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $request['update_time'] = now_time(time());
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '收货地址编辑');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'c.is_deleted' => 1,
                'c.site_id' => $request['site_id'],
            ];
            $request['keyword']?$where['r.name|c.cash_out_id'] = ['like','%'.$request['keyword'].'%']:'';
            $request['status']?$where['c.status'] = $request['status']:'';
            $request['retail_uuid']?$where['c.retail_uuid'] = $request['retail_uuid']:'';
            $request['user_uuid']?$where['c.user_uuid'] = $request['user_uuid']:'';
            ($request['start_time'] && $request['end_time'])?$where['c.create_time'] = ['between', [$request['start_time'],$request['end_time'].' 23:59:59']]:'';
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
            return CashOut::build()
                ->field('c.user_uuid,c.cash_out_id,c.status,c.price,c.commission,c.real_price,c.bank_number,c.bank_name,c.note,c.reason,c.name,c.create_time,c.review_time,r.name as retail_name,r.type,c.phone,c.retail_uuid')
                ->alias('c')
                ->join('retail r','r.uuid = c.retail_uuid')->where($where)
                ->order('c.create_time desc')
                ->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询收货地址详情');
            $where = [
                'c.cash_out_id' => $uuid,
                'c.is_deleted' => 1,
            ];
            $data = CashOut::build()
                ->field('
                    c.cash_out_id,
                    c.status,
                    c.price,
                    c.commission,
                    c.real_price,
                    c.bank_number,
                    c.bank_name,
                    c.note,
                    c.reason,
                    c.name,
                    c.img,
                    c.create_time,
                    c.review_time,
                    c.retail_uuid,
                    r.name as retail_name,
                    r.phone as retail_phone,
                    r.type,
                    a.name as admin_name,
                    c.phone
                ')
                ->alias('c')
                ->join('retail r','r.uuid = c.retail_uuid','left')
                ->join('admin a','a.uuid = c.admin_uuid','left')
                ->where($where)
                ->findOrFail();

            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($uuid, $userInfo)
    {
        try {
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除收货地址');
            CashOut::build()->whereIn('uuid', explode(',',$uuid))->update(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDefault($uuid, $userInfo)
    {
        try {
            $data = Address::build()->where('uuid', $uuid)->findOrFail();
            Address::build()->where('user_uuid', $data->user_uuid)->update(['is_default'=>2]);
            $data->save(['is_default'=>1]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '设置默认收货地址');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
