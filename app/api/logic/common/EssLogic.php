<?php

namespace app\api\logic\common;

use app\api\model\Contestant;
use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Ess\V20201111\EssClient;
use TencentCloud\Ess\V20201111\Models\DescribeFlowTemplatesRequest;
use TencentCloud\Ess\V20201111\Models\UserInfo;
use TencentCloud\Ess\V20201111\Models\FlowCreateApprover;
use TencentCloud\Ess\V20201111\Models\CreateFlowRequest;
use TencentCloud\Ess\V20201111\Models\CreateDocumentRequest;
use TencentCloud\Ess\V20201111\Models\FormField;
use TencentCloud\Ess\V20201111\Models\StartFlowRequest;
use TencentCloud\Ess\V20201111\Models\CreateSchemeUrlRequest;
use TencentCloud\Ess\V20201111\Models\DescribeFileUrlsRequest;

/**
 * 腾讯电子签-逻辑
 * User: Yacon
 * Date: 2023-03-30
 * Time: 21:24
 */
class EssLogic
{
  //private static $secretId = "AKyDwn9UUgygogiswcUyedPESBgwnu0CGG"; // AK-测试
  //private static $secretKey = "SKkgErfD8MSa7U5yjLiobBFLHtPZOYPK1V"; // SK-测试
  //private static $templateId = "yDwfyUUgygo5gxi0UEG7pXgxysdRLMjK"; // 模板ID-测试
  //private static $userId = "yDwfLUUgygo0zec9UErhfNqR7pMODNYN"; // 用户ID-测试

  private static $secretId = "AKIDGML6br480e4OTJwLWTCseFfssdLwIPGr"; // AK-正式
  private static $secretKey = "RdaHqFQQGzb0GJIVaTAd3UkkVAev9C8r"; // SK-正式
  private static $templateId = "yDweJUUvm4ckdUjybT3uOxcBw7crma8B"; // 模板ID-正式
  private static $userId = "yDwnkUUv35f7vUCk8Eq8HrhLTIePnud7"; // 用户ID-正式
  
  private static $templateInfo; // 模板信息
  private static $flowId; // 签署流程ID
  private static $clientUserName;
  private static $clientUserMobile;

  static public function commonAdd($request, $userInfo)
  {
    if (!$userInfo['name']) throw new Exception('请先设置昵称');
    if (!$userInfo['mobile']) throw new Exception('请先设置手机号');
    if (!$request['uuid']) throw new Exception('请提供报名UUID');

    $contestant = Contestant::build()->where(['uuid' => $request['uuid'], 'user_uuid' => $userInfo['uuid']])->find();
    if (!$contestant || $contestant['state'] != 2) throw new Exception('您无法签约');

    self::$clientUserName = $userInfo['name'];
    self::$clientUserMobile = $userInfo['mobile'];

    // 如果存在签约流UUID,则直接获取签约小程序URL
    if ($contestant->sign_flow_id) {
      self::$flowId = $contestant->sign_flow_id;
    }
    // 否则创建签约流程
    else {
      // 获取模板信息
      self::DescribeFlowTemplates();
      // 创建签署流
      self::CreateFlow();
      // 创建合同文档
      self::CreateDocument();
      // 开启签署流
      self::StartFlow();
    }

    // 记录签署流UUID和合同文件UUID
    $contestant->sign_flow_id = self::$flowId;
    $contestant->file_url = self::DescribeFileUrls()['FileUrls'][0]['Url'];
    $contestant->save();
    
    // 获取签约小程序URL
    return self::CreateSchemeUrl();
  }

  /**
   * 获取客户端
   */
  private static function getClient()
  {
    $cred = new Credential(self::$secretId, self::$secretKey);
    $httpProfile = new HttpProfile();
    $httpProfile->setEndpoint("ess.tencentcloudapi.com");
    $clientProfile = new ClientProfile();
    $clientProfile->setHttpProfile($httpProfile);
    return new EssClient($cred, "", $clientProfile);
  }

