<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 商品参数/功效/适用人群/产品类型-控制器
 */
class Parameter extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $request = $this->selectParam([
            'site_id',
            'type'
        ]);
        $where = [
            'is_deleted'=>1,
            'status'=>1
        ];
        $request['site_id']?$where['site_id']=$request['site_id']:'';
        $request['type']?$where['type']=$request['type']:'';
        $result = \app\api\model\Parameter::build()->order('create_time desc')->where($where)->select();
        $this->render(200, ['result' => $result]);
    }

}
