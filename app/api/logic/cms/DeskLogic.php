<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\AfterSale;
use app\api\model\AfterSaleDetail;
use app\api\model\MedicalReport;
use app\api\model\Order;
use app\api\model\Reason;
use app\api\model\User;
use think\Exception;

/**
 * 工作台-逻辑
 */
class DeskLogic
{
    static public function menu()
    {
        return '工作台';
    }

    static public function td_yd($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id']
            ];
            $today = [date('Y-m-d', time()), date('Y-m-d', time()) . ' 23:59:59'];
            $yesterday = [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'];
            $td_order = Order::build()->where($where)->where('pay_time', 'between', $today)->count();
            $yd_order = Order::build()->where($where)->where('pay_time', 'between', $yesterday)->count();

            $td_peo = Order::build()->where($where)->where('pay_time', 'between', $today)->group('user_uuid')->count();
            $yd_peo = Order::build()->where($where)->where('pay_time', 'between', $yesterday)->group('user_uuid')->count();

            $td_price = Order::build()->where($where)->where('pay_time', 'between', $today)->whereNotNull('pay_time')->sum('price');
            $yd_price = Order::build()->where($where)->where('pay_time', 'between', $yesterday)->whereNotNull('pay_time')->sum('price');

            $td_register = User::build()->where($where)->where('create_time', 'between', $today)->count();
            $yd_register = User::build()->where($where)->where('create_time', 'between', $yesterday)->count();

            AdminLog::build()->add($userInfo['uuid'], self::menu(), '今日较昨日数据');
            return [
                'order' => [
                    'num' => $td_order,
                    'persent' => calculateIncrease($td_order, $yd_order),
                ],
                'pay_peo' => [
                    'num' => $td_peo,
                    'persent' => calculateIncrease($td_peo, $yd_peo),
                ],
                'price' => [
                    'num' => $td_price,
                    'persent' => calculateIncrease($td_price, $yd_price),
                ],
                'register' => [
                    'num' => $td_register,
                    'persent' => calculateIncrease($td_register, $yd_register),
                ]
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function pending($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id']
            ];
            $pending_payment = Order::build()->where($where)->where('status', 1)->count();
            $pending_shipment = Order::build()->where($where)->where('status', 2)->count();
            $pending_after_sale = AfterSale::build()->where($where)->where('status', 1)->count();
            $pending_medical_report = MedicalReport::build()->where($where)->where('status', 1)->count();
            return [
                'pending_payment' => $pending_payment,
                'pending_shipment' => $pending_shipment,
                'pending_after_sale' => $pending_after_sale,
                'pending_medical_report' => $pending_medical_report
            ];
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '待处理订单');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function analyze($request, $userInfo)
    {
        try {
            $list_date = cut_date(strtotime('-29 day'), time(), 2);
            // 使用单次查询获取所有数据
            $query = Order::build()
                ->where('is_deleted', 1)
                ->where('site_id', $request['site_id'])
                ->whereBetween('pay_time', [
                    $list_date['data'][0] . ' 00:00:00',
                    $list_date['data'][count($list_date['data']) - 1] . ' 23:59:59'
                ]);

            // 使用GROUP_CONNECT和子查询优化
            $results = $query->field([
                'DATE(pay_time) as stat_date',
                'COUNT(*) as order_num',
                'SUM(price) as total_amount',
                'COUNT(DISTINCT user_uuid) as user_count'
            ])->group('DATE(pay_time)')->select()->toArray();

            // 格式化结果
            $num = [];
            $price = [];
            $peo = [];
            foreach ($list_date['data'] as $v) {
                $is = 0;
                $data = [
                    'order_num' => 0,
                    'total_amount' =>0,
                    'user_count' => 0,
                ];
                foreach ($results as $result) {
                    if($v == $result['stat_date']) {
                        $is = 1;
                        $data = [
                            'order_num' => $result['order_num'],
                            'total_amount' => $result['total_amount'],
                            'user_count' => $result['user_count'],
                        ];
                    }
                }
                $num[] = $data['order_num'];
                $price[] = $data['total_amount'];
                $peo[] = $data['user_count'];
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '交易概况（近30天）');
            return ['date_time' => $list_date['data'], 'price' => $price, 'num' => $num, 'peo' => $peo];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
