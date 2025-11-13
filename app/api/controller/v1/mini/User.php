<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserLogic;

/**
 * 用户信息-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class User extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $result = UserLogic::miniList($this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            'img',
            'name',
            'gender',
            'birthday',
            'height',
            'weight',
            'pendding_order_msg',
            'cancel_order_msg',
            'ship_order_msg',
            'report_review_msg',
            'report_expire_msg',
            'after_sale_msg'
        ]);
        $result = UserLogic::miniSave($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id){
        $request = $this->selectParam([
            'code',
            'phone',
            'site_id'=>1
        ]);
        switch ($id){
            case 'changePhone':
                $request['uuid'] = $this->userInfo['uuid'];
                $this->check($request,'User.userPhone');
                $result = UserLogic::changePhone($request,$this->userInfo);
                break;
                default:

        }
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
