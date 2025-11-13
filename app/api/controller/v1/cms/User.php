<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\UserLogic;

/**
 * 学生-控制器
 */
class User extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_index' => 1, // 当前页码
            'page_size' => 10, // 每页条目数
            'keyword',
            'last_login_time',
            'create_time',
            'gender' ,
            'disabled',
            'is_retail',
            'site_id'=>1
        ]);
        $result = UserLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = UserLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    // public function save()
    // {
    //   $request = $this->selectParam([]);
    //   $result = UserLogic::cmsAdd($request, $this->userInfo);
    //   if (isset($result['msg'])) {
    //     $this->returnmsg(400, [], [], '', '', $result['msg']);
    //   } else {
    //     $this->render(200, ['result' => $result]);
    //   }
    // }

    public function update($id)
    {
        $request = $this->selectParam([
            'disabled' // 是否启用 1=启用 2=禁用
        ]);
        $request['uuid'] = $id;
        $result = UserLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    // public function delete($id)
    // {
    //   $result = UserLogic::cmsDelete($id, $this->userInfo);
    //   if (isset($result['msg'])) {
    //     $this->returnmsg(400, [], [], '', '', $result['msg']);
    //   } else {
    //     $this->render(200, ['result' => $result]);
    //   }
    // }
}
