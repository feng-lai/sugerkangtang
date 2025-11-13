<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Level;
use app\api\model\User;

class ResetCountersignLogic
{

    public static function sync()
    {

        // 白银每月可补签的次数
        $level2_times = Level::build()->where(['is_deleted' => 1, 'level' => 2])->value('repair_times');
        // 黄金每月可补签的次数
        $level3_times = Level::build()->where(['is_deleted' => 1, 'level' => 3])->value('repair_times');
        // 钻石每月可补签的次数
        $level4_times = Level::build()->where(['is_deleted' => 1, 'level' => 4])->value('repair_times');
        // 黑金每月可补签的次数
        $level5_times = Level::build()->where(['is_deleted' => 1, 'level' => 5])->value('repair_times');

        // 重置白银用户补签次数
        User::build()->where(['is_deleted' => 1, 'level_id' => 2])->update(['countersign' => $level2_times]);

        // 重置黄金用户补签次数
        User::build()->where(['is_deleted' => 1, 'level_id' => 3])->update(['countersign' => $level3_times]);

        // 重置钻石用户补签次数
        User::build()->where(['is_deleted' => 1, 'level_id' => 4])->update(['countersign' => $level4_times]);

        // 重置黑金用户补签次数
        User::build()->where(['is_deleted' => 1, 'level_id' => 5])->update(['countersign' => $level5_times]);
    }
}
