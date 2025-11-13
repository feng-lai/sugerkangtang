<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Questionnaire;

class QuestionnaireLogic
{

    public static function sync()
    {

        $now = date('Y-m-d', time());

        // 到达开始时间，问卷调查的状态改为进行中
        Questionnaire::build()->where(['is_deleted' => 1, 'begin_time' => ['<=', $now], 'end_time' => ['>', $now]])->update(['state' => 2]);
        // 到达结束时间，问卷调查的状态改为已结束
        Questionnaire::build()->where(['is_deleted' => 1, 'end_time' => ['<=', $now]])->update(['state' => 3]);
        // 到达结束时间，问卷调查的状态改为已结束
        Questionnaire::build()->where(['is_deleted' => 1, 'hide_time' => ['<=', $now]])->update(['state' => 4]);
        
    }
}
