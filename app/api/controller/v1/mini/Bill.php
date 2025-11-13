<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\BillLogic;

/**
 * 账单-控制器
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class Bill extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_index' => 1, // 当前页码
            'page_size' => 10, // 每页条目数
            'status',
            'site_id'=>1,
            'start_time',
            'end_time',
        ]);
        $result = BillLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    /*
    public function save()
    {
      $request = $this->selectParam([
        'price', // 金额
        'type', // 提现方式
      ]);
      $this->check($request, "CashOut.save");
      $result = CashOutLogic::miniAdd($request, $this->userInfo);
      if (isset($result['msg'])) {
        $this->returnmsg(400, [], [], '', '', $result['msg']);
      } else {
        $this->render(200, ['result' => $result]);
      }
    }*/

    // public function update($id){
    //   $request = $this->selectParam([]);
    //   $request['uuid'] = $id;
    //   $result = UserLogic::miniEdit($request,$this->userInfo);
    //   if (isset($result['msg'])) {
    //     $this->returnmsg(400, [], [], '', '', $result['msg']);
    //   } else {
    //     $this->render(200, ['result' => $result]);
    //   }
    // }

    // public function delete($id){
    //   $result = UserLogic::miniDelete($id,$this->userInfo);
    //   if (isset($result['msg'])) {
    //     $this->returnmsg(400, [], [], '', '', $result['msg']);
    //   } else {
    //     $this->render(200, ['result' => $result]);
    //   }
    // }
}
