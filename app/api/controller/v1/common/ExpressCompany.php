<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\ExpressCompanyLogic;

/**
 * 快递100-快递公司-控制器
 * User: Yacon
 * Date: 2022-08-31
 * Time: 17:19
 */
class ExpressCompany extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function index()
  {
    $request = $this->selectParam([
      'state' => 1, // 状态 1=显示 2=隐藏
    ]);
    $result = ExpressCompanyLogic::miniList($request, $this->userInfo);
    $this->render(200, ['result' => $result]);
  }

  // public function read($id)
  // {
  //   $result = ExpressCompanyLogic::miniDetail($id, $this->userInfo);
  //   $this->render(200, ['result' => $result]);
  // }

  // public function save(){
  //   $request = $this->selectParam([]);
  //   $this->check($request,"ExpressCompany.save");
  //   $result = ExpressCompanyLogic::miniAdd($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function update($id){
  //   $request = $this->selectParam([]);
  //   $request['uuid'] = $id;
  //   $this->check($request,"ExpressCompany.edit");
  //   $result = ExpressCompanyLogic::miniEdit($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function delete($id){
  //   $result = ExpressCompanyLogic::miniDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
