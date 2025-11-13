<?php
  namespace app\common\validate;
  use think\Validate;

  /**
   * 公告-校验
   * User: Yacon
   * Date: 2023-03-20
   * Time: 00:25
   */
  class Notification extends Validate
  {
    protected $rule = [
      
    ];

    protected $field = [
      
    ];

    protected $message = [
      
    ];

    protected $scene = [
      'list' => [],
      'save' => [],
      'edit' => []
    ];
  }