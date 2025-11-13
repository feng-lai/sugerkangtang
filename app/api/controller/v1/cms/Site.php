<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;

/**
 * -控制器
 */
class Site extends Api
{
    public $restMethodList = 'get|post|put';
    public function index()
    {
        $result = \app\api\model\Site::build()->select();
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
        ]);
        $result = \app\api\model\Site::build()->insert([
            'name'=>$request['name'],
            'create_time'=>now_time(time()),
            'update_time'=>now_time(time()),
        ]);
        $this->render(200, ['result' => $result]);
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
        ]);
        $result = \app\api\model\Site::build()->where('id', $id)->update(['name'=>$request['name']]);
        $this->render(200, ['result' => $result]);
    }
}
