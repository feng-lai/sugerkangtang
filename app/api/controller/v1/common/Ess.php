<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\EssLogic;

/**
 * 腾讯电子签-控制器
 * User: Yacon
 * Date: 2023-03-30
 * Time: 21:24
 */
class Ess extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->miniValidateToken();
  }

  // public function index(){
  //   $request = $this->selectParam([
  //     'page_index'=>1,      // 当前页码
  //     'page_size'=>10,      // 每页条目数
  //     'keyword_search'=>'', // 关键词
  //     'start_time'=>'',     // 开始时间
  //     'end_time'=>'',        // 结束时间
  //     'is_page'=>1,        // 是否分页 1=分页 2=不分页
  //   ]);
  //   $this->check($request,"Ess.list");
  //   if($request['is_page'] == 1){
  //     $result = EssLogic::commonPage($request,$this->userInfo);
  //   }
  //   else{
  //     $result = EssLogic::commonList($request,$this->userInfo);
  //   }
  //   $this->render(200,['result' => $result]);
  // }

  // public function read($id){
  //   $result = EssLogic::commonDetail($id,$this->userInfo);
  //   $this->render(200,['result' => $result]);
  // }

  public function save()
  {
    $request = $this->selectParam([
      'uuid' => '', //选手UUID
    ]);
    $result = EssLogic::commonAdd($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

  // public function update($id){
  //   $request = $this->selectParam([]);
  //   $request['uuid'] = $id;
  //   $this->check($request,"Ess.edit");
  //   $result = EssLogic::commonEdit($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function delete($id){
  //   $result = EssLogic::commonDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
