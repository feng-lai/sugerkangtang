<?php

namespace app\api\logic\common;

use app\api\controller\v1\common\UploadBase64;
use app\common\tools\Image;
use think\Exception;
use think\Config;
use think\Db;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\Response\QrCodeResponse;

/**
 * 获取小程序码-逻辑
 * User:
 * Date: 2022-04-17
 * Time: 09:59
 */
class GetAppQrCodeLogic
{
  static public function commonList($request, $userInfo)
  {
    // 扫描二维码后跳转的地址
    $qrCode = new QrCode($request['path']);
    // 内容区域宽高,默认为300
    $qrCode->setSize($request['width']);
    // 外边距大小,默认为10
    $qrCode->setMargin(10);
    // 设置编码
    $qrCode->setEncoding('UTF-8');
    // 设置容错等级
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
    // 设置二维码颜色,默认为黑色
    $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
    // 设置二维码背景色,默认为白色
    $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
    // 设置二维码下方的文字
    //$qrCode->setLabel('个人技术博客网站', 11, null, LabelAlignment::CENTER());
    ##### 二维码中的logo #####
    // $qrCode->setLogoPath('demo1/logo.jpg');
    // $qrCode->setLogoSize(100, 90);
    // $qrCode->setLogoWidth(100);
    // $qrCode->setLogoHeight(90);
    ##### 二维码中的logo / #####
    // 启用内置的验证读取器(默认情况下禁用)
    $qrCode->setValidateResult(false);
    ########## 二维码三种显示方式 ##########
    // 二维码输出在浏览器上
    // header('Content-Type: ' . $qrCode->getContentType());
    // echo $qrCode->writeString();
    // 二维码存在本地
    // $qrCode->writeFile('3.png');
    // 返回数据URI
    // data:image/png;base64,iVBORwxxx
    $dataUri = $qrCode->writeDataUri();
    // Base64文件上传并返回文件路径

    $result = UploadBase64Logic::commonAdd(['img' => str_replace('data:image/png;base64,','',$dataUri), 'type' => 'png'], $userInfo);
    return $result;
  }
}
