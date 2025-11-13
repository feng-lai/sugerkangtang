<?php
  namespace app\common\validate;
  use think\Validate;

  /**
   * 上传Base64文件-校验
   * User: Yacon
   * Date: 2022-09-02
   * Time: 08:26
   */
  class UploadBase64 extends Validate
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