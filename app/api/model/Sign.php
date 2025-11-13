<?php

namespace app\api\model;
use app\api\model\Config;

/**
 * 签到-模型
 * User:
 * Date:
 * Time:
 */
class Sign extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getScore($num){
      $config = Config::build()->select();
      $res = [];
      foreach($config as $k=>$v){
        $res[$v->key] = $v->value;
      }

      switch ($num){
        case 1:
          $score = $res['DAY_SIGN_ONE'];
          break;
        case 2:
          $score = $res['DAY_SIGN_TWO'];
          break;
        case 3:
          $score = $res['DAY_SIGN_THREE'];
          break;
        case 4:
          $score = $res['DAY_SIGN_FOUR'];
          break;
        case 5:
          $score = $res['DAY_SIGN_FIVE'];
          break;
        case 6:
          $score = $res['DAY_SIGN_SIX'];
          break;
        case 7:
          $score = $res['DAY_SIGN_SEVEN'];
          break;

      }
      return $score;
    }

    public function week_info($user_uuid){
      $time = time(); // 可设定日期
      $week_day_num = date('w', $time);
      if ($week_day_num == 0) {
        // 当前是周日的情况
        $one = date('Y-m-d', $time);
      } else {
        $one = date('Y-m-d', strtotime("-" . ($week_day_num) . " day", $time));
      }
      $weekarray = array("日", "一", "二", "三", "四", "五", "六");
      for($x = 0; $x <= 6; $x++){
        $day = date('Y-m-d',strtotime($one .'+'.$x.' days'));

        $is_sign = self::build()::where('user_uuid','=',$user_uuid)->whereTime('create_time','between',[$day.' 00:00:00',$day.' 24:00:00'])->find();
        $score = Config::build()->where('key','=','DAY_SIGN_ONE')->value('value');
        //获得的积分
        if($is_sign){
          $info = Score::build()::where('user_uuid','=',$user_uuid)->where('sign_uuid',$is_sign->uuid)->find();
          $score = $info->score;
        }else{
          $score = $this->getScore(1);
        }
        /**
        //最新签到数据之后的积分数显示
        $new = self::where('user_uuid','=',$user_uuid)->order('create_time desc')->find();
        if($new){
          if($day > date('Y-m-d',strtotime($new->create_time)) && date('Y-m-d',strtotime($new->create_time))>$one){
            if($new->num < 7){
              $num = $new->num+date('d',strtotime($one .'+'.$x.' days') - strtotime($new->create_time));
              if($num >=7){
                $num = 7;
              }
              $score = $this->getScore($num);
            }else{
              $score = $this->getScore(7);
            }
          }else if(date('Y-m-d',strtotime($new->create_time)) < $one){
            $score = $this->getScore($x+1);
          }
        }else{
          $score = $this->getScore($x+1);
        }
         **/
        $data[$x] = ['week'=>$weekarray[date('w',strtotime($day))],'is_sign'=>$is_sign?1:0,'day'=>$day,'score'=>$score];
      }
      return array_values($data);
    }

}
