<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Member;
use think\Exception;
use think\Db;

/**
 * 会员设置逻辑
 */
class MemberLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Member::build()->field('uuid,pid,name,create_time')->where('is_deleted', 1)->order('create_time asc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        foreach($result as $v){
            if($v->pid){
                $v->next = Member::build()->where('uuid', $v->pid)->value('name');
                $v->type = 2;
            }else{
                $v->next = '末级会员';
                $v->type = 1;
            }
        }
        AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Member::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->next = $data->pid?Member::build()->where('uuid', $data->pid)->value('name'):'末级会员';
        AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', '','');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            //名称是否重复
            if (Member::build()->where('name', $request['name'])->where('is_deleted', 1)->count()) {
                return ['msg' => '名称已存在'];
            }
            if($request['pid']  && Member::build()->where('pid', $request['pid'])->where('is_deleted', 1)->count()){
                return ['msg' => '选择的下级会员已有数据，请选择别的下级会员'];
            }
            $request['uuid'] = uuid();
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Member::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', '',$request);
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            //名称是否重复
            if (Member::build()->where('name', $request['name'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()) {
                return ['msg' => '名称已存在'];
            }
            if($request['pid']  && Member::build()->where('pid', $request['pid'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()){
                return ['msg' => '选择的下级会员已有数据，请选择别的下级会员'];
            }
            $old  = Member::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $user = Member::build()->where('uuid', $uuid)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', $old,$user);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Member::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            if(!$data->pid){
                return ['msg'=>'基础会员不能删除'];
            }
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', '',$data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
