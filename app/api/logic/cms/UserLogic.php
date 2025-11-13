<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Order;
use app\api\model\Retail;
use app\api\model\User;
use app\api\model\Score;
use app\api\model\UserInterrest;
use app\api\model\UserRelation;
use app\common\tools\Sync;
use think\Exception;
use think\Db;

/**
 * 用户管理-用户列表-逻辑
 */
class UserLogic
{
    static public function menu()
    {
        return ['用户管理', '用户列表'];
    }

    static public function cmsList($request, $userInfo)
    {
        $map['u.is_deleted'] = 1;
        $request['keyword'] ? $map['u.name|u.phone'] = ['like', '%' . $request['keyword'] . '%'] : '';
        $request['gender'] ? $map['u.gender'] = $request['gender'] : '';
        $request['site_id'] ? $map['u.site_id'] = $request['site_id'] : '';
        if ($request['last_login_time']) {
            $last_login_time = explode(',', $request['last_login_time']);
            if($last_login_time[0]){
                $map['u.last_login_time'] = [
                    'between',
                    [
                        $last_login_time[0], $last_login_time[1]
                    ]
                ];
            }

        }
        if ($request['create_time']) {
            $create_time = explode(',', $request['create_time']);
            if($create_time[0]){
                $map['u.create_time'] = [
                    'between',
                    [
                        $create_time[0], $create_time[1]
                    ]
                ];
            }

        }
        $result = User::build()
            ->alias('u')
            ->join('retail r','r.user_uuid = u.uuid and r.is_deleted = 1','left')
            ->field('
            u.site_id,
            u.uuid,
            u.name,
            u.img,
            u.height,
            u.weight,
            u.phone,
            u.invite_uuid,
            u.disabled,
            u.birthday,
            u.last_login_time,
            u.create_time,
            u.disabled_end_time,
            u.disabled_uuid,
            u.disabled_time,
            u.gender,
            u.disabled_note,
            r.uuid as retail_uuid
            ');
        if ($request['disabled']) {
            if ($request['disabled'] == 1) {
                $result = $result->where(function ($query) use ($map) {
                    $query->where($map)->where('u.disabled', 1);
                })->whereOr(function ($query) use ($map) {
                    $query->where($map)->where(['u.disabled' => 2, 'u.disabled_end_time' => ['<=', date('Y-m-d H:i:s')]])->where('u.disabled_end_time', 'NOT NULL');
                });
            } else {
                $result = $result->where(function ($query) use ($map) {
                    $query->where($map)->where(['u.disabled' => 2, 'u.disabled_end_time' => ['>', date('Y-m-d H:i:s')]]);
                })->whereOr(function ($query) use ($map) {
                    $query->where($map)->where(['u.disabled' => 2, 'u.disabled_end_time' => NULL]);
                });
            }
        }else{
            $result = $result->where($map);
        }
        if($request['is_retail'] == 1){
            $result = $result->whereNotNull('r.uuid');
        }
        if($request['is_retail'] == 2){
            $result = $result->whereNull('r.uuid');
        }
        $result = $result->order('u.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item['invite_user'] = User::build()->where(['uuid' => $item['invite_uuid']])->value('name');
                if ($item['disabled_end_time'] <= date('Y-m-d H:i:s') && $item['disabled'] == 2 && $item['disabled_end_time'] != NULL) {
                    $item['disabled'] = 1;
                }
                $item['disabled_admin'] = Admin::build()->where(['uuid' => $item['disabled_uuid']])->value('name');
                $item['price'] = Order::build()->where(['user_uuid'=>$item['uuid'],'status'=>['in',[2,3,4]]])->sum('price');
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);

        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $result = User::build()
            ->alias('a')
            ->join('user u', 'u.uuid = a.invite_uuid', 'left')
            ->join('retail r', 'r.user_uuid = u.uuid', 'left')
            ->where('a.uuid', $id)
            ->field('
                    a.uuid,
                    a.name,
                    a.img,
                    a.height,
                    a.weight,
                    a.gender,
                    a.phone,
                    a.birthday,
                    u.name as invite_name,
                    r.name as retail_name,
                    a.last_login_time,
                    a.create_time,
                    a.disabled,
                    a.disabled_end_time
            ')
            ->find();
        $is_retail = Retail::build()->where(['user_uuid' => $id])->count();
        if ($is_retail) {
            $result->is_retail = 1;
        } else {
            $result->is_retail = 2;
        }
        if ($result->disabled_end_time && $result->disabled_end_time < now_time(time())) {
            $result->disabled = 1;
        }
        $result->cost = Order::build()->where('user_uuid', $id)->where('status', 'in', [2, 3, 4])->sum('price');
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);

        return $result;
    }

    static public function setDisabled($request, $userInfo)
    {
        if ($request['disabled'] == 2) {
            $data['disabled'] = $request['disabled'];
            $data['disabled_time'] = now_time(time());
            $data['disabled_note'] = $request['disabled_note'];
            $data['disabled_uuid'] = $userInfo['uuid'];
            if ($request['type'] == 2) {
                $data['disabled_end_time'] = date('Y-m-d H:i:s', strtotime('+' . $request['day'] . ' day'));
            } else {
                $data['disabled_end_time'] = Null;
            }
        } else {
            $data = [
                'disabled' => $request['disabled'],
                'disabled_time' => NULL,
                'disabled_note' => NULL,
                'disabled_uuid' => NULL,
                'disabled_end_time' => NULL,
            ];
        }
        User::build()->whereIn('uuid', explode(',', $request['uuid']))->update($data);
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return true;
    }


}
