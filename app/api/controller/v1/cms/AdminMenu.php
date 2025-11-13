<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminMenuLogic;

/**
 * 后台菜单-控制器
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminMenu extends Api
{
    public $restMethodList = 'get|put|post|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'pid',
            'level' => 1,
            'page_size' => 10,
            'page_index' => 1,
            'vis',
            'status',
            'type',
            'name'
        ]);
        $result = AdminMenuLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = AdminMenuLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'url',
            'pid' => '',
            'level' => 1,
            'type',
            'status',
            'vis',
        ]);
        $this->check($request, "AdminMenu.save");
        $result = AdminMenuLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'url',
            'pid',
            'level',
            'type',
            'status',
            'vis',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "AdminMenu.edit");
        $result = AdminMenuLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = AdminMenuLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
