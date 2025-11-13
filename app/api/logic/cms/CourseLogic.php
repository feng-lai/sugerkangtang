<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Chapter;
use app\api\model\Course;
use app\api\model\CourseCate;
use think\Exception;
use think\Db;

/**
 *课程逻辑
 */
class CourseLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['c.is_deleted' => 1];
        if ($request['name']) {
            $where['c.name'] = ['like', '%' . $request['name'] . '%'];
        }
        if ($request['vis']) {
            $where['c.vis'] = ['=', $request['vis']];
        }
        if ($request['is_quality']) {
            $where['c.is_quality'] = ['=', $request['is_quality']];
        }
        if ($request['is_hot']) {
            $where['c.is_hot'] = ['=', $request['is_hot']];
        }
        if ($request['is_home']) {
            $where['c.is_home'] = ['=', $request['is_home']];
        }
        if ($request['course_cate_uuid']) {
            $where['c.course_cate_uuid'] = ['=', $request['course_cate_uuid']];
        }
        if ($request['sub_course_cate_uuid']) {
            $where['c.sub_course_cate_uuid'] = ['=', $request['sub_course_cate_uuid']];
        }        $result = Course::build()
            ->alias('c')
            ->field('
                c.uuid,
                c.name,
                ca.name as course_cate_name,
                cu.name as sub_course_cate_name,
                (select count(1) as num from `chapter` where course_uuid = c.uuid and is_deleted = 1) as num,
                c.create_time,
                c.vis,
                c.weight,
                c.is_home,
                c.is_quality,
                c.is_hot
            ')
            ->join('course_cate ca', 'ca.uuid = c.course_cate_uuid', 'left')
            ->join('course_cate cu', 'cu.uuid = c.sub_course_cate_uuid', 'left')
            ->where($where)
            ->order('c.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '拼课课程', '课程列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Course::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->course_cate_name = CourseCate::build()->where('uuid', $data->course_cate_uuid)->value('name');
        $data->sub_course_cate_name = CourseCate::build()->where('uuid', $data->sub_course_cate_uuid)->value('name');
        $data->chapter = Chapter::build()->where('course_uuid',$id)->where('is_deleted', 1)->select()->each(function ($chapter){
            $chapter->points = json_decode($chapter->points);
        });
        AdminLog::build()->add($userInfo['uuid'], '拼课课程', '课程列表');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if(Course::build()->where('is_deleted',1)->where('name',$request['name'])->count()){
                return ['msg'=>'当前课程名称已存在，请重新输入'];
            }
            if(Course::build()->where('is_deleted',1)->where('weight',$request['weight'])->count()){
                return ['msg'=>'当前课程权重已存在，请重新输入'];
            }
            $chapter = $request['chapter'];

            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            unset($request['chapter']);
            Course::build()->insert($request);

            //保存章节信息
            foreach ($chapter as $v){
                $data = $v;
                $data['uuid'] = uuid();
                $data['course_uuid'] = $request['uuid'];
                $data['create_time'] = now_time(time());
                $data['update_time'] = now_time(time());
                $data['points'] = json_encode($v['points'],JSON_UNESCAPED_UNICODE);
                Chapter::build()->insert($data);
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表','', $request);
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            Db::startTrans();
            $old = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $old->chapter = Chapter::build()->where('course_uuid', $uuid)->where('is_deleted', 1)->find();

            $chapter = $request['chapter'];
            $request['update_time'] = now_time(time());
            $data = Course::build()->where('uuid', $uuid)->findOrFail();
            unset($request['chapter']);
            $data->save($request);

            //保存章节信息
            //先删除原来的
            Chapter::build()->where('course_uuid', $uuid)->delete();
            foreach ($chapter as $v){
                $data = $v;
                $data['uuid'] = uuid();
                $data['course_uuid'] = $uuid;
                $data['create_time'] = now_time(time());
                $data['update_time'] = now_time(time());
                $data['points'] = json_encode($v['points'],JSON_UNESCAPED_UNICODE);
                Chapter::build()->insert($data);
            }
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表', $old, $request);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data  = Course::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表','' ,$data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setVis($request, $userInfo, $uuid)
    {
        try {
            $user = Course::build()->where('uuid', $uuid)->where('is_deleted',1)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表','', $request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function recommend($request, $userInfo, $uuid)
    {
        try {
            DB::startTrans();
            $user = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            if (!in_array($user->status, [1, 2])) {
                return ['msg' => '已完成或者已取消的课程不能取消'];
            }
            //已报名用户修改为取消
            $order = Order::build()->where('course_uuid', $uuid)->where('is_deleted', 1)->select();
            Order::build()->where('course_uuid', $uuid)->where('is_deleted', 1)->update(['status' => 2, 'cancel_type' => 3, 'reason' => $request['reason'],'cancel_time' => date('Y-m-d H:i:s')]);
            $user->save(['status' => 4, 'reason' => $request['reason'], 'cancel_type' => 3, 'cancel_time' => date('Y-m-d H:i:s')]);
            //发送取消通知
            foreach ($order as $v) {
                send_msg($v->user_uuid, $v->uuid, '', '你报名的' . $user->name . '，老师已取消课程，请及时查看',$uuid);
            }
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程推荐管理');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setMinMax($request, $userInfo, $uuid)
    {
        $course = Course::build()->where('uuid', $uuid)->findOrFail();
        if ($course->status != 1) {
            return ['msg' => '报名中的课程才能修改'];
        }
        if ($request['min'] > $request['max']) {
            return ['msg' => '最少成团数不能大于最大成团数'];
        }
        if ($request['min'] > $course->min) {
            return ['msg' => '最少成团数只能减少'];
        }
        if ($request['max'] < $course->max) {
            return ['msg' => '最大成团数只能增加'];
        }
        //当前报名人数
        $num = Order::build()->where('status', 1)->where('is_deleted', 1)->where('course_uuid', $uuid)->count();
        if ($num > $request['min']) {
            return ['msg' => '最少成团数不能比已报名的人数少'];
        }
        $course->save(['min' => $request['min'], 'max' => $request['max']]);
        AdminLog::build()->add($userInfo['uuid'], '拼课课程', '修改最少最大成团人数：' . $course->name, $request);
        return true;
    }

    static function setMax($request, $userInfo, $uuid)
    {
        try {
            DB::startTrans();
            $course = Course::build()->where('uuid', $uuid)->findOrFail();
            if ($course->status != 2) {
                return ['msg' => '待开课的课程才能追加'];
            }
            if ($request['max'] < $course->max) {
                return ['msg' => '最大成团数只能增加'];
            }
            //修改为拼课中状态，结束时间修改为当前时间+1天
            $course->save(['max' => $request['max'], 'status' => 1, 'end' => now_time(time() + 3600 * 24)]);
            AdminLog::build()->add($userInfo['uuid'], '拼课课程', '追加最大成团人数：' . $course->name, $request);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
