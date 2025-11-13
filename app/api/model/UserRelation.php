<?php
  namespace app\api\model;

  /**
   * 用户分享关系-模型
   * User: Yacon
   * Date: 2023-03-20
   * Time: 16:29
   */
  class UserRelation extends BaseModel
  {
      public static function build() {
          return new self();
      }
      //绑定关系
      public function to_relation($user_uuid,$new_user_uuid){
        if($user_uuid == $new_user_uuid){
          return true;
        }
        if(self::build()->where('new_user_uuid' , $new_user_uuid)->count()){
          return true;
        }
        $level = self::build()->where('new_user_uuid' , $user_uuid)->value('level');
        if ($level) {
          $level ++;
        }else{
          $level = 1;
        }
        //计算分享等级
        $userRelation = self::build();
        $userRelation['uuid'] = uuid();
        $userRelation['create_time'] = now_time(time());
        $userRelation['update_time'] = now_time(time());
        $userRelation['user_uuid'] = $user_uuid;
        $userRelation['new_user_uuid'] = $new_user_uuid;
        $userRelation['level'] = $level;
        $userRelation->save();
      }
  }