<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 标签-控制器
 */
class Tag extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $result = \app\api\model\Tag::build()->field('name')->order('sort','asc')->where(['is_deleted'=>1])->select();
        $this->render(200, ['result' => $result]);

    }
}
