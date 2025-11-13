<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ChannelLogic;
/**
 * 渠道商-控制器
 */
class Channel extends Api
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
            'region_uuid',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = ChannelLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = ChannelLogic::cmsDetail($id, $this->userInfo);
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
            'recommend_name',
            'uname',
            'password'=>'123456',
            'note',
            'region_uuid',
            'retail_uuid',
            'site_id' => 1,
        ]);
        $this->check($request, "Channel.save");
        $result = ChannelLogic::cmsAdd($request, $this->userInfo);
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
            'bank_number',
            'note',
            'recommend_name',
            'uname',
            'password',
            'region_uuid',
            'retail_uuid',
            'site_id' => 1,
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Channel.save");
        $result = ChannelLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = ChannelLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
