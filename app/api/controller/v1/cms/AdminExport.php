<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\logic\cms\AdminLogic;
use think\Exception;
use app\common\tools\AliOss;
use app\api\model\AdminLog;

/**
 * 教师导出-控制器
 */
class AdminExport extends Api
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
            'keyword',
            'page_index' => 1,
            'page_size' => 10,
            'college_uuid',
            'disabled'
        ]);
        $result = AdminLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        AdminLog::build()->add($this->userInfo['uuid'], '学生管理','导出');
        $data = ['头像','姓名', '教师工号', '书院','性别','已创建课程','评价','手机号'];

        $resultPHPExcel = new \PHPExcel();
        $objActSheet = $resultPHPExcel->getActiveSheet();
        $row = [
            'A'=>10,
            'B'=>15,
            'C'=>10,
            'D'=>10,
            'E'=>10,
            'F'=>15,
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
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $a, $v['number']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $a, $v['college_name']);
            //性别
            $gender = '未知';
            if($v['gender'] == 1){
                $gender = '男';
            }
            if($v['gender'] == 2){
                $gender = '女';
            }
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $a, $gender);
            $resultPHPExcel->getActiveSheet()->setCellValue('F' . $a, $v['course']);
            $resultPHPExcel->getActiveSheet()->setCellValue('G' . $a, $v['evaluate']);
            $resultPHPExcel->getActiveSheet()->setCellValue('H' . $a, $v['mobile']);
            $a++;
            $objActSheet->getRowDimension($k+2)->setRowHeight(60);
        }
        try{
            $excel_writer = \PHPExcel_IOFactory::createWriter($resultPHPExcel,'Excel2007');
            $file_name = '教师.xlsx';
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
