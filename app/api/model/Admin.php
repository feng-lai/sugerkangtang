<?php

namespace app\api\model;

use Exception;

/**
 * 后台管理员用户-模型
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:19
 */
class Admin extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    /**
     * 生成ID号
     */
    public function createID()
    {
        $number = $this->max('serial_number');
        $number++;
        $count = strlen($number);
        $pre = 'AM';
        for ($i = 0; $i < 7 - $count; $i++) {
            $pre .= '0';
        }
        $result = $pre .  $number;
        return [$number, $result];
    }

    public function getRoleUuidAttr($value)
    {
        return json_decode($value, true);
    }

    public function setRoleUuidAttr($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 用户登陆
     * @param {String} $mobile 账号
     * @param {String} $password 密码
     */
    public function login($uname, $password)
    {
        // 加密密码
        $password = md6($password);

        // 用户登陆
        $user = self::field('*')
            ->where(['uname' => $uname, 'password' => $password, 'is_deleted' => 1])
            ->find();

        // 如果用户不存在，则报错
        if (empty($user)) {
            AdminLog::build()->add('', '系统设置-管理员管理','登录失败',1);
            throw new Exception('登陆失败,密码错误或者用户不存在', 403);
        }

        if($user['outline_type'] == 1){
            if(Producer::build()->where('uuid',$user['producer_uuid'])->value('status') == 2){
                throw new Exception('出品方已禁用', 403);
            }
        }
        if($user['outline_type'] == 2){
            if(Dealer::build()->where('uuid',$user['dealer_uuid'])->value('status') == 2){
                throw new Exception('特邀经销商已禁用', 403);
            }
        }
        if($user['outline_type'] == 3){
            if(Region::build()->where('uuid',$user['region_uuid'])->value('status') == 2){
                throw new Exception('大区推广员已禁用', 403);
            }
        }
        if($user['outline_type'] == 4){
            if(Channel::build()->where('uuid',$user['channel_uuid'])->value('status') == 2){
                throw new Exception('渠道商已禁用', 403);
            }
        }

        //用户未启用
        if($user['status'] == 2){
            throw new Exception('用户已禁用', 403);
        }

        $user->save(['last_login' => date('Y-m-d H:i:s')]);

        AdminLog::build()->add($user['uuid'],'系统设置-管理员管理','登录',1);

        $user = objToArray($user);

        unset($user['password']);

        // 查询角色
        $admin_role = AdminRole::build()->whereIn('uuid',$user['role_uuid'])->where('name','超级管理员')->find();
        if($admin_role){
            //超级管理员
            $adminRole = AdminRole::build()->where('is_deleted',1)->select();
        }else{
            $adminRole = AdminRole::build()->whereIn('uuid', $user['role_uuid'])->where('is_deleted',1)->select();
        }
        $menu = [];
        $role = [];
        foreach ($adminRole as $v) {
            if($v['menus']){
                $menu = array_merge($menu, $v['menus']);
            }
            $role[] = $v['name'];
        }
        // 菜单
        $menu= array_unique($menu);
        $user['menus'] =array_values($menu);

        $user['url'] = AdminMenu::build()->whereIn('uuid',$menu)->column('url');

        $menus = AdminMenu::build()->field('uuid,name,url,pid')->where(['uuid' => ['in', $menu], 'is_deleted' => 1])->select();
        // 角色名
        $user['role_name'] = $role;

        $menus = objToArray($menus);
        $user['menus_all'] = getTreeList($menus, null);


        // 记录用户信息
        $result['user'] = $user;

        // 更新Token
        $token = AdminToken::build()->where(['admin_uuid' => $user['uuid']])->find();

        if (empty($token)) {
            throw new Exception('非法登陆', 403);
        }

        // 如果Token已过期，则更新Token
        if ($token->expiry_time < now_time(time())) {
            // 生成Token
            $result['token'] = uuid();
            $token->token = $result['token'];
            $token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
            $token->save();
        }

        $result['token'] = $token->token;


        return $result;
    }
}
