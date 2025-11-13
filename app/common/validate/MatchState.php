<?php
  namespace app\common\validate;
  use think\Validate;

  /**
   * 赛事上下架-校验
   * User: Yacon
   * Date: 2023-03-19
   * Time: 15:36
   */
  class MatchState extends Validate
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