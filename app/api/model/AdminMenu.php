<?php
  namespace app\api\model;

  /**
   * 菜单-模型
   * User: Yacon
   * Date: 2022-08-25
   * Time: 23:54
   */
  class AdminMenu extends BaseModel
  {
      public static function build() {
          return new self();
      }
  }