<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ProducerLogic;

/**
 * 出品方-控制器
 */
class Producer extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'keyword',
            'status',
            'site_id',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = ProducerLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ProducerLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'contact_name',
            'phone',
            'address',
            'address_detail',
            'bank',
            'bank_number',
            'uname',
            'password'=>'123456',
            'note',
            'recommend_name',
            'partner_uuid',
            'site_id' => 1,
        ]);
        $this->check($request, "Producer.save");
        $result = ProducerLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'contact_name',
            'phone',
            'address',
            'address_detail',
            'bank',
            'uname',
            'password',
            'bank_number',
            'recommend_name',
            'note',
            'partner_uuid',
            'site_id' => 1,
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Producer.save");
        $result = ProducerLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = ProducerLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
