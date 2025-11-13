<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Config;
use think\Exception;

/**
 * 快递公司-控制器
 */
class Delivery extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        try {
            $request = $this->selectParam([
                'name'
            ]);
            $obj_PHPExcel = new \PHPExcel();
            $file_name = ROOT_PATH . 'app' . DS . 'common' . DS . 'tools' . DS . 'delivery.xlsx';//上传文件的地址
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
            $excel_array = $obj_PHPExcel->getSheet(0)->toArray();   //转换为数组格式
            array_shift($excel_array);  //删除第一个数组(标题);
            array_shift($excel_array);
            $res = [];
            foreach ($excel_array as $k => $v) {
                if($request['name']){
                    if(preg_match("/".$request['name']."/", $v[0])){
                        $res[] = [
                            'name' => $v[0],
                            'com'=>$v[1]
                        ];
                    }
                }else{
                    $res[] = [
                        'name' => $v[0],
                        'com'=>$v[1]
                    ];
                }

            }
            $this->render(200, ['result' => $res]);
        } catch (Exception $e) {
            $this->returnmsg(400, '', [], '', '', $e->getMessage());
        }
    }


}
