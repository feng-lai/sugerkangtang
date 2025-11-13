<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Config;
use app\api\model\MedicalReport;
use app\api\model\Msg;
use think\Exception;
use think\Db;

/**
 * 体检报告管理
 */
class MedicalReportLogic
{
    static public function menu()
    {
        return '体检报告管理';
    }

    static public function cmsList($request, $userInfo)
    {
        $where = [
            'm.is_deleted' => 1
        ];
        $request['keyword'] ? $where['m.name|u.name|m.uuid'] = ['like', '%' . $request['keyword'] . '%'] : '';
        ($request['status'] && $request['status'] != 4) ? $where['m.status'] = ['=', $request['status']] : '';
        $request['site_id'] ? $where['m.site_id'] = ['=', $request['site_id']] : '';
        $request['user_uuid'] ? $where['m.user_uuid'] = ['=', $request['user_uuid']] : '';
        $request['start_time'] ? $where['m.create_time'] = ['between', [$request['start_time'], get_end_time($request['end_time'])]] : '';
        $request['review_start_time'] ? $where['m.review_time'] = ['between', [$request['review_start_time'], get_end_time($request['review_end_time'])]] : '';
        $day = Config::build()->where('key', 'ReportPassTime')->value('value');
        $result = MedicalReport::build()
            ->field('
                m.*,
                u.name as user_name,
                u.phone
            ')
            ->alias('m')
            ->join('user u', 'u.uuid = m.user_uuid');
        if ($request['status'] && $request['status'] == 4) {
            $result = $result->where(Db::raw('DATE_ADD(m.review_time, INTERVAL '.$day.' day) < NOW()'));
        }
        $result = $result->where($where)
            ->order('m.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $day = Config::build()->where('key', 'ReportPassTime')->value('value');
                $item['expiration_time'] = $item->status == 2 ? date('Y-m-d H:i:s', strtotime('+' . $day . ' days', strtotime($item['review_time']))) : '';
                $day = strtotime($item['expiration_time']) - time();
                if ($day <= 0) {
                    $day = 0;
                } else {
                    $day = ceil($day / (3600 * 24));
                }
                $item['day'] = $day;
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = MedicalReport::build()->field('m.*,u.name as user_name,u.phone,a.name as admin_name')
            ->alias('m')
            ->where('m.uuid', $id)
            ->join('user u', 'u.uuid = m.user_uuid', 'LEFT')
            ->join('admin a', 'a.uuid = m.admin_uuid', 'left')
            ->where('m.is_deleted', 1)
            ->findOrFail();
        $day = Config::build()->where('key', 'ReportPassTime')->value('value');
        $data['expiration_time'] = $data->status == 2 ? date('Y-m-d H:i:s', strtotime('+' . $day . ' days', strtotime($data['review_time']))) : '';
        $day = strtotime($data['expiration_time']) - time();
        if ($day <= 0) {
            $day = 0;
        } else {
            $day = ceil($day / (3600 * 24));
        }
        $data['day'] = $day;
        AdminLog::build()->add($userInfo['uuid'], self::menu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            MedicalReport::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '新增轮播图-' . $request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = MedicalReport::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '编辑轮播图-' . $user['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = MedicalReport::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '删除轮播图-' . $data['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setStatus($request, $userInfo)
    {
        try {
            Db::startTrans();
            $banner = MedicalReport::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            if ($banner->status != 1) {
                return ['msg' => '非待审核状态'];
            }
            $banner->save(['status' => $request['status'], 'reason' => $request['reason'], 'review_time' => now_time(time()), 'admin_uuid' => $userInfo['uuid']]);
            if ($request['status'] == 2) {
                $content = '您上传的体检报告（' . $banner->date . '）审核通过,点击查看详情';
            } else {
                $content = '您上传的体检报告（' . $banner->date . '）审核不通过,原因：' . $request['reason'] . '，请重新上传一个月内报告';
            }
            //通知
            Msg::build()->insert([
                'uuid' => uuid(),
                'medical_report_uuid' => $request['uuid'],
                'type' => 2,
                'user_uuid' => $banner['user_uuid'],
                'title' => '报告审核通知',
                'content' => $content,
                'site_id' => $banner->site_id,
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ]);
            AdminLog::build()->add($userInfo['uuid'], self::menu(), '体检报告审核');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
