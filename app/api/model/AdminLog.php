<?php

namespace app\api\model;

use \think\Request;

/**
 * 管理员日志-模型
 * User: Yacon
 * Date: 2022-08-11
 * Time: 20:43
 */
class AdminLog extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    /**
     * 添加日志
     * @param {stirng} $admin_uuid 管理员UUID
     * @param {string} $menu 一级菜单
     * @param {string} $content 操作对象
     * @param {string} $status 操作对象
     *
     */
    public function add(string $admin_uuid,string $menu, string $content,$status = 2)
    {
        $method = \request()->method();
        switch (strtolower($method)) {
            case 'get':
                $type = 3;
                break;
            case 'post':
                $type = 1;
                break;
            case 'delete':
                $type = 4;
                break;
            case 'put':
                $type = 2;
                break;
            default:
                $type = 1;
        }
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? request()->ip();
        $this->insert([
            'uuid' => uuid(),
            "create_time" => now_time(time()),
            "update_time" => now_time(time()),
            "menu" => $menu,
            "content" => $content,
            "admin_uuid" => $admin_uuid,
            "ip" => $ip,
            'type' => $type,
            'status' => $status,
        ]);
    }

}
