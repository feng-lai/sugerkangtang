<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\FeelLogic;

/**
 * 心得导出-控制器
 */
class FeelExport extends Api
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
            'page_index' => 1,
            'admin_uuid',
            'course_name'
        ]);
        $result = FeelLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        $data = [];
        $data[] = ['课程名称', '授课老师', '类型', '学习心得', '审核状态', '提交时间'];
        foreach ($result['data'] as $k => $v) {
            $text = '';
            if($v['status'] == 0 && $v['status']  !== null){
                $text = '待审核';
            }
            if($v['status'] == 1){
                $text = '已通过';
            }
            if($v['status'] == 2){
                $text = '已拒绝';
            }
            $tmp = [
                $v['name'],
                $v['admin_name'],
                $v['cate_name'],
                $v['is_feel'] == '1'?'已提交':'未提交',
                $text,
                $v['is_feel'] == '1'?$v['create_time']:''
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

            $file_name = '学生'.\app\api\model\User::build()->where('uuid',$request['user_uuid'])->value('name').'提交的心得_'.time().'.xlsx';
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
