<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\model\User;

/**
 * 年级-控制器
 */
class Grade extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = User::build()->where('is_deleted',1)->whereNotNull('grade')->where('grade','<>','')->group('grade')->column('grade');

        $this->render(200, ['result' => $result]);

    }

}
