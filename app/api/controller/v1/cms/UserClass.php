<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\model\User;

/**
 * 班级-控制器
 */
class UserClass extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = User::build()->where('is_deleted',1)->whereNotNull('class')->where('class','<>','')->group('class')->column('class');

        $this->render(200, ['result' => $result]);

    }

}
