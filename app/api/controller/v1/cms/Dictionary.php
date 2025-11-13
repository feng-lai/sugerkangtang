<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\DictionaryLogic;

/**
 * 字典-控制器
 */
class Dictionary extends Api
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
            'tag',
            'status',
            'dictionary_type_uuid',
            'site_id',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = DictionaryLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = DictionaryLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'dictionary_type_uuid',
            'tag',
            'key',
            'order_number',
            'style',
            'status',
            'note',
            'site_id' => 1,
        ]);
        $this->check($request, "Dictionary.save");
        $result = DictionaryLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'dictionary_type_uuid',
            'tag',
            'key',
            'order_number',
            'style',
            'status',
            'note',
        ]);
        $request['uuid'] = $id;
        $this->check($request, "Dictionary.save");
        $result = DictionaryLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = DictionaryLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
