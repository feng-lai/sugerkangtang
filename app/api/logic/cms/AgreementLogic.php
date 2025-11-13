<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Agreement;
use think\Exception;
use think\Db;

/**
 * 协议中心逻辑
 */
class AgreementLogic
{
    static public function getMenu()
    {
        return '内容管理-协议中心';
    }

    static public function getType($type)
    {
        //类型 1=用户协议 2=隐私政策 3=换电服务协议 4=个人信息收集清单 5=个人信息共享清单
        switch ($type) {
            case 1:
                return '用户协议';
                break;
            case 2:
                return '隐私政策';
                break;
            case 3:
                return '换电服务协议';
                break;
            case 4:
                return '个人信息收集清单';
                break;
            case 5:
                return '个人信息共享清单';
                break;
            default:
                return '用户协议';
        }
    }

    static public function cmsList($request, $userInfo)
    {
        $where['a.is_deleted'] = 1;
        $request['site_id']?$where['a.site_id'] = $request['site_id']:'';
        $request['type']?$where['a.type'] = $request['type']:'';
        $result = Agreement::build()->alias('a')->where($where)->order('a.create_time desc');
        if($request['uuid']){
            $result = $result->where('a.uuid','<>',$request['uuid']);
        }else{
            $result = $result->join('(SELECT type, MAX(create_time) as max_create_time FROM agreement GROUP BY type) recent', 'a.type = recent.type AND a.create_time = recent.max_create_time');
        }
        $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item->admin_name = Admin::build()->where('uuid',$item->admin_uuid)->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Agreement::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '查看详情');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $request['uuid'] = uuid();
            $request['admin_uuid'] = $userInfo['uuid'];
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Agreement::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '新增-'.self::getType($request['type']));
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = Agreement::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '编辑-'.self::getType($user['type']));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Agreement::build()->where('uuid', $id)->findOrFail();
            if(Agreement::build()->where('type', $data['type'])->order('create_time desc')->value('uuid') == $id){
                return ['msg'=>'只能删除历史版本的协议'];
            }
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu(), '删除-'.self::getType($data['type']));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
