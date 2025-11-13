<?php
  namespace app\api\model;

  /**
   * 公告-模型
   * User: Yacon
   * Date: 2023-03-20
   * Time: 00:25
   */
  class Notification extends BaseModel
  {
      public static function build() {
          return new self();
      }
  }