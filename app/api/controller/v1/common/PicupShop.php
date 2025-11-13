<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\PicupShopLogic;

/**
 * 皮卡图片处理-控制器
 * User: Yacon
 * Date: 2023-03-28
 * Time: 20:30
 */
class PicupShop extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function index()
  {
    $request = $this->selectParam([
      'mattingType' => 1,      // 抠图类型,1：人像，2：物体，3：头像，4：一键美化，6：通用抠图，11：卡通化，17: 卡通头像，18: 人脸变清晰， 19: 照片上色
      'crop' => false, //是否裁剪至最小非透明区域，url参数，加在url后面，false不裁剪，true裁剪，不填写不裁剪
      'bgcolor' => '', // 填充背景色，url参数，加在url后面，十六进制大小RGB颜色，如FFFFFF，不填写不填充背景色
      'faceAnalysis' => false, // 人脸检测点信息，为true返回带人脸检测信息
      'outputFormat' => false, // 输出图片格式，url参数，加在url后面，可以为png,webp,jpg_$quality($quality是压缩图片的值，为0～100之间，比如jpg_75)，默认值为png
      'url' => '' // 图片的url地址
    ]);
    $result = PicupShopLogic::commonList($request, $this->userInfo);
    $this->render(200, ['result' => $result]);
  }
}
