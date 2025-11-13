<?php

namespace app\api\logic\cms;

use app\api\model\AdminMenu;
use app\api\model\AdminRole;
use app\api\model\College;
use think\Exception;
use think\Db;

/**
 * 后台用户-逻辑
 */
class AdminInfoLogic
{
    static public function info($user)
    {
        try {
            $role = AdminRole::build()->whereIn('uuid',$user['role_uuid'])->where('name','超级管理员')->find();
            // 查询角色
            if($role){
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
            unset($user['password']);
            return $user;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
