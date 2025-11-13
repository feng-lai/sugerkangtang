<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;

/**
 * 字典-控制器
 */
class DictionaryCountry extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = \app\api\model\Dictionary::build()->where('type', 'country')->column('tag');
        $this->render(200, ['result' => $result]);
    }


}
