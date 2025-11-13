<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\AfterSale;
use app\api\model\AfterSaleDetail;
use app\api\model\Reason;
use think\Exception;

/**
 * 售后统计-逻辑
 */
class AfterSaleStatLogic
{
    static public function menu()
    {
        return '售后管理-售后统计';
    }

    static public function price_num($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
                'status' => ['<>', 4]
            ];
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $where['create_time'] = ['between', [$request['start_time'] . '-01 00:00:00', get_last_time($request['end_time'])]];
                } else {
                    $where['create_time'] = ['between', [$request['start_time'], $request['end_time'] . ' 23:59:59']];
                }
            }
            $num = AfterSale::build()->where($where)->count();
            $price = AfterSale::build()->where($where)->sum('refund_price');
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '统计');
            return ['price' => $price, 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function analyze($request, $userInfo)
    {
        try {
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $request['start_time'] = $request['start_time'] . '-01 00:00:00';
                    $request['end_time'] = get_last_time($request['end_time']);
                } else {
                    $request['start_time'] = $request['start_time'] . ' 00:00:00';
                    $request['end_time'] = $request['end_time'] . ' 23:59:59';
                }
            } else {
                if($request['type']){
                    if($request['type'] == 1){
                        $request['start_time'] = now_time(strtotime('-12 months'));
                        $request['end_time'] = now_time(time());
                    }else{
                        $request['start_time'] = now_time(strtotime('-12 day'));
                        $request['end_time'] = now_time(time());
                    }
                }else{
                    $request['start_time'] = now_time(strtotime('-12 day'));
                    $request['end_time'] = now_time(time());
                    $request['type'] = 2;
                }
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']), $request['type']);

            // 使用单次查询获取所有数据
            $query = AfterSale::build()
                ->where('is_deleted', 1)
                ->where('site_id', $request['site_id'])
                ->where('status', 2)
                ->whereBetween('create_time', [
                    $request['start_time'],
                    $request['end_time']
                ]);
            if($request['type'] == 2){
                // 使用GROUP_CONNECT和子查询优化
                $results = $query->field([
                    'DATE(create_time) as stat_date',
                    'COUNT(*) as num',
                    'SUM(refund_price) as price'
                ])->group('DATE(create_time)')->select()->toArray();
            }else{
                // 使用GROUP_CONNECT和子查询优化
                $results = $query->field([
                    'DATE_FORMAT(create_time, "%Y-%m") as stat_date',
                    'COUNT(*) as num',
                    'SUM(refund_price) as price'
                ])->group('stat_date')->select()->toArray();
            }

            // 格式化结果
            $num = [];
            $price = [];
            foreach ($list_date['data'] as $v) {
                $is = 0;
                $data = [
                    'num' => 0,
                    'price' =>0,
                ];
                foreach ($results as $result) {
                    if($v == $result['stat_date']) {
                        $is = 1;
                        $data = [
                            'num' => $result['num'],
                            'price' => $result['price'],
                        ];
                    }
                }
                $num[] = $data['num'];
                $price[] = $data['price'];
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '售后统计');
            return ['date_time' => $list_date['data'], 'price' => $price, 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function reason($request, $userInfo)
    {
        try {
            $reason = Reason::build()->where('type', 2)->where('is_deleted', 1)->column('content');
            $num = [];
            foreach ($reason as $v) {
                $where = [
                    'is_deleted' => 1,
                    'site_id' => $request['site_id'],
                    'reason' => $v
                ];
                if ($request['start_time'] && $request['end_time']) {
                    if ($request['type'] == 1) {
                        $where['create_time'] = ['between', [$request['start_time'] . '-01 00:00:00', get_last_time($request['end_time'])]];
                    } else {
                        $where['create_time'] = ['between', [$request['start_time'], $request['end_time'] . ' 23:59:59']];
                    }
                }
                $num[] = AfterSale::build()->where($where)->count();
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '售后原因占比');
            return ['reason' => $reason, 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function ranking($request, $userInfo)
    {
        try {
            $where = [
                'ad.is_deleted' => 1,
                'ad.site_id' => $request['site_id']
            ];
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $where['ad.create_time'] = ['between', [$request['start_time'] . '-01 00:00:00', get_last_time($request['end_time'])]];
                } else {
                    $where['ad.create_time'] = ['between', [$request['start_time'], $request['end_time'] . ' 23:59:59']];
                }
            }
            $orderBy = ['num', 'desc'];
            if ($request['orderBy'] && $request['sort']) {
                $orderBy = [$request['orderBy'], $request['sort']];
            }
            $data = AfterSaleDetail::build()
                ->field('
                ad.product_uuid,
                p.name,
                p.main_img,
                count(ad.product_uuid) as num,
                sum(ad.price) as price
                ')
                ->alias('ad')
                ->join('product p', 'ad.product_uuid = p.uuid')
                ->group('ad.product_uuid')
                ->order($orderBy[0], $orderBy[1])
                ->where($where)
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '商品售后次数排名');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
