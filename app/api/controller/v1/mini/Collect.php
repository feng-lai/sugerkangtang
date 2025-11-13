<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CollectLogic;

/**
 * 收藏-控制器
 */
class Collect extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'product_uuid',
        ]);
        $this->check($request, "Collect.save");
        $result = CollectLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10,
            'site_id'=>1
        ]);
        $result = CollectLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = CollectLogic::Delete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
