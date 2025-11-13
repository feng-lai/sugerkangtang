<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\AfterSaleLogic;

/**
 * 售后跟踪-控制器
 */
class AfterSaleLog extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'after_sale_id',
        ]);
        $result = \app\api\model\AfterSaleLog::build()->field('name,content,create_time')->where('after_sale_id', $request['after_sale_id'])->order('create_time asc')->select();
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }



}
