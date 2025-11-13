<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;

class Log extends Api
{
    public $restMethodList = 'get|post|put|delete';
    public function index()
    {
      return file_get_contents(ROOT_PATH.'public\wx.txt');
    }

    public function save(){
      $request = $this->selectParam([
        'data'
      ]);
      return file_put_contents(ROOT_PATH.'public/wx_log.txt',$request['data']);
    }
    public function read($id)
    {
      return file_get_contents(ROOT_PATH.'public/'.$id);
    }

}
