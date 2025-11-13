<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\api\logic\common\DdLoginLogic;
use think\Exception;

/**
 * 钉钉免登-控制器
 */
class DdLogin extends Api
{
    public $restMethodList = 'post';

    public function save()
    {
        $request = $this->selectParam([
            'code'
        ]);
        if(!$request['code']){
            $this->returnmsg(400, [], [], '', '', '免登授权码不能为空');
        }
        $result = DdLoginLogic::add($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
