<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ProductAttributeLogic;

/**
 * 商品-规格-控制器
 */
class ProductAttribute extends Api
{
    public $restMethodList = 'get|put';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'keyword',
            'category_uuid',
            'qty_min',
            'qty_max',
            'page_size'=>10,
            'page_index'=>1,
            'site_id',
            'is_qty_danger'
        ]);
        $result = ProductAttributeLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }


    public function update($id)
    {
        $request = $this->selectParam([
            'qty',
            'qty_danger',
        ]);
        $request['uuid'] = $id;
        $result = ProductAttributeLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }



}
