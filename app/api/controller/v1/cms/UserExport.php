<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\UserLogic;
use think\Exception;
use app\common\tools\AliOss;
use app\api\model\AdminLog;

/**
 * 学生导出-控制器
 */
class UserExport extends Api
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
            'page_index' => 1, // 当前页码
            'page_size' => 10, // 每页条目数
            'name' => '',
            'college_uuid' => '',
            'grade' => '',
            'class' => '',
            'number' => '',
            'sign_min' => '',
            'sign_max' => '',
            'disabled'=>1
        ]);
        $result = UserLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        AdminLog::build()->add($this->userInfo['uuid'], '学生管理','导出');
        $data = ['姓名', '学号', '书院','年级','专业','签到统计','报名统计'];

        $resultPHPExcel = new \PHPExcel();
        $objActSheet = $resultPHPExcel->getActiveSheet();
        $row = [
            'A'=>10,
            'B'=>10,
            'C'=>10,
            'D'=>10,
            'E'=>10,
            'F'=>10,
            'G'=>15
        ];
        $i = 0;
        foreach($row as $k=>$v){
            $i++;
            // 水平居中（位置很重要，建议在最初始位置）
            $resultPHPExcel->setActiveSheetIndex(0)->getStyle($k)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            // 设置个表格宽度
            $resultPHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($v);
            // 垂直居中
            $resultPHPExcel->setActiveSheetIndex(0)->getStyle($k)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设值
            $resultPHPExcel->getActiveSheet()->setCellValue($k.'1', $data[$i-1]);
        }

        $a  = 2;
        foreach($result['data'] as $k=>$v){
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $a, $v['name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $a, $v['number'].' ');
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $a, $v['college_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $a, $v['grade']);
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $a, $v['major']);
            $resultPHPExcel->getActiveSheet()->setCellValue('F'.$a, "已签到：{$v['sign_num']}\n未签到：{$v['no_sign']}");
            $resultPHPExcel->getActiveSheet()->setCellValue('G'.$a, "已成团：{$v['order_finish_num']}\n未成团：{$v['order_num']}");
            foreach (['F', 'G'] as $col) {
                $resultPHPExcel->getActiveSheet()
                    ->getStyle($col.$a)
                    ->getAlignment()
                    ->setWrapText(true);
            }
            $a++;
            $objActSheet->getRowDimension($k+2)->setRowHeight(60);

        }
        try{
            $excel_writer = \PHPExcel_IOFactory::createWriter($resultPHPExcel,'Excel2007');
            $file_name = '【北理团团】学生列表批量导出_'.time().'.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;

            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            $this->render(200, ['result' => 'upload/' . $file_name]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
