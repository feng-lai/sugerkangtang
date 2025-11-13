<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AfterSaleStatLogic;

/**
 * 售后统计-控制器
 */
class AfterSaleStat extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function read($id)
    {
        $request = $this->selectParam([
            'page_size'=>10,
            'page_index'=>1,
            'type',
            'start_time',
            'end_time',
            'orderBy',
            'sort',
            'site_id'=>1
        ]);
        switch ($id) {
            case 'price_num':
                $result = AfterSaleStatLogic::price_num($request,$this->userInfo);
                break;
            case 'analyze':
                $result = AfterSaleStatLogic::analyze($request,$this->userInfo);
                break;
            case 'reason':
                $result = AfterSaleStatLogic::reason($request,$this->userInfo);
                break;
            case 'ranking':
                $result = AfterSaleStatLogic::ranking($request,$this->userInfo);
            default:
        }
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
