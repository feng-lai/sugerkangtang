<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 商品分类-控制器
 */
class Category extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'site_id'=>1
        ]);
        $result = \app\api\model\Category::build()->field('uuid,name,vis,img')->where('is_deleted',1)->where('vis',1)->where('site_id',$request['site_id'])->order('order_number desc')->select();
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $this->userInfo = $this->miniValidateToken2();
        $result = ProductLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
