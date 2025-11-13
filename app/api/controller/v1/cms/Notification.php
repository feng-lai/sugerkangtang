<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\NotificationLogic;

/**
 * 公告-控制器
 * User: Yacon
 * Date: 2023-03-20
 * Time: 00:25
 */
class Notification extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'page_index' => 1,      // 当前页码
      'page_size' => 10,      // 每页条目数
      'keyword_search' => '', // 关键词
      'start_time' => '',     // 开始时间
      'end_time' => '',        // 结束时间
      'is_page' => 1,        // 是否分页 1=分页 2=不分页
      'title' => '', // 标题
      'province_uuid',
      'city_uuid',
      'area_uuid',
      'state'
    ]);
    if ($request['is_page'] == 1) {
      $result = NotificationLogic::cmsPage($request, $this->userInfo);
    } else {
      $result = NotificationLogic::cmsList($request, $this->userInfo);
    }
    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = NotificationLogic::cmsDetail($id, $this->userInfo);
    $this->render(200, ['result' => $result]);
  }

  public function save()
  {
    $request = $this->selectParam([
      'title' => '', // 标题
      'content' => '', // 内容
      'province_uuid',
      'city_uuid',
      'area_uuid',
      'province',
      'city',
      'area',
      'img',
      'state'=>1
    ]);
    $result = NotificationLogic::cmsAdd($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

  public function update($id)
  {
    $request = $this->selectParam([]);
    $request['uuid'] = $id;
    unset($request['version']);
    unset($request['id']);
    $result = NotificationLogic::cmsEdit($request, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

  public function delete($id)
  {
    $result = NotificationLogic::cmsDelete($id, $this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
