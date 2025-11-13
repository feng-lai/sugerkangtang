<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Config;
use think\Exception;
use think\Db;

/**
 * 配置-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ConfigLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Config::build();
        if ($request) {
            $result = $result->where('key', 'in', explode(',',$request));
        }
        $result = $result->select();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-平台内容配置', '列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Config::build()
            ->where('key', $id)
            ->field('*')
            ->find();
        AdminLog::build()->add($userInfo['uuid'], '内容管理-平台内容配置-'.$data->content, '详情');
        return $data;
    }


    static public function cmsEdit($request, $userInfo)
    {
        try {
            $where['key'] = $request['key'];
            $where['is_deleted'] = 1;
            $request['site_id']?$where['site_id'] = $request['site_id']:'';
            $data = Config::build()->where($where)->findOrFail();
            $data->save(['value' => $request['value']]);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-平台内容配置-'.$data->content, '编辑');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsSave($request, $userInfo){
        try {
            foreach($request as $v){
                Config::build()->where('key', $v['key'])->update(['value' => $v['value']]);
            }
            AdminLog::build()->add($userInfo['uuid'], '内容管理-平台内容配置', '编辑');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function save($request, $userInfo){
        try {
            if(is_array($request['value'])){
                $request['value'] = json_encode($request['value'],JSON_UNESCAPED_UNICODE);
            }
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Config::build()->save($request);
            AdminLog::build()->add($userInfo['uuid'], '内容管理-平台内容配置', '新增');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
