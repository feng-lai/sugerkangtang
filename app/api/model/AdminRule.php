<?php
  namespace app\api\model;

  /**
   * 管理员角色-模型
   * User: Yacon
   * Date: 2022-08-25
   * Time: 23:51
   */
  class AdminRule extends BaseModel
  {
      public static function build() {
          return new self();
      }
  }