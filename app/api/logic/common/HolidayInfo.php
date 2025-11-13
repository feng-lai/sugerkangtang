<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Holiday;
use app\api\model\Message;
use app\common\wechat\Util;

class HolidayInfo
{
    public static function sync()
    {
        $startDate = date('Y-m-d', strtotime('+1 month', time()));
        $endDate = date('Y-m-d', strtotime('+2 month', time()));
        $dates = getDateFromRange($startDate, $endDate);
        $datas = [];
        foreach ($dates as $date) {
            // 状态 1=工作日 2=周末 3=节假日 4=调班
            $data = [
                'uuid' => uuid(),
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
                'date' => $date,
                'state' => 1
            ];
            $result = Util::getCurl("https://calc.ygcf.info/api/v1/workday/count?start_date={$date}&end_date={$date}&token=620c52e802993");
            $result = objToArray(json_decode($result));
            // 节假日
            if ($result['data']['holiday']) {
                $data['state'] = 3;
            }
            // 调班
            else if ($result['data']['dayOff']) {
                $data['state'] = 4;
            }
            // 周末
            else if ($result['data']['weekend']) {
                $data['state'] = 2;
            }
            array_push($datas, $data);
        }
        Holiday::build()->insertAll($datas);
    }
}
