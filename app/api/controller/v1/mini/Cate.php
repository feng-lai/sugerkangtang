<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CateLogic;

/**
 * 分类-控制器
 */
class Cate extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'name',
            'page_size' => 10,
            'page_index' => 1,
            'home_vis',
            'pid',
            'level'
        ]);
        $result = CateLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
