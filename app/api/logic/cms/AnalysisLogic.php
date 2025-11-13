<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Course;
use app\api\model\College;
use app\api\model\Evaluate;
use app\api\model\Feel;
use app\api\model\Admin;
use app\api\model\Order;
use app\api\model\Sign;
use app\api\model\User;
use app\common\tools\AliOss;
use think\Exception;
use think\Db;

/**
 * 数据分析逻辑
 */
class AnalysisLogic
{

    static public function course_stat($request, $userInfo)
    {
        try {
            $where['c.is_deleted'] = 1;
            $where['c.status'] = ['<>', 4];
            if ($request['start_time'] && $request['end_time']) {
                $where['c.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['c.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['c.create_time'] = ['<=',$request['end_time']];
            }
            if ($request['admin_name']) {
                $where['a.name|a.number'] = ['like', '%' . $request['admin_name'] . '%'];
            }
            $data1 = Course::build()->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where($where);
            if ($request['college_uuid']) {
                $data1 = $data1->where("FIND_IN_SET('{$request['college_uuid']}', c.college)");
            }
            $data1 = $data1->count();

            $data2 = Course::build()->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where($where)->where('c.status', 1);
            if ($request['college_uuid']) {
                $data2 = $data2->where("FIND_IN_SET('{$request['college_uuid']}', c.college)");
            }
            $data2 = $data2->count();

            $data3 = Course::build()->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where($where)->where('c.status', 2);
            if ($request['college_uuid']) {
                $data3 = $data3->where("FIND_IN_SET('{$request['college_uuid']}', c.college)");
            }
            $data3 = $data3->count();

            $data4 = Course::build()->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where($where)->where('c.status', 3);
            if ($request['college_uuid']) {
                $data4 = $data4->where("FIND_IN_SET('{$request['college_uuid']}', c.college)");
            }
            $data4 = $data4->count();

            $data = [
                'course_all' => $data1,
                'course_sign' => $data2,
                'course_begin' => $data3,
                'course_end' => $data4,
            ];

            AdminLog::build()->add($userInfo['uuid'], '数据分析', '拼团数据分析-统计', $request);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function course_trendChart($request, $userInfo)
    {
        try {
            $where['c.is_deleted'] = 1;
            $where['c.status'] = ['<>', 4];
            if ($request['admin_name']) {
                $where['a.name|a.number'] = ['like', '%' . $request['admin_name'] . '%'];
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']));
            $course_all = [];//课程总数
            $course_sign = []; //拼课中
            $course_begin = []; //待开课
            $course_end = []; //已结束

            foreach ($list_date['data'] as $v) {
                if ($list_date['type'] == 1) {
                    $where['c.create_time'] = ['BETWEEN', [$v, $v . ' 23:59:59']];
                }
                if ($list_date['type'] == 2) {
                    // 计算该月的第一天
                    $firstDayOfMonth = date("Y-m-01", strtotime($v));
                    // 计算该月的最后一天
                    $lastDayOfMonth = date("Y-m-t", strtotime($v));
                    $where['c.create_time'] = ['BETWEEN', [$firstDayOfMonth, $lastDayOfMonth . ' 23:59:59']];
                }
                if ($list_date['type'] == 3) {
                    // 计算该年的第一天
                    $firstDayOfMonth = $v . '-01-01';
                    // 计算该年的最后一天
                    $lastDayOfMonth = $v . '-12-31 23:59:59';
                    $where['c.create_time'] = ['BETWEEN', [$firstDayOfMonth, $lastDayOfMonth]];
                }
                $course_all[] = Course::build()->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where($where)->count();
                $course_sign[] = Course::build()->where($where)->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where('c.status', 1)->count();
                $course_begin[] = Course::build()->where($where)->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where('c.status', 2)->count();
                $course_end[] = Course::build()->where($where)->alias('c')->join('admin a', 'a.uuid = c.admin_uuid')->where('c.status', 3)->count();
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '拼团数据分析-趋势', $request);
            return ['date_time' => $list_date['data'], 'course_all' => $course_all, 'course_sign' => $course_sign, 'course_begin' => $course_begin, 'course_end' => $course_end];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function order_stat($userInfo)
    {
        try {
            $all = Order::build()->where('status', 1)->where('is_deleted', 1)->count();
            $month = Order::build()->where('status', 1)->where('is_deleted', 1)->whereTime('create_time', 'm')->count();
            $week = Order::build()->where('status', 1)->where('is_deleted', 1)->whereTime('create_time', 'w')->count();
            $day = Order::build()->where('status', 1)->where('is_deleted', 1)->whereTime('create_time', 'd')->count();

            $done_all = Course::build()->where('is_deleted', 1)->whereTime('end', 'd')->count();
            $done_success = Course::build()->whereIn('status', [2, 3])->where('is_deleted', 1)->whereTime('end', 'd')->count();

            if ($done_all) {
                $done = $done_success / $done_all;
            } else {
                $done = 0;
            }
            $done = round($done, 2);

            $data = [
                'all' => $all,
                'month' => $month,
                'week' => $week,
                'day' => $day,
                'done' => $done
            ];
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-统计', '');
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function order_college_trendChart($userInfo)
    {
        try {
            $college = College::build()->field('uuid,name')->where('is_deleted', 1)->select();
            $num = [];
            $data = [];
            foreach ($college as $v) {
                $data[] = $v->name;
                $num[] = Order::build()
                    ->alias('o')
                    ->join('course c', 'c.uuid = o.course_uuid', 'left')
                    ->where('o.status', 1)
                    ->where('o.is_deleted', 1)
                    ->where("FIND_IN_SET('{$v->uuid}', c.college)")
                    ->count();
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-书院分布');
            return ['college' => $data, 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function order_cate_trendChart($userInfo)
    {
        try {
            $cate = Course::build()->field('cate_uuid,ca.name')->alias('c')->join('cate ca', 'ca.uuid = c.cate_uuid')->group('c.cate_uuid')->where('c.is_deleted', 1)->select();
            $done_all = Course::build()->where('is_deleted', 1)->count();
            $num = [];
            $data = [];
            foreach ($cate as $v) {
                $data[] = $v->name;
                if ($done_all) {
                    $num[] = round(Course::build()
                            ->where('cate_uuid', $v->cate_uuid)
                            ->where('is_deleted', 1)
                            ->count() / $done_all, 2);
                } else {
                    $num[] = 0;
                }


            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-活动类别成团率');
            return ['college' => $data, 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function order_trendChart($request, $userInfo)
    {
        try {
            $where['o.is_deleted'] = 1;
            $where['o.status'] = 1;

            if ($request['start_time'] && $request['end_time']) {
                $where['o.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['o.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['o.create_time'] = ['<=',$request['end_time']];
            }


            if ($request['cate_uuid']) {
                $where['c.cate_uuid'] = $request['cate_uuid'];
            }
            if ($request['course_uuid']) {
                $where['o.course_uuid'] = $request['course_uuid'];
            }
            if ($request['college_uuid']) {
                $where['u.college_uuid'] = $request['college_uuid'];
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']));
            $num = [];//课程总数

            foreach ($list_date['data'] as $v) {
                if ($list_date['type'] == 1) {
                    $where['o.create_time'] = ['BETWEEN', [$v, $v . ' 23:59:59']];
                }
                if ($list_date['type'] == 2) {
                    // 计算该月的第一天
                    $firstDayOfMonth = date("Y-m-01", strtotime($v));
                    // 计算该月的最后一天
                    $lastDayOfMonth = date("Y-m-t", strtotime($v));
                    $where['o.create_time'] = ['BETWEEN', [$firstDayOfMonth, $lastDayOfMonth . ' 23:59:59']];
                }
                if ($list_date['type'] == 3) {
                    // 计算该年的第一天
                    $firstDayOfMonth = $v . '-01-01';
                    // 计算该年的最后一天
                    $lastDayOfMonth = $v . '-12-31 23:59:59';
                    $where['o.create_time'] = ['BETWEEN', [$firstDayOfMonth, $lastDayOfMonth]];
                }
                $num[] = Order::build()->alias('o')->join('course c', 'c.uuid = o.course_uuid','left')->join('user u', 'u.uuid = o.user_uuid','left')->where($where)->count();
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-趋势');
            return ['date_time' => $list_date['data'], 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function order_list($request, $userInfo)
    {
        try {
            $where['o.is_deleted'] = 1;
            if ($request['course_uuid']) {
                $where['o.course_uuid'] = $request['course_uuid'];
            }
            if ($request['start_time'] && $request['end_time']) {
                $where['o.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['o.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['o.create_time'] = ['<=',$request['end_time']];
            }
            if ($request['cate_uuid']) {
                $where['c.cate_uuid'] = [$request['cate_uuid']];
            }
            if ($request['college_uuid']) {
                $where['u.college_uuid'] = $request['college_uuid'];
            }
            $order = Order::build()
                ->alias('o')
                ->field('
                o.uuid,
                u.uuid as user_uuid,
                u.name,
                u.class,
                u.number,
                u.major,
                co.name as college_name,
                co.uuid as college_uuid,
                o.create_time,
                c.name as course_name,
                c.uuid as course_uuid,
               ca.name as cate_name
            ')
                ->join('course c', 'c.uuid = o.course_uuid', 'left')
                ->join('cate ca', 'ca.uuid = c.cate_uuid', 'left')
                ->join('user u', 'u.uuid = o.user_uuid', 'left')
                ->join('college co', 'u.college_uuid = co.uuid', 'left')
                ->order('o.create_time desc')
                ->where($where);
            if ($request['college_uuid']) {
                //$order = $order->where("FIND_IN_SET('{$request['college_uuid']}', c.college)");
            }
            $order = $order->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-列表', $request);
            return $order;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function order_list_export($request, $userInfo)
    {
        try {
            $result = self::order_list($request, $userInfo);
            $result = $result->toArray();
            $data = [];
            $data[] = ['学号', '姓名', '班级', '书院', '活动名称', '活动类别', '报名时间'];
            foreach ($result['data'] as $k => $v) {
                $tmp = [
                    $v['number'] . ' ',
                    $v['name'],
                    $v['class'],
                    $v['college_name'],
                    $v['course_name'],
                    $v['cate_name'],
                    $v['create_time']
                ];

                foreach ($tmp as $tmp_k => $tmp_v) {
                    $tmp[$tmp_k] = $tmp_v . '';
                }
                $data[] = $tmp;
            }
            $excel = new \PHPExcel();
            $excel_sheet = $excel->getActiveSheet();
            $excel_sheet->fromArray($data);
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $file_name = '报名统计-列表.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;
            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '报名数据分析-列表导出', $request);
            return 'upload/' . $file_name;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function user_order_stat($request, $userInfo)
    {
        try {
            $where = ['o.status' => 1, 'o.is_deleted' => 1];
            if ($request['start_time'] && $request['end_time']) {
                $where['o.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['o.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['o.create_time'] = ['<=',$request['end_time']];
            }
            if ($request['keyword']) {
                $where['u.name|u.number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['c.uuid'] = $request['college_uuid'];
            }
            $all = Order::build()->alias('o')->where($where)->join('user u', 'u.uuid = o.user_uuid')->join('college c', 'c.uuid = u.college_uuid')->count();

            $where = ['s.is_deleted' => 1];
            if ($request['start_time'] && $request['end_time']) {
                $where['s.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['s.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['s.create_time'] = ['<=',$request['end_time']];
            }
            if ($request['keyword']) {
                $where['u.name|u.number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['c.uuid'] = $request['college_uuid'];
            }
            $sign = Sign::build()->alias('s')->join('user u', 'u.uuid = s.user_uuid')->join('college c', 'c.uuid = u.college_uuid')->where($where)->count();

            $where = ['f.is_deleted' => 1, 'f.status' => 1];
            if ($request['start_time'] && $request['end_time']) {
                $where['f.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['f.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['f.create_time'] = ['<=',$request['end_time']];
            }
            if ($request['keyword']) {
                $where['u.name|u.number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['c.uuid'] = $request['college_uuid'];
            }
            $feel = Feel::build()->alias('f')->join('user u', 'u.uuid = f.user_uuid')->join('college c', 'c.uuid = u.college_uuid')->where($where)->count();
            if ($all) {
                $feel = round($feel / $all, 2);
            } else {
                $feel = 0;
            }

            $where = ['is_deleted' => 1];

            if ($request['start_time'] && $request['end_time']) {
                $where['last_login_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['last_login_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['last_login_time'] = ['<=',$request['end_time']];
            }

            if ($request['keyword']) {
                $where['name|number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['college_uuid'] = $request['college_uuid'];
            }
            $active = User::build()->whereNotNull('last_login_time')->where($where)->count();
            $arr = [
                'all' => $all,
                'sign' => $sign,
                'feel' => $feel,
                'active' => $active
            ];
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '学生拼团-分析统计', $request);
            return $arr;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function user_order_trendChart($userInfo)
    {
        try {
            $list_date = cut_date(strtotime("-29 day"), time());
            $num = [];
            foreach ($list_date['data'] as $v) {
                $num [] = Order::where(['status' => 1, 'is_deleted' => 1])->whereTime('create_time', 'BETWEEN', [$v, $v . ' 23:59:59'])->count();
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '学生拼团分析-近30天拼团趋势');
            return ['date_time' => $list_date['data'], 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function user_order_list($request, $userInfo)
    {
        try {
            $where = ['is_deleted' => 1];
            if ($request['keyword']) {
                $where['name|number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['college_uuid'] = $request['college_uuid'];
            }
            $user = User::build()->field('uuid,name,number,college_uuid')->where($where)->order('create_time', 'desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($request) {
                $where = ['is_deleted' => 1, 'user_uuid' => $item['uuid']];

                if ($request['start_time'] && $request['end_time']) {
                    $where['create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
                }else if($request['start_time']){
                    $where['create_time'] = ['>=',$request['start_time']];
                }else if($request['end_time']){
                    $where['create_time'] = ['<=',$request['end_time']];
                }


                $item['college_name'] = College::build()->where('uuid', $item['college_uuid'])->value('name');
                $item['order_num'] = Order::build()->where('status', 1)->where($where)->count();
                $item['sign_num'] = Sign::build()->where($where)->count();
                $item['feel_num'] = Feel::build()->where($where)->where('status', 1)->count();
                if ($item['feel_num'] > $item['sign_num']) {
                    $item['feel_num'] = $item['sign_num'];
                }
                return $item;
            });
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '学生拼团分析-列表', $request);
            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function user_order_list_export($request, $userInfo)
    {
        try {
            $result = self::user_order_list($request, $userInfo);
            $result = $result->toArray();
            $data = [];
            $data[] = ['学生姓名', '学号', '书院', '拼团次数', '签到成功次数', '签到且评语次数'];
            foreach ($result['data'] as $k => $v) {
                $tmp = [
                    $v['name'],
                    $v['number'] . ' ',
                    $v['college_name'],
                    $v['order_num'],
                    $v['sign_num'],
                    $v['feel_num']
                ];

                foreach ($tmp as $tmp_k => $tmp_v) {
                    $tmp[$tmp_k] = $tmp_v . '';
                }
                $data[] = $tmp;
            }

            $excel = new \PHPExcel();
            $excel_sheet = $excel->getActiveSheet();
            $excel_sheet->fromArray($data);
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

            $file_name = '学生拼团分析-列表.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;

            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            return 'upload/' . $file_name;
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '学生拼团分析-列表导出', $request);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function admin_course_stat($request, $userInfo)
    {
        try {
            $where = ['c.is_deleted' => 1, 'c.status' => 3];

            if ($request['start_time'] && $request['end_time']) {
                $where['c.class_begin'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['c.class_begin'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['c.class_begin'] = ['<=',$request['end_time']];
            }

            if ($request['keyword']) {
                $where['a.name|a.number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['c.college_uuid'] = $request['college_uuid'];
            }
            $all = Course::build()->alias('c')->where($where)->join('admin a', 'a.uuid = c.admin_uuid')->count();

            $where = ['e.is_deleted' => 1];

            if ($request['start_time'] && $request['end_time']) {
                $where['e.create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
            }else if($request['start_time']){
                $where['e.create_time'] = ['>=',$request['start_time']];
            }else if($request['end_time']){
                $where['e.create_time'] = ['<=',$request['end_time']];
            }

            if ($request['keyword']) {
                $where['u.name|u.number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['u.college_uuid'] = $request['college_uuid'];
            }
            $evaluate = Evaluate::build()->alias('e')->join('user u', 'u.uuid = e.user_uuid')->where($where)->count();

            $avg = Evaluate::build()->alias('e')->join('user u', 'u.uuid = e.user_uuid')->where($where)->avg('point');
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '教师授课分析-统计', $request);
            return ['all' => $all, 'evaluate' => $evaluate, 'avg' => $avg];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function admin_course_trendChart($userInfo)
    {
        try {
            $list_date = cut_date(strtotime("-29 day"), time());
            $num = [];
            foreach ($list_date['data'] as $v) {
                $num [] = Course::where(['status' => 3, 'is_deleted' => 1])->whereTime('class_begin', 'BETWEEN', [$v, $v . ' 23:59:59'])->count();
            }
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '教师授课分析-近30天授课趋势');
            return ['date_time' => $list_date['data'], 'num' => $num];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function admin_course_list($request, $userInfo)
    {
        try {
            $where = ['is_deleted' => 1];
            if ($request['keyword']) {
                $where['name|number'] = ['like', '%' . $request['keyword'] . '%'];
            }
            if ($request['college_uuid']) {
                $where['college_uuid'] = $request['college_uuid'];
            }
            $admin = Admin::build()->field('uuid,name,number,college_uuid')->where($where)->order('create_time', 'desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($request) {
                $item['college_name'] = College::build()->where('uuid', $item['college_uuid'])->value('name');

                $where = ['is_deleted' => 1, 'admin_uuid' => $item['uuid']];
                if ($request['start_time'] && $request['end_time']) {
                    $where['class_begin'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
                }else if($request['start_time']){
                    $where['class_begin'] = ['>=',$request['start_time']];
                }else if($request['end_time']){
                    $where['class_begin'] = ['<=',$request['end_time']];
                }
                $item['course_num'] = Course::build()->where('status', 3)->where($where)->count(); //授课次数

                $where = ['is_deleted' => 1, 'admin_uuid' => $item['uuid']];
                if ($request['start_time'] && $request['end_time']) {
                    $where['create_time'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
                }else if($request['start_time']){
                    $where['create_time'] = ['>=',$request['start_time']];
                }else if($request['end_time']){
                    $where['create_time'] = ['<=',$request['end_time']];
                }
                $item['evaluate'] = Evaluate::build()->where($where)->avg('point');//学生评价


                $where = ['o.is_deleted' => 1, 'c.admin_uuid' => $item['uuid'], 'o.status' => 1];
                if ($request['start_time'] && $request['end_time']) {
                    $where['c.class_begin'] = ['BETWEEN',[$request['start_time'], $request['end_time']]];
                }else if($request['start_time']){
                    $where['c.class_begin'] = ['>=',$request['start_time']];
                }else if($request['end_time']){
                    $where['c.class_begin'] = ['<=',$request['end_time']];
                }
                $num = Order::build()->alias('o')->join('course c', 'c.uuid = o.course_uuid')->where($where)->count();
                if ($item['course_num']) {
                    $item['avg_num'] = round($num / $item['course_num']);
                } else {
                    $item['avg_num'] = 0;
                }
                return $item;
            });
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '教师授课分析-列表', $request);
            return $admin;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static function admin_course_list_export($request, $userInfo){
        try {
            $result = self::admin_course_list($request, $userInfo);
            $result = $result->toArray();
            $data = [];
            $data[] = ['教师姓名', '工号', '授课次数', '学生评价', '平均授课人数'];
            foreach ($result['data'] as $k => $v) {
                $tmp = [
                    $v['name'],
                    $v['number'] . ' ',
                    $v['course_num'],
                    $v['evaluate'],
                    $v['avg_num']
                ];

                foreach ($tmp as $tmp_k => $tmp_v) {
                    $tmp[$tmp_k] = $tmp_v . '';
                }
                $data[] = $tmp;
            }

            $excel = new \PHPExcel();
            $excel_sheet = $excel->getActiveSheet();
            $excel_sheet->fromArray($data);
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $file_name = '教师授课分析-列表.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;
            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            return 'upload/' . $file_name;
            AdminLog::build()->add($userInfo['uuid'], '数据分析', '教师授课分析-列表导出', $request);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
