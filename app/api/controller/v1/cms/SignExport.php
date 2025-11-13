<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\SignLogic;

/**
 * 签到导出-控制器
 */
class SignExport extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'user_uuid',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = SignLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        $data = [];
        $data[] = ['课程名称', '授课老师', '类型', '签到状态', '开课时间', '签到时间'];
        foreach ($result['data'] as $k => $v) {
            $tmp = [
                $v['name'],
                $v['admin_name'],
                $v['cate_name'],
                $v['is_sign'] == '1'?'已签到':'未签到',
                $v['class_begin'],
                $v['class_begin']
            ];

            foreach ($tmp as $tmp_k => $tmp_v) {
                $tmp[$tmp_k] = $tmp_v . '';
            }
            $data[] = $tmp;
        }

        try {
            $excel = new \PHPExcel();
            $excel_sheet = $excel->getActiveSheet();
            $excel_sheet->fromArray($data);
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

            $file_name = '签到信息.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;

            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            $this->render(200, ['result' => 'upload/' . $file_name]);
        } catch (\Exception $e) {
            unlink($file_path);
            throw new Exception($e->getMessage(), 500);
        }
    }
}
