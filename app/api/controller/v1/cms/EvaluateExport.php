<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\model\AdminLog;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\EvaluateLogic;

/**
 * 评价导出-控制器
 */
class EvaluateExport extends Api
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
            'admin_uuid',
            'page_size'=>10,
            'page_index'=>1,
        ]);
        $result = EvaluateLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        AdminLog::build()->add($this->userInfo['uuid'], '评价管理','导出');
        $data = ['活动名称', '评价学生', '是否匿名', '评价星级', '评价内容','评价图片','评价时间'];

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
            $img = explode(',',$v['img']);
            if(file_exists(ROOT_PATH . 'public' . DS . $img[0]) && $v['img']){
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $img_url = ROOT_PATH . 'public' . DS . $img[0];
                $objDrawing->setPath($img_url);//这里拼接 . 是因为要在根目录下获取
                // 设置宽度高度
                $objDrawing->setHeight(50);//照片高度
                $objDrawing->setWidth(50); //照片宽度
                /*设置图片要插入的单元格*/
                $objDrawing->setCoordinates('F' . $a);
                // 图片偏移距离
                $objDrawing->setOffsetX(15);
                $objDrawing->setOffsetY(15);
                $objDrawing->setWorksheet($resultPHPExcel->getActiveSheet());
            }else{
                $resultPHPExcel->getActiveSheet()->setCellValue('F' . $a, $v['img']);
            }

            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $a, $v['name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $a, $v['uname'].' ');
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $a, $v['anonymous'] == 1?'是':'否');
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $a, $v['point']);
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $a, $v['content']);
            $resultPHPExcel->getActiveSheet()->setCellValue('G' . $a, $v['create_time']);
            $a++;
            $objActSheet->getRowDimension($k+2)->setRowHeight(60);
        }

        try {
            $excel_writer = \PHPExcel_IOFactory::createWriter($resultPHPExcel,'Excel2007');

            $file_name = '评价列表.xlsx';
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
