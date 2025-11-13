<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AfterSale;
use app\api\model\AfterSaleDetail;
use app\api\model\AfterSaleLog;
use app\api\model\Bill;
use app\api\model\CommissionOrder;
use app\api\model\MedicalReport;
use app\api\model\Msg;
use app\api\model\Order;
use app\api\model\OrderAddress;
use app\api\model\OrderDetail;
use app\api\model\OrderLog;
use app\api\model\OrderPath;
use app\api\model\Product;
use app\api\model\ProductAttribute;
use app\api\model\User;
use app\common\tools\wechatMsg;
use app\common\tools\WechatRefund;
use app\common\wechat\Pay;
use think\Exception;
use think\Db;

/**
 * 售后逻辑
 * User:
 * Date: 2022-08-11
 * Time: 21:24
 */
class AfterSaleLogic
{
    static public function menu()
    {
        return '售后管理-售后订单';
    }

    static public function cmsList($request, $userInfo)
    {
        $map = [];
        $request['keyword'] ? $map['a.after_sale_id|p.name'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['user_info'] ? $map['u.name|u.phone'] = ['like', '%' . $request['user_info'] . '%'] : '';
        $request['status'] ? $map['a.status'] = ['=', $request['status']] : '';
        $request['start_time'] ? $map['a.create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        $result = AfterSale::build()
            ->field('a.after_sale_id,a.create_time,a.status,a.price,a.reason,a.order_id,a.user_uuid')
            ->alias('a')
            ->where($map)
            ->join('user u', 'a.user_uuid = u.uuid', 'left')
            ->join('after_sale_detail ad', 'ad.after_sale_id = a.after_sale_id', 'left')
            ->join('product p', 'p.uuid = ad.product_uuid', 'left')
            ->order('a.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->product = AfterSaleDetail::build()
                    ->field('p.uuid as product_uuid,p.name,a.price,a.qty,av.name as attribute_name,at.attribute_value,at.img as product_img')
                    ->alias('a')
                    ->join('product_attribute at', 'at.uuid = a.product_attribute_uuid', 'left')
                    ->join('product p', 'at.product_uuid = p.uuid', 'left')
                    ->join('attribute av', 'av.uuid = at.attribute_uuid', 'left')
                    ->where('a.order_id', $item['order_id'])
                    ->where('a.is_deleted', 1)
                    ->where('a.after_sale_id', $item['after_sale_id'])
                    ->select();
                $item->user = User::build()->field('uuid,name,img')->where(['uuid' => $item['user_uuid']])->find();
            });

        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = AfterSale::build()->where('after_sale_id', $id)->findOrFail();
        $data->log = AfterSaleLog::build()->where('after_sale_id', $id)->order('create_time asc')->select()->each(function ($item) {
            $item->admin_name = Admin::build()->where('uuid',$item->admin_uuid)->value('name');
        });
        $data->product = AfterSaleDetail::build()
            ->field('p.uuid as product_uuid,p.name,a.price,a.qty,av.name as attribute_name,at.attribute_value,at.code as attribute_code,at.img as attribute_img')
            ->alias('a')
            ->join('product_attribute at', 'at.uuid = a.product_attribute_uuid', 'left')
            ->join('product p', 'at.product_uuid = p.uuid', 'left')
            ->join('attribute av', 'av.uuid = at.attribute_uuid', 'left')
            ->where('a.order_id', $data['order_id'])
            ->where('a.is_deleted', 1)
            ->where('a.after_sale_id', $data['after_sale_id'])
            ->select();
        $data->user = User::build()->field('uuid,name,phone')->where(['uuid' => $data['user_uuid']])->find();
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查询详情');
        return $data;
    }


    static public function cmsDelete($request, $userInfo)
    {
        try {
            $data = Order::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->where('status', 1)->findOrFail();
            $data->save(['reason' => $request['reason'], 'status' => 2, 'cancel_type' => 3]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除订单');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }



    static public function after_sale_count($userInfo)
    {
        try {
            $statusList = [1, 2, 3, 4];
            $result = AfterSale::build()
                ->field('status,count(*) as count')
                ->group('status')
                ->select()->toArray();

            // 将结果转为以status为key的数组
            $data = array_column($result, null, 'status');

            // 补全缺失的status
            foreach ($statusList as $status) {
                if (!isset($data[$status])) {
                    $data[$status] = ['status' => $status, 'count' => 0];
                }
            }
            $data = array_values($data);
            usort($data, function ($a, $b) {
                return $a['status'] <=> $b['status'];
            });
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '售后状态统计');
            return $data;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        try {
            Db::startTrans();
            $after_sale = AfterSale::build()->where('after_sale_id', $request['after_sale_id'])->findOrFail();
            if($after_sale->status != 1){
                return ['msg'=>'非待审核状态'];
            }
            if($request['status'] == 2){
                $name = '审核通过';
                $content = ['退款金额'=>'￥'.$request['refund_price']];
                if($after_sale->price < $request['refund_price']){
                    return ['退款金额不能大于售后金额'];
                }
                //退款
                $order = Order::build()->where('order_id', $after_sale['order_id'])->findOrFail();
                if(!$order->trade_no){
                    return ['msg'=>'失败'];
                }

                $refund = new WechatRefund();
                $res = $refund->refund($order->trade_no,$order->order_id,$order->price,-$request['refund_price'],'售后退款');
                if ($res) {
                    //账单
                    Bill::build()->insert([
                        'uuid'=>uuid(),
                        'after_sale_id'=>$request['after_sale_id'],
                        'user_uuid'=>$order['user_uuid'],
                        'bill_id'=>'BX'.getOrderNumber(),
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                        'type'=>5,
                        'price'=>-$request['refund_price'],
                        'site_id'=>$order['site_id'],
                    ]);
                }else{
                    return ['msg'=>'退款失败'];
                }
                //订单状态改为已关闭/已取消
                $order->save(['status' => 5, 'update_time' => date('Y-m-d H:i:s')]);
                //商品规格恢复
                AfterSaleDetail::build()->where('after_sale_id',$request['after_sale_id'])->select()->each(function($item){
                    ProductAttribute::build()->where('uuid',$item['product_attribute_uuid'])->setInc('qty',$item['qty']);
                    Product::build()->where('uuid',$item['product_uuid'])->setInc('qty',$item['qty']);
                    //佣金取消结算
                    CommissionOrder::build()->where('order_id', $item['order_id'])->where('product_attribute_uuid', $item['product_attribute_uuid'])->update(['status' => 3]);
                });
            }else{
                $name = '审核未通过';
                $content = ['拒绝原因'=>$request['refuse_reason']];
            }
            $after_sale->save(array_filter($request));
            //售后跟踪
            AfterSaleLog::build()->save([
                'uuid'=>uuid(),
                'after_sale_id'=>$request['after_sale_id'],
                'name'=>$name,
                'content'=>$content,
                'admin_uuid'=>$userInfo['uuid'],
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time())
            ]);
            $user = User::build()->where('uuid',$after_sale->user_uuid)->find();
            if($user->after_sale_msg == 1){
                //发送订阅消息
                $wechatMsg = new wechatMsg();
                $res = $wechatMsg->handle('gcuxGO0kqAdWMT3S3xnm9ENqgtEauZ0Ga0N6TuaRKGQ','pagesA/refundDetails?id='.$after_sale->after_sale_id,$user->openid,[
                    'character_string1'=>['value'=>$after_sale->after_sale_id],
                    'amount3'=>['value'=>$after_sale->refund_price.'元'],
                    'time4'=>['value'=>now_time(time())],
                    'thing9'=>['value'=>$request['status'] == 2?'您的退款申请已被受理':'您的退款申请已被拒绝'],
                    'thing7'=>['value'=>$request['status'] == 2?'您的退款'.$after_sale->refund_price.'已到账，请查看~':$request['refuse_reason']],
                ]);
                //消息
                if($request['status'] == 2){
                    $content = '您的退款申请（订单'.$after_sale->order_id.'）已审核通过并退款，退款金额：'.$after_sale->refund_price;
                }else{
                    $content = '您的退款申请（订单'.$after_sale->order_id.'）未通过审核，原因：'.$request['refuse_reason'];
                }
                Msg::build()->insert([
                    'uuid'=>uuid(),
                    'after_sale_id' => $after_sale->after_sale_id,
                    'type'=>3,
                    'user_uuid'=>$after_sale->user_uuid,
                    'title'=>'售后进度通知',
                    'content'=>$content,
                    'site_id'=>$after_sale->site_id,
                    'create_time'=>now_time(time()),
                    'update_time'=>now_time(time()),
                ]);
            }
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '售后审核');
            Db::commit();
            return true;
        }catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function setNote($request, $userInfo)
    {
        try {
            $data = AfterSale::build()->where('after_sale_id', $request['after_sale_id'])->where('is_deleted',1)->findOrFail();
            $data->save(['note' => $request['note']]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '添加备注');
            return true;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
