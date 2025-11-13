<?php

namespace app\api\controller;

use app\api\controller\UnauthorizedException;
use app\api\controller\Send;
use app\api\model\Captcha;
use DefaultAcsClient;
use DefaultProfile;
use think\Exception;
use think\Request;
use think\Db;
use think\Cache;
use Dysmsapi\Request\V20170525\SendSmsRequest;
//use Aliyun\Core\Config;
//include APP_PATH .'commom/aliApiSdk/aliyun-php-sdk-core/Config.php';
require '../vendor/aliApiSdk/aliyun-php-sdk-core/Config.php';
require '../vendor/aliApiSdk/Dysmsapi/Request/V20170525/SendSmsRequest.php';

/**
 * 短信接口平台
 */
class Sms
{

	function __construct()
	{
		# code...
	}

	/**
	 * 短信发送
	 * @param $mobile
	 * @param array $arr ["变量"=>"值"]
	 * @param string $model_code
	 * @return bool|mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function send_notice($mobile, $arr = [], $msg_type = '')
	{

		if (!$mobile) return false;

        $model_code = config('alimobile.templateCode'); // 短信验证码模板code

		$accessKeyId =  config('alimobile.AccessKey'); //参考本文档步骤2
		$accessKeySecret =  config('alimobile.SecretKey'); //参考本文档步骤2
		//$setSignName =  config('alidayu.signName');//参考本文档步骤2
		//短信API产品名（短信产品名固定，无需修改）
		$product = "Dysmsapi";
		//短信API产品域名（接口地址固定，无需修改）
		$domain = "dysmsapi.aliyuncs.com";
		//暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
		$region = "cn-hangzhou";

		// 服务结点
		$endPointName = "cn-hangzhou";
		$profile = \DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
		\DefaultProfile::addEndpoint($region, $endPointName, $product, $domain);
		$acsClient = new \DefaultAcsClient($profile);
		$request = new SendSmsRequest();
		//必填-短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
		$request->setPhoneNumbers($mobile);
		//必填-短信签名
        $request->setSignName(config('alimobile.signName'));

		//必填-短信模板Code

		$request->setTemplateCode($model_code);


		//选填-假如模板中存在变量需要替换则为必填(JSON格式)
		// $arr变量为空时，表示发送验证码短信
		if (count($arr) <= 0) {
			$arr['code'] = randString(4);
		}
		$paramString = json_encode($arr);

		$request->setTemplateParam($paramString);
		//选填-发送短信流水号
		//        $request->setOutId("1234");
		//发起访问请求
		$acsResponse = $acsClient->getAcsResponse($request);

		$result = json_decode(json_encode($acsResponse), true);

		date_default_timezone_set("PRC");

		if ($result['Code'] == "OK") {
			if (!empty($arr['code'])) {
				$captchaInfo = Captcha::build()->where('user_mobile', $mobile)->find();
				if (!empty($captchaInfo['uuid'])) {
					$data['code'] = $arr['code'];
					$data['create_time'] = now_time(time());
					Captcha::build()->where("user_mobile", $mobile)->update($data);
				} else {
					$data['uuid'] = uuid();
					$data['code'] = $arr['code'];
					$data['create_time'] = now_time(time());
					$data['user_mobile'] = $mobile;
					Captcha::build()->insert($data);
				}
			}
			return $result;
		} else {
			// throw new Exception($result['Message']);
			return false;
		}
	}
}
