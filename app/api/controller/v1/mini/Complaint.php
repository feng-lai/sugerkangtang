<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\ComplaintLogic;

/**
 * 投诉建议-控制器
 */
class Complaint extends Api
{
    public $restMethodList = 'post|get';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index(){
        $request = $this->selectParam([
            'page_index',
            'page_size'
        ]);
        $result = ComplaintLogic::List($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            'content',
            'img',
            'type',
            'anonymous'
        ]);
        $this->check($request, "Complaint.save");
        $result = ComplaintLogic::Add($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
