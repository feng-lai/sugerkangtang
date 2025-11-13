<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AfterSaleLogic;

/**
 * 售后备注-控制器
 */
class AfterSaleSetNote extends Api
{
    public $restMethodList = 'put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }


    public function update($id)
    {
        $request = $this->selectParam([
            "note",
        ]);
        $request['after_sale_id'] = $id;
        $this->check($request,'AfterSale.setNote');
        $result = AfterSaleLogic::setNote($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
