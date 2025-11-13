<?php
  namespace app\api\controller\v1\common;
  use app\api\controller\Api;
  use think\Exception;
  use app\api\logic\common\EssCallbackLogic;

  /**
   * 腾讯电子签-回调-控制器
   * User: Yacon
   * Date: 2023-03-31
   * Time: 16:41
   */
  class EssCallback extends Api
  {
      public $restMethodList = 'get|post|put|delete';

      public function save(){
        $params = file_get_contents('php://input');
        $headers = getallheaders();


        $result = EssCallbackLogic::commonAdd($params,$headers);
        if (isset($result['msg'])) {
          $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
          $this->render(200, ['result' => $result]);
        }
      }
  }