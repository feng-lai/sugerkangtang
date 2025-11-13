<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\AnalysisLogic;

/**
 * 数据分析-控制器
 */
class Analysis extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function read($id)
    {
        //课程分析
        $request = $this->selectParam([
            'start_time',
            'end_time',
            'college_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'admin_name'
        ]);
        switch ($id){
            case 'course_stat':
                $result = AnalysisLogic::course_stat($request,$this->userInfo);
                break;
            case 'course_trendChart':
                $result = AnalysisLogic::course_trendChart($request,$this->userInfo);
                break;
        }
        //报名分析
        $request = $this->selectParam([
            'college_uuid',
            'course_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'start_time',
            'end_time',
            'cate_uuid'
        ]);
        switch ($id){
            case 'order_stat':
                $result = AnalysisLogic::order_stat($this->userInfo);
                break;
            case 'order_college_trendChart':
                $result = AnalysisLogic::order_college_trendChart($this->userInfo);
                break;
            case 'order_cate_trendChart':
                $result = AnalysisLogic::order_cate_trendChart($this->userInfo);
                break;
            case 'order_trendChart':
                $result = AnalysisLogic::order_trendChart($request,$this->userInfo);
                break;
            case 'order_list':
                $result = AnalysisLogic::order_list($request,$this->userInfo);
                break;
            case 'order_list_export':
                $result = AnalysisLogic::order_list_export($request,$this->userInfo);
                break;
        }

        //学生拼团分析
        $request = $this->selectParam([
            'start_time',
            'end_time',
            'college_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'keyword'
        ]);
        switch ($id){
            case 'user_order_stat':
                $result = AnalysisLogic::user_order_stat($request,$this->userInfo);
                break;
            case 'user_order_trendChart':
                $result = AnalysisLogic::user_order_trendChart($this->userInfo);
                break;
            case 'user_order_list':
                $result = AnalysisLogic::user_order_list($request,$this->userInfo);
                break;
            case 'user_order_list_export':
                $result = AnalysisLogic::user_order_list_export($request,$this->userInfo);
                break;
        }

        //教师授课分析
        switch ($id){
            case 'admin_course_stat':
                $result = AnalysisLogic::admin_course_stat($request,$this->userInfo);
                break;
            case 'admin_course_trendChart':
                $result = AnalysisLogic::admin_course_trendChart($this->userInfo);
                break;
            case 'admin_course_list':
                $result = AnalysisLogic::admin_course_list($request,$this->userInfo);
                break;
            case 'admin_course_list_export':
                $result = AnalysisLogic::admin_course_list_export($request,$this->userInfo);
                break;
        }

        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
