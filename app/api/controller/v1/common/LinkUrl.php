<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\LinkUrlLogic;

/**
 * 小码短链接-控制器
 * User: 
 * Date: 2023-03-20
 * Time: 11:56
 */
class LinkUrl extends Api
{
  public $restMethodList = 'post|put';


  public function _initialize()
  {
    parent::_initialize();
  }


  public function save()
  {
    $request = $this->selectParam([
      'uuid', // 用户uuid
      'origin_url', // 跳转链接，必须是以 http:// 或者 https:// 开头的链接或应用跳转链接
    ]);
    $this->check($request, "LinkUrl.save");
    $result = LinkUrlLogic::commonAdd($request);
    $this->render(200, ['result' => $result]);
  }
  /**
  public function update($id)
  {
    $request = $this->selectParam([
      'uuid' => 'user', // 终端类型 user=用户端
      'origin_url', // 用户唯一标识
      'url'
    ]);
    $result = LinkUrlLogic::commonUpdate($request);
    $this->render(200, ['result' => $result]);
  }
  **/
}
