<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\MedicalReport;
use DateTime;
use think\Exception;
use think\Db;

/**
 * 体检报告-逻辑
 */
class MedicalReportLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            //近期报告限制
            $ReportUploadTime = Config::build()->where('key', 'ReportUploadTime')->value('value');
            $targetDate = New DateTime($request['date']);
            $currentDate = New DateTime();
            $interval = $targetDate->diff($currentDate);
            if($interval->days > $ReportUploadTime){
                return ['msg'=>'请提交'.$ReportUploadTime.'天以内的报告'];
            }
            $request['uuid'] = uuid();
            $request['user_uuid'] = $userInfo['uuid'];
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            MedicalReport::build()->save($request);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'is_deleted' => 1,
                'site_id' => $request['site_id'],
                'user_uuid' => $userInfo['uuid'],
            ];
            $request['status'] ? $where['status'] = $request['status'] : '';
            if($request['is_use']){
                $day = Config::build()->where('key','ReportPassTime')->value('value');
                if($request['is_use'] == 1){
                    $where['review_time'] = 'is not null';
                    $where['review_time'] = ['>=',date('Y-m-d H:i:s',strtotime("-{$day} days"))];
                    $where['status'] = 2;
                }
            }
            return MedicalReport::build()->where($where)->order('date desc')->select();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data =  MedicalReport::build()->where('is_deleted', 1)->where('user_uuid', $userInfo['uuid'])->where('uuid', $uuid)->findOrFail();
            $day = Config::build()->where('key','ReportPassTime')->value('value');
            $data['expiration_time'] = $data->status == 2?date('Y-m-d H:i:s',strtotime('+'.$day.' days',strtotime($data['review_time']))):'';
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
