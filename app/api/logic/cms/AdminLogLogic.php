<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\AdminToken;
use app\api\model\AdminRole;
use think\Exception;
use think\Db;

/**
 * 后台用户-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminLogLogic
{
    static public function cmsList($request)
    {
        $map['l.is_deleted'] = 1;
        if ($request['start_time']) $map['l.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
        if ($request['keyword']) $map['a.name|l.content'] = ['like', '%' . $request['name'] . '%'];
        if ($request['type']) $map['l.type'] = ['=',$request['type']];
        if ($request['status']) $map['l.status'] = ['=',$request['status']];
        if ($request['admin_uuid']) $map['l.admin_uuid'] = ['=',$request['admin_uuid']];
        if ($request['menu']) $map['l.menu'] = ['=',$request['menu']];
        $result = AdminLog::build()
            ->field('l.*,a.name as admin_name')
            ->alias('l')
            ->join('admin a', 'a.uuid = l.admin_uuid')
            ->where($map)
            ->order('l.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);

        return $result;
    }

    static public function cmsDetail($id)
    {
        return Admin::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('*')
            ->find();
    }

    static public function cmsAdd($request)
    {
        try {
            Db::startTrans();
            AdminRole::build()->findOrFail($request['role_uuid']);
            if (Admin::build()->where('mobile', $request['mobile'])->count()) {
                throw new Exception('账号已存在', 500);
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'password' => md6($request['password']),
                'email' => $request['email'],
                'mobile' => $request['mobile'],
                'role_uuid' => $request['role_uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Admin::build()->save($data);
            //添加token
            $token = AdminToken::build();
            $token->uuid = uuid();
            $token->admin_uuid = $data['uuid'];
            $token->create_time = now_time(time());
            $token->update_time = now_time(time());
            $token->save();
            Db::commit();
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request)
    {
        try {
            $user = Admin::build()->where('uuid', $request['uuid'])->find();
            if (isset($request['password'])) {
                $request['password'] = md6($request['password']);
            }
            $user->save($request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id)
    {
        try {
            //少于90天不能删除
            $data = AdminLog::build()->where('uuid', $id)->findOrFail();
            if(time() - strtotime($data->create_time) < 90*3600*24){
                return ['msg'=>'少于90天的日志不能删除'];
            }
            $data->save(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
