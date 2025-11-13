<?php
  namespace app\common\validate;
  use think\Validate;

  /**
   * 赛事报名点-校验
   * User: Yacon
   * Date: 2023-03-19
   * Time: 21:10
   */
  class matchsPoint extends Validate
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