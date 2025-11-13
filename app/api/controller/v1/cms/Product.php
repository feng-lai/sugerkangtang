<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ProductLogic;

/**
 * 商品-控制器
 */
class Product extends Api
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
            'keyword',
            'status',
            'site_id',
            'start_time',
            'end_time',
            'recommend',
            'recommend_start_time',
            'recommend_end_time',
            'price_min',
            'price_max',
            'site_id',
            'category_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = ProductLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ProductLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'img',
            'code',
            'main_img',
            'video',
            'category_uuid',
            'selling_point',
            'after_sale_day',
            'is_after_sale',
            'qty',
            'price',
            'original_price',
            'is_original_price',
            'desc',
            'site_id'=>1,
            'parameter',
            'attribute',
            'effect',
            'suitable_for',
            'product_type',
        ]);
        $this->check($request, "Product.save");
        $result = ProductLogic::cmsAdd($request, $this->userInfo);
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
            'img',
            'code',
            'main_img',
            'video',
            'category_uuid',
            'selling_point',
            'after_sale_day',
            'is_after_sale',
            'qty',
            'price',
            'original_price',
            'is_original_price',
            'desc',
            'parameter',
            'attribute',
            'effect',
            'suitable_for',
            'product_type',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Product.save");
        $result = ProductLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = ProductLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
