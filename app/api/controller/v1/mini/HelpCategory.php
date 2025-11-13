<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\ProblemLogic;

/**
 * 帮助手册-分类-控制器
 */
class HelpCategory extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $request = $this->selectParam([
            'site_id' => 1,
            'type'
        ]);
        $where = [
            'site_id' => $request['site_id'],
            'is_deleted'=>1
        ];
        $request['type']?$where['type'] = $request['type']:'';
        $result = \app\api\model\HelpCategory::build()
            ->where($where)
            ->select();
        $this->render(200, ['result' => $result]);
    }


}
