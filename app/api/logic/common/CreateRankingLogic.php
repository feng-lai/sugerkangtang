<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\ActivitiesRanking;
use app\api\model\ActivitiesRankingSetting;
use app\api\model\ActivitiesRankingWinUser;

class CreateRankingLogic
{

    public static function sync()
    {
        // 消费者扫码排行榜
        $consumerCode = ActivitiesRankingSetting::build()->where(['user_identity' => 1, 'category' => 1])->find();
        $consumerCodeCycle = $consumerCode['cycle'];
        $consumerCodeContent = $consumerCode['content'];

        // 终端扫码排行榜
        $terminalCode = ActivitiesRankingSetting::build()->where(['user_identity' => 2, 'category' => 1])->find();
        $terminalCodeCycle = $terminalCode['cycle'];
        $terminalCodeContent = $terminalCode['content'];

        // 终端分享排行榜
        $terminalShare = ActivitiesRankingSetting::build()->where(['user_identity' => 2, 'category' => 2])->find();
        $terminalShareCycle = $terminalShare['cycle'];
        $terminalShareContent = $terminalShare['content'];

        // 终端拉新排行榜
        $terminalPull = ActivitiesRankingSetting::build()->where(['user_identity' => 2, 'category' => 3])->find();
        $terminalPullCycle = $terminalPull['cycle'];
        $terminalPullContent = $terminalPull['content'];

        $now = date('Y-m-d', time());

        // 已过期的排行榜
        $rankings = ActivitiesRanking::build()->where(['end_time' => $now])->select();
        $rankings = objToArray($rankings);
        foreach ($rankings as $ranking) {
            // 获奖用户
            $winUsers = ActivitiesRankingWinUser::build()->where(['ranking_uuid' => $ranking['uuid']])->order(['num' => 'desc', 'update_time' => 'asc'])->limit(3)->select();
            foreach ($winUsers as $winUser) {
                ActivitiesRankingWinUser::build()->where(['uuid' => $winUser['uuid']])->update(['win' => 2, 'apply' => 1]);
            }

            // 结束已过期的排行榜
            ActivitiesRanking::build()->where(['uuid' => $ranking['uuid']])->update([
                'state' => 3,
                'win_num' => ActivitiesRankingWinUser::build()->where(['ranking_uuid' => $ranking['uuid'], 'win' => 2])->count()
            ]);

            // 创建新的消费者扫码排行榜
            if ($ranking['user_identity'] == 1 && $ranking['category'] == 1) {
                ActivitiesRanking::build()->insert([
                    'uuid' => uuid(),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                    'content' => $consumerCodeContent,
                    'state' => 2,
                    'serial_number' => $ranking['serial_number'] + 1,
                    'begin_time' => $now,
                    'end_time' => date('Y-m-d', strtotime("+{$consumerCodeCycle} day", time())),
                    'user_identity' => $ranking['user_identity'],
                    'category' => $ranking['category'],
                ]);
            }
            // 创建新的终端扫码排行榜
            else if ($ranking['user_identity'] == 2 && $ranking['category'] == 1) {
                ActivitiesRanking::build()->insert([
                    'uuid' => uuid(),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                    'content' => $terminalCodeContent,
                    'state' => 2,
                    'serial_number' => $ranking['serial_number'] + 1,
                    'begin_time' => $now,
                    'end_time' => date('Y-m-d', strtotime("+{$terminalCodeCycle} day", time())),
                    'user_identity' => $ranking['user_identity'],
                    'category' => $ranking['category'],
                ]);
            }
            // 创建新的终端分享排行榜
            else if ($ranking['user_identity'] == 2 && $ranking['category'] == 2) {
                ActivitiesRanking::build()->insert([
                    'uuid' => uuid(),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                    'content' => $terminalShareContent,
                    'state' => 2,
                    'serial_number' => $ranking['serial_number'] + 1,
                    'begin_time' => $now,
                    'end_time' => date('Y-m-d', strtotime("+{$terminalShareCycle} day", time())),
                    'user_identity' => $ranking['user_identity'],
                    'category' => $ranking['category'],
                ]);
            }
            // 创建新的终端拉新排行榜
            else if ($ranking['user_identity'] == 2 && $ranking['category'] == 3) {
                ActivitiesRanking::build()->insert([
                    'uuid' => uuid(),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                    'content' => $terminalPullContent,
                    'state' => 2,
                    'serial_number' => $ranking['serial_number'] + 1,
                    'begin_time' => $now,
                    'end_time' => date('Y-m-d', strtotime("+{$terminalPullCycle} day", time())),
                    'user_identity' => $ranking['user_identity'],
                    'category' => $ranking['category'],
                ]);
            }
        }
    }
}
