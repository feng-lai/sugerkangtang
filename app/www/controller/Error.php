<?php
namespace app\www\controller;

use think\Controller;
use think\Request;

class Error extends Controller
{
    public function index()
    {
        $header = [];
        http_response_code(405);
        $error['error']['reason']="Method Not Allowed ";
        $error['error']['message'] = "资源请求类型有误";
        foreach ($header as $name => $val) {
            if (is_null($val)) {
                header($name);
            } else {
                header($name . ':' . $val);
            }
        }
        exit(json_encode($error,JSON_UNESCAPED_UNICODE));
//        return json(array('error'=>405,'message'=>'No routing path can be found for the request1.'));
    }
}