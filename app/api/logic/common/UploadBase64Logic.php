<?php

namespace app\api\logic\common;

use app\common\tools\AliOss;
use think\Exception;
use think\Db;

/**
 * 上传Base64文件-逻辑
 * User: Yacon
 * Date: 2022-09-02
 * Time: 08:26
 */
class UploadBase64Logic
{

  static public function commonAdd($request, $userInfo)
  {

    $img = $request['img'];
    $type = $request['type'];
    $source = 'tangtangtang';

    // 图片临时目录
    $basePath = ROOT_PATH . 'public' . DS . 'upload';
    if(!file_exists($basePath)){
      mkdir($basePath);
    }

    // 文件类型
    $types = ['jpg', 'gif', 'png', 'jpeg'];
    if (!in_array($type, $types)) {
      throw new Exception('类型不匹配', 400);
    }

    $img = str_replace(array('_', '-'), array('/', '+'), $img);
    $img   = base64_decode($img);

    $photo = uuid();
    $photo = $photo . '.' . $type;

    // 存储到本地
    $tmp_img = $basePath . DS  . $photo;

    try {
      file_put_contents($tmp_img, $img);
    } catch (Exception $e) {
      throw new Exception('写入文件失败:' . $e->getMessage());
    }

    // 上传到阿里云
    $photo = $source . '/' . $photo;
    try {
      $oss  = new AliOss();
      $oss->uploadOss($tmp_img, $photo);
      @unlink($tmp_img);
      return $photo;
    } catch (Exception $e) {
      @unlink($tmp_img);
      throw new Exception('上传失败:' . $e->getMessage());
    }
  }
}
