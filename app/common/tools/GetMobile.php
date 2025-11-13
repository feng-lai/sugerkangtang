<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/11/17
 * Time: 17:21
 *
 */
namespace app\common\tools;


use AlibabaCloud\SDK\Dypnsapi\V20170525\Dypnsapi;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetMobileRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use app\api\logic\common\loginByCodeLogic;

class GetMobile
{
  /**
   * 使用AK&SK初始化账号Client
   * @param string $accessKeyId
   * @param string $accessKeySecret
   * @return Dypnsapi Client
   */
  public static function createClient($accessKeyId, $accessKeySecret){
    $config = new Config([
      // 必填，您的 AccessKey ID
      "accessKeyId" => $accessKeyId,
      // 必填，您的 AccessKey Secret
      "accessKeySecret" => $accessKeySecret
    ]);
    // 访问的域名
    $config->endpoint = "dypnsapi.aliyuncs.com";
    return new Dypnsapi($config);
  }

  /**
   * @param string[] $args
   * @return void
   */
  public static function main($args){
    // 请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID 和 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
    // 工程代码泄露可能会导致 AccessKey 泄露，并威胁账号下所有资源的安全性。以下代码示例使用环境变量获取 AccessKey 的方式进行调用，仅供参考，建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html
    $client = self::createClient(config('alimobile.AccessKey'), config('alimobile.SecretKey'));
    $getMobileRequest = new GetMobileRequest([
      "accessToken" => $args['access_token']
    ]);
    $runtime = new RuntimeOptions([]);
    try {
      // 复制代码运行请自行打印 API 的返回值
      $res = $client->getMobileWithOptions($getMobileRequest, $runtime);
      return $res->body->getMobileResultDTO->mobile;
    }
    catch (Exception $error) {
      throw new \think\Exception($error->getMessage(), 500);
    }
  }
}