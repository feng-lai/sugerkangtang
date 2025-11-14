<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Bill;
use app\api\model\Partner;
use app\api\model\Retail;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 平台流水逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class BillLogic
{
    static public function profit($request, $userInfo)
    {
        try {
            //类别 1=商城订单 2=佣金结算 3=提现申请 4=取消订单退款 5=售后退款 6=提现退回
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
                'type' => ['in', [1, 5, 7]]
            ];
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $request['start_time'] = $request['start_time'] . '-01 00:00:00';
                    $request['end_time'] = get_last_time($request['end_time']);
                } else {
                    $request['start_time'] = $request['start_time'] . ' 00:00:00';
                    $request['end_time'] = $request['end_time'] . ' 23:59:59';
                }
            } else {
                if ($request['type']) {
                    if ($request['type'] == 1) {
                        $request['start_time'] = now_time(strtotime('-12 months'));
                        $request['end_time'] = now_time(time());
                    } else {
                        $request['start_time'] = now_time(strtotime('-12 day'));
                        $request['end_time'] = now_time(time());
                    }
                } else {
                    $request['start_time'] = now_time(strtotime('-12 day'));
                    $request['end_time'] = now_time(time());
                    $request['type'] = 2;
                }
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']), $request['type']);
            $data = Bill::build()->where($where);

            if ($request['type'] == 2) {
                // 使用GROUP_CONNECT和子查询优化
                $results = $data->field([
                    'DATE(create_time) as stat_date',
                    'SUM(CASE WHEN type=1 THEN price ELSE 0 END) as order_price',
                    'ABS(SUM(CASE WHEN type IN (4,5) THEN price ELSE 0 END)) as after_sale_price',
                    'SUM(CASE WHEN type=2 THEN price ELSE 0 END) as commission_price',
                ])->group('DATE(create_time)')->select();
            } else {
                // 使用GROUP_CONNECT和子查询优化
                $results = $data->field([
                    'DATE_FORMAT(create_time, "%Y-%m") as stat_date',
                    'SUM(CASE WHEN type=1 THEN price ELSE 0 END) as order_price',
                    'ABS(SUM(CASE WHEN type IN (4,5) THEN price ELSE 0 END)) as after_sale_price',
                    'SUM(CASE WHEN type=2 THEN price ELSE 0 END) as commission_price',
                ])->group('stat_date')->select();
            }
            $results = $results->each(function ($item, $key) {
                $item['profit'] = $item['order_price'] - $item['after_sale_price'] - $item['commission_price'];
            })->toArray();
            // 格式化结果
            $profit = [];
            $after_sale_price = [];
            $commission_price = [];
            foreach ($list_date['data'] as $v) {
                $data = [
                    'profit' => 0,
                    'after_sale_price' => 0,
                    'commission_price' => 0,
                ];
                foreach ($results as $result) {
                    if ($v == $result['stat_date']) {
                        $data = [
                            'profit' => $result['profit'],
                            'commission_price' => $result['commission_price'],
                            'after_sale_price' => $result['after_sale_price'],
                        ];
                    }
                }
                $profit[] = floatval($data['profit']);
                $after_sale_price[] = floatval($data['after_sale_price']);
                $commission_price[] = floatval($data['commission_price']);
            }
            return ['date_time' => $list_date['data'], 'profit' => $profit, 'after_sale_price' => $after_sale_price, 'commission_price' => $commission_price];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    static public function stat($request, $userInfo)
    {
        try {
            if ($request['start_time'] && $request['end_time']) {
                $time = checkDateFormat($request['start_time']);
                if ($time && $time == 1) {
                    $request['end_time'] = $request['end_time'] . ' 23:59:59';
                }
                if ($time && $time == 2) {
                    $request['start_time'] = $request['start_time'] . '-01';
                    $request['end_time'] = get_last_time($request['end_time']);
                }
            }
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
                'type' => ['in', [1, 2, 4, 5]]
            ];
            if ($request['start_time'] && $request['end_time']) {
                $where['create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
            }
            $res = [];
            $after_sale_price = 0;
            $data = Bill::build()
                ->field('
                    sum(price) as price,
                    type
                ')
                ->where($where)
                ->group('type')
                ->select()->each(function ($item) use (&$res, &$after_sale_price) {
                    if ($item['type'] == 1) {
                        $res['order_price'] = floatval($item['price']);
                    }
                    if ($item['type'] == 5 || $item['type'] == 4) {
                        $after_sale_price += floatval($item['price']);
                    }
                    if ($item['type'] == 2) {
                        $res['commission_price'] = floatval($item['price']);
                    }
                });
            $res['after_sale_price'] = abs(round($after_sale_price, 2));
            if (count($data)) {
                $res['profit'] = $res['order_price'] - $res['after_sale_price'] - $res['commission_price'];
            } else {
                $res['order_price'] = 0;
                $res['after_sale_price'] = 0;
                $res['commission_price'] = 0;
                $res['profit'] = 0;
            }

            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    static public function cmsList($request, $userInfo)
    {
        $where = [
            'is_deleted' => 1,
            'site_id' => $request['site_id']
        ];
        $request['type'] ? $where['type'] = $request['type'] : '';
        $request['bill_id'] ? $where['bill_id'] = ['like','%'.$request['bill_id'].'%'] : '';
        if($request['status']){
            if($request['status'] == 1){
                $where['price'] = ['>',0];
            }else if($request['status'] == 2){
                $where['price'] = ['<',0];
            }else{
                return ['msg'=>'status非法参数'];
            }
        }
        ($request['start_time'] && $request['end_time']) ? $where['create_time'] = ['between time', [$request['start_time'], $request['end_time']]] : '';
        if ($request['retail_uuid']) {
            $retail = Retail::build()->where('uuid', $request['retail_uuid'])->where('is_deleted',1)->findOrFail();
            $where['user_uuid'] = $retail->user_uuid;
            if($request['type']){
                $where['type'] = $request['type'];
            }else{
                $where['type'] = ['in',[2,3,6]];
            }
        }
        if ($request['user_uuid']) {
            $where['user_uuid'] = $request['user_uuid'];
            if($request['type']){
                $where['type'] = $request['type'];
            }else{
                $where['type'] = ['in',[3,6,7,8,9,10]];
            }
        }
        $result = Bill::build()
            ->where($where)
            ->field('bill_id,order_id,after_sale_id,commission_order_id,cash_out_id,price,type,create_time,user_uuid,wallet')
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
            ->each(function ($item, $key){
                $item['main_id'] = $item['order_id'] ?? $item['after_sale_id'] ?? $item['cash_out_id'] ?? $item['commission_order_id'];
                $user = User::build()->where('uuid', $item['user_uuid'])->find();
                $item['user_name'] = $user->name ?? '';
                $item['phone'] = $user->phone ?? '';
                unset($item['order_id']);
                unset($item['after_sale_id']);
                unset($item['cash_out_id']);
                unset($item['commission_order_id']);
            });
        AdminLog::build()->add($userInfo['uuid'], '平台流水管理', '查询列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data['income'] = Bill::build()->where('type', 'in', [1, 8])->where('pay_type', 'in', [2, 3, 4])->sum('price');
        $data['month_income'] = Bill::build()->where('type', 'in', [1, 8])->where('pay_type', 'in', [2, 3, 4])->whereTime('create_time', 'month')->sum('price');
        AdminLog::build()->add($userInfo['uuid'], '平台流水管理', '查询统计');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            //最多6个
            if (RechangeSet::build()->count() >= 6) {
                throw new Exception('最多添加6条配置', 500);
            }

            $data = [
                'uuid' => uuid(),
                'price' => $request['price'],
                'coins' => $request['coins'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            RechangeSet::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '新增：' . $data['coins']);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = RechangeSet::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '更新：' . $user->coins);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = RechangeSet::build()->where('uuid', $id)->findOrFail();
            $data->delete();
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '删除：' . $data->coins);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