  /**
   * 1.查询模板
   */
  private static function DescribeFlowTemplates()
  {
    try {

      // 实例化一个请求对象,每个接口都会对应一个request对象
      $req = new DescribeFlowTemplatesRequest();

      $params = array(
        "Operator" => array(
          "UserId" => self::$userId
        ),
        "Filters" => array(
          array(
            "Key" => "template-id",
            "Values" => array(self::$templateId)
          )
        )
      );
      $req->fromJsonString(json_encode($params));
      $resp = self::getClient()->DescribeFlowTemplates($req);

      self::$templateInfo =  objToArray(json_decode($resp->toJsonString()));
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }

  /**
   * 2.创建签署流
   */
  private static function CreateFlow()
  {
    try {

      $flowName = self::$templateInfo['Templates'][0]['TemplateName'];
      $organizationName = self::$templateInfo['Templates'][0]['Recipients'][0]['RoleName'];
      $approverName = self::$templateInfo['Templates'][0]['Creator'];
      $approverMobile = "13283887887";

      $client = self::getClient();
      $req = new CreateFlowRequest();
      $userInfo = new UserInfo();
      $userInfo->setUserId(self::$userId);
      $req->setOperator($userInfo);

      // 企业方 静默签署时type为3/非静默签署type为0
      $enterpriseInfo = new FlowCreateApprover();
      $enterpriseInfo->setApproverType(3);
      $enterpriseInfo->setOrganizationName($organizationName);
      $enterpriseInfo->setApproverName($approverName);
      $enterpriseInfo->setApproverMobile($approverMobile);
      $enterpriseInfo->setRequired(true);

      // 客户个人
      $clientInfo = new FlowCreateApprover();
      $clientInfo->setApproverType(1);
      $clientInfo->setApproverName(self::$clientUserName);
      $clientInfo->setApproverMobile(self::$clientUserMobile);
      $clientInfo->setRequired(true);

      // 企业方2 （当进行B2B场景时，允许指向未注册的企业，签署人可以查看合同并按照指引注册企业）
      $req->Approvers = [];
      array_push($req->Approvers, $enterpriseInfo);
      array_push($req->Approvers, $clientInfo);

      $req->setFlowName($flowName);
      // 请设置合理的时间，否则容易造成合同过期
      $req->setDeadLine(time() + 7 * 24 * 3600);

      $resp = $client->CreateFlow($req);

      // 输出json格式的字符串回包
      $result =  objToArray(json_decode($resp->toJsonString()));
      self::$flowId = $result['FlowId'];
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }

  /**
   * 3.创建电子文档
   */
  private static function CreateDocument()
  {
    try {
      $client = self::getClient();
      $req = new CreateDocumentRequest();
      $userInfo = new UserInfo();
      $userInfo->setUserId(self::$userId);
      $req->setOperator($userInfo);

      $req->FileNames = [];
      array_push($req->FileNames, "filename");

      // 由CreateFlow返回
      $req->setFlowId(self::$flowId);

      // 后台配置后查询获取
      $req->setTemplateId(self::$templateId);

      // $formField = new FormField();
      // 在模板配置拖入控件的界面可以查询到
      // $formField->setComponentName("********************************");
      // $formField->setComponentValue("********************************");

      // $req->FormFields = [];
      // array_push($req->FormFields, $formField);

      $resp = $client->CreateDocument($req);

      // 输出json格式的字符串回包
      return  objToArray(json_decode($resp->toJsonString()));
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }

  /**
   * 4.发起签署流程
   */
  private static function StartFlow()
  {
    try {
      $client = self::getClient();

      $req = new StartFlowRequest();

      $userInfo = new UserInfo();
      $userInfo->setUserId(self::$userId);
      $req->setOperator($userInfo);

      $req->setFlowId(self::$flowId);

      $resp = $client->StartFlow($req);

      // 输出json格式的字符串回包
      return  objToArray(json_decode($resp->toJsonString()));
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }

  /**
   * 5. 获取签署流程链接
   */
  private static function CreateSchemeUrl()
  {
    try {
      $client = self::getClient();

      $req = new CreateSchemeUrlRequest();

      $userInfo = new UserInfo();
      $userInfo->setUserId(self::$userId);
      $req->setOperator($userInfo);

      $req->setFlowId(self::$flowId);
      $req->setPathType(1);
      $req->setEndPoint('APP');

      $resp = $client->CreateSchemeUrl($req);

      // 输出json格式的字符串回包
      return  objToArray(json_decode($resp->toJsonString()));
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }

  /**
   * 获取合同文件链接
   */
  private static function DescribeFileUrls()
  {
    try {
      $client = self::getClient();

      $req = new DescribeFileUrlsRequest();

      $userInfo = new UserInfo();
      $userInfo->setUserId(self::$userId);
      $req->setOperator($userInfo);

      $req->setBusinessType("FLOW");
      $req->BusinessIds = [];
      array_push($req->BusinessIds, self::$flowId);

      $resp = $client->DescribeFileUrls($req);

      // 输出json格式的字符串回包
      return  objToArray(json_decode($resp->toJsonString()));
    } catch (TencentCloudSDKException $e) {
      throw new Exception($e);
    }
  }
}
