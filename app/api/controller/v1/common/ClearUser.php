<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\ClearUserLogic;
use app\api\model\Contestant;
use app\api\model\User;
use app\api\model\UserToken;

/**
 * 清理用户信息-控制器
 * User: Yacon
 * Date: 2023-04-06
 * Time: 18:09
 */
class ClearUser extends Api
{
  public $restMethodList = 'get|post|put|delete';


  // public function _initialize()
  // {
  //   parent::_initialize();
  //   $this->userInfo = $this->commonValidateToken();
  // }

  // public function index(){
  //   $request = $this->selectParam([
  //     'page_index'=>1,      // 当前页码
  //     'page_size'=>10,      // 每页条目数
  //     'keyword_search'=>'', // 关键词
  //     'start_time'=>'',     // 开始时间
  //     'end_time'=>'',        // 结束时间
  //     'is_page'=>1,        // 是否分页 1=分页 2=不分页
  //   ]);
  //   $this->check($request,"ClearUser.list");
  //   if($request['is_page'] == 1){
  //     $result = ClearUserLogic::commonPage($request,$this->userInfo);
  //   }
  //   else{
  //     $result = ClearUserLogic::commonList($request,$this->userInfo);
  //   }
  //   $this->render(200,['result' => $result]);
  // }

  public function read($id)
  {
    $user = User::build()->where(['mobile' => $id])->find();

    UserToken::build()->where(['user_uuid' => $user['uuid']])->delete();
    User::build()->where(['uuid' => $user['uuid']])->delete();
    Contestant::build()->where(['user_uuid' => $user['uuid']])->delete();
    Contestant::build()->where(['mobile' => $id])->delete();
    $this->render(200, ['result' => true]);
  }

  // public function save(){
  //   $request = $this->selectParam([]);
  //   $this->check($request,"ClearUser.save");
  //   $result = ClearUserLogic::commonAdd($request,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }

  // public function update($id)
  // {
  //   $request['uuid'] = $id;

  //   $user = User::build()->where(['mobile' => $id])->find();
  //   UserToken::build()->where(['user_uuid' => $user->uuid])->delete();
  //   User::build()->where(['uuid' => $user->uuid])->delete();
  //   Contestant::build()->where(['user_uuid' => $user->uuid])->delete();
  //   $this->render(200, ['result' => true]);
  // }

  // public function delete($id){
  //   $result = ClearUserLogic::commonDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
