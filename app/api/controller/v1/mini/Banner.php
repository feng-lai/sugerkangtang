<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 轮播-控制器
 */
class Banner extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $request = $this->selectParam([
            'site_id',
        ]);
        $where = [
            'is_deleted'=>1,
            'status'=>1
        ];
        $request['site_id']?$where['site_id']=$request['site_id']:'';
        $result = \app\api\model\Banner::build()->order('order_number','asc')->where($where)->select();
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = \app\api\model\Banner::build()->where(['uuid'=>$id])->find();
        $this->render(200, ['result' => $result]);
    }
}
