<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\model\Bill;
use app\api\model\Order;
use app\common\tools\AliOss;
use think\Exception;

/**
 * 平台流水导出-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class BillExport extends Api
{
  public $restMethodList = 'get';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'cost_type',
      'type',
      'page_size'=>10,
      'page_index'=>1,
      'start_time',
      'end_time',
      'pay_type'
    ]);
    $result = Bill::build()->field('bill_sn,type,create_time,price,coins,pay_type,order_sn');
    if($request['type']) $result = $result->where('type',$request['type']);
    if($request['pay_type']) $result = $result->where('pay_type',$request['pay_type']);
    if($request['start_time']) $result = $result->where('create_time','>=',$request['start_time']);
    if($request['end_time']) $result = $result->where('create_time','<=',$request['end_time'].'24:00:00');
    if($request['cost_type']){
      if($request['cost_type'] == 1){
        //收入
        $result = $result->where('type','in','1,8')->where('pay_type','in',[2,3,4]);
      }elseif ($request['cost_type'] == 2){
        //支出
        $result = $result->where('type','in','4,9');
      }else{
        throw new Exception('cost_type只能为1或2', 500);
      }
    }
    $result = $result->where('type','in',[1,4,8,9])->where('pay_type','in',[2,3,4])->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    foreach($result as $v){
      if(in_array($v->type,[1,8])){
        $v->cost_type = 1;
      }else{
        $v->cost_type = 2;
      }
      if($v->order_sn){
        $v->trade_no = Order::build()->where('order_sn',$v->order_sn)->value('trade_no');
      }else{
        $v->trade_no = '';
      }
    }
    $data = [];
    $data[] = ['流水号', '收支类型', '流水类型','支付渠道','凭证号','时间','金额'];
    foreach ($result as $k => $v) {
      //类型 1充值  4提现 8商城订单 9售后退款
      $text = '';
      switch ($v->type){
        case 1:
          $text = '星豆充值(收入)';
          break;
        case 4:
          $text = '钱包提现(支出)';
          break;
        case 8:
          $text = '商城订单(收入)';
          break;
        case 9:
          $text = '售后退款(支出)';
          break;
      }
      $pay_text = '';// 2微信 3支付宝 4通联支付
      if($v->pay_type == 2){
        $pay_text = '微信支付';
      }elseif($v->pay_type == 3){
        $pay_text = '支付宝';
      }elseif($v->pay_type == 4){
        $pay_text = '通联支付';
      }
      $tmp = [
        $v->bill_sn.' ',
        $v->cost_type == 1?'收入':'支出',
        $text,
        $pay_text,
        $v->trade_no.' ',
        $v->create_time,
        $v->price
      ];

      foreach ($tmp as $tmp_k => $tmp_v) {
        $tmp[$tmp_k] = $tmp_v.'';
      }
      $data[] = $tmp;
    }

    try{
      $excel = new \PHPExcel();
      $excel_sheet = $excel->getActiveSheet();
      $excel_sheet->fromArray($data);
      $excel_writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');

      $file_name = '平台流水.xlsx';
      $file_path = ROOT_PATH .$file_name;
      $excel_writer->save($file_path);

      if (!file_exists($file_path)) {
        throw new \Exception("Excel生成失败");
      }
      //$result = uploadFileExcel($file_name,$file_path,'match_service/excel/');
      $oss = new AliOss();
      $oss->uploadOss($file_path, 'match_service/excel/'.$file_name);
      unlink($file_path);
      $this->render(200, ['result' => 'match_service/excel/'.$file_name]);
    } catch (\Exception $e) {
      unlink($file_path);
      throw new Exception($e->getMessage(), 500);
    }

  }
}
