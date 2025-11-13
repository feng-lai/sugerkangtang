<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\UploadBase64Logic;

/**
 * 上传Base64文件-控制器
 * User: Yacon
 * Date: 2022-09-02
 * Time: 08:26
 */
class UploadBase64 extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
      'img', // BASE64串
      'type' => 'jpg' // 类型
    ]);

    if (empty($request['img'])) {
      throw new Exception('请上传图片', 400);
    }

    $result = UploadBase64Logic::commonAdd($request, $this->userInfo);

    $this->render(200, ['result' => $result]);
  }
}
