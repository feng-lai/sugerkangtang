<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\model\AdminLog;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\OrderLogic;

/**
 * 报名导出-控制器
 */
class OrderExport extends Api
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
            'course_uuid',
            'is_sign',
            'status'
        ]);
        $result = OrderLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        $data = ['活动封面','活动名称', '书院', '专业','手机号','活动状态','签到情况','签到时间'];

        $resultPHPExcel = new \PHPExcel();
        $objActSheet = $resultPHPExcel->getActiveSheet();
        $row = [
            'A'=>10,
            'B'=>10,
            'C'=>10,
            'D'=>10,
            'E'=>10,
            'F'=>10,
            'G'=>15,
            'H'=>15
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
            //1=拼课中 2=待开课 3=已完成 4评课取消
            switch ($v['status']){
                case 1:
                    $text = '拼课中';
                    break;
                case 2:
                    $text = '待开课';
                    break;
                case 3:
                    $text = '已完成';
                    break;
                case 4:
                    $text = '评课取消';
                    break;
                default:
                    break;
            }


            if(file_exists(ROOT_PATH . 'public' . DS . $v['img']) && $v['img']){
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $img_url = ROOT_PATH . 'public' . DS . $v['img'];
                $objDrawing->setPath($img_url);//这里拼接 . 是因为要在根目录下获取
                // 设置宽度高度
                $objDrawing->setHeight(50);//照片高度
                $objDrawing->setWidth(50); //照片宽度
                /*设置图片要插入的单元格*/
                $objDrawing->setCoordinates('B' . $a);
                // 图片偏移距离
                $objDrawing->setOffsetX(15);
                $objDrawing->setOffsetY(15);
                $objDrawing->setWorksheet($resultPHPExcel->getActiveSheet());
            }else{
                $resultPHPExcel->getActiveSheet()->setCellValue('A' . $a, $v['img']);
            }

            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $a, $v['name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $a, $v['college_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $a, $v['major']);
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $a, $v['mobile'].' ');
            $resultPHPExcel->getActiveSheet()->setCellValue('F' . $a, $text);
            $resultPHPExcel->getActiveSheet()->setCellValue('G' . $a, $v['is_sign'] == 1?'已签到':'未签到');
            $resultPHPExcel->getActiveSheet()->setCellValue('H' . $a, $v['sign_time']);
            $a++;
            $objActSheet->getRowDimension($k+2)->setRowHeight(60);
        }


        try {
            $excel_writer = \PHPExcel_IOFactory::createWriter($resultPHPExcel,'Excel2007');
            $file_name = '活动信息.xlsx';
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
