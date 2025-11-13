<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\RegionLogic;

/**
 * 地区-控制器
 * User: Yacon
 * Date: 2022-02-16
 * Time: 22:54
 */
class Region extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function index()
  {
    $request = $this->selectParam([
      'parent_id' => -1,     // 父级UUID
      'tree' => 0, // 1=获取树结构
    ]);
    $result = RegionLogic::miniList($request);
    $this->render(200, ['result' => $result]);
  }

  // public function read($id){
  //   $result = RegionLogic::miniDetail($id,$this->userInfo);
  //   $this->render(200,['result' => $result]);
  // }

  // public function save(){
  //   $request = $this->selectParam([]);
  //   $this->check($request,"Region.save");
  //   $result = RegionLogic::miniAdd($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function update($id){
  //   $request = $this->selectParam([]);
  //   $request['uuid'] = $id;
  //   $this->check($request,"Region.edit");
  //   $result = RegionLogic::miniEdit($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function delete($id){
  //   $result = RegionLogic::miniDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
