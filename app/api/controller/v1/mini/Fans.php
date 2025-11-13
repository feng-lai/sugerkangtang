<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\FansLogic;

/**
 * 粉丝-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class Fans extends Api
{
  public $restMethodList = 'get';


  public function _initialize()
  {
    parent::_initialize();

  }

  public function index()
  {
    $request = $this->selectParam([
      'page_index' => 1, // 当前页码
      'page_size' => 10, // 每页条目数
      'type',
      'contestant_uuid'//选手uuid
    ]);
    $result = FansLogic::cmsList($request);
    $this->render(200, ['result' => $result]);
  }



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
