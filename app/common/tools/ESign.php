<?php

namespace app\common\tools;
include(ROOT_PATH . "extend/SaaSAPI_V3_Demo_PHP/EsignOpenAPI.php");
include(ROOT_PATH . "extend/SaaSAPI_V3_Demo_PHP/run/moduleDemo/fileAndTemplate/file.php");

use think\Exception;
use esign\comm\EsignHttpHelper;
use esign\emun\HttpEmun;
use esign\Config;
use esign\comm\EsignLogHelper;

class ESign
{
    public static function build()
    {
        return new self();
    }

    public function getConfig()
    {
        return Config::$config;
    }


    public function docTemplateCreateUrl($fileId, $docTemplateType)
    {
        $config = Config::$config;
        $apiaddr = "/v3/doc-templates/doc-template-create-url";
        $requestType = HttpEmun::POST;
        $data = [
            "docTemplateName" => "模板文件测试",
            "docTemplateType" => $docTemplateType,
            "fileId" => $fileId,
            "redirectUrl" => ""
        ];
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
        //发起接口请求
        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());
        $docTemplateId = json_decode($response->getBody())->data->docTemplateId;
        $fileId = $this->createByDocTemplate($docTemplateId);
        return $docTemplateId;
    }

    public function createByDocTemplate($docTemplateId, $components)
    {
        //渠道商 docTemplateId df54f55e664f4768986667f2e594d167
        $config = Config::$config;
        $apiaddr = "/v3/files/create-by-doc-template";
        $requestType = HttpEmun::POST;
        $data = [
            "docTemplateId" => $docTemplateId,
            "fileName" => "糖康堂渠道商合作协议.pdf",
            "components" => $components,
            "requiredCheck" => true
        ];
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
        //发起接口请求
        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());
        $fileId = json_decode($response->getBody())->data->fileId;
        return $fileId;
    }

    public function createByFile($fileId, $fileName, $phone, $name, $redirectUrl = "", $signFieldPosition)
    {
        EsignLogHelper::printMsg("**********基于文件发起签署调用开始**********");
        $config = Config::$config;
        $apiaddr = "/v3/sign-flow/create-by-file";
        $requestType = HttpEmun::POST;
        $data = [
            "docs" => [
                [
                    "fileId" => $fileId,
                    "fileName" => $fileName
                ]
            ],
            "signFlowConfig" => [
                "signFlowTitle" => str_replace('.pdf', '', $fileName),
                "autoFinish" => true,
                "autoStart" => true,
                "redirectConfig" => [
                    "redirectUrl" => $redirectUrl
                ]
            ],
            "signers" => [
                [
                    "signConfig" => [
                        "signOrder" => 1
                    ],
                    "signFields" => [
                        [
                            "customBizNum" => "自定义编码001",
                            "fileId" => $fileId,
                            "normalSignFieldConfig" => [
                                "autoSign" => true,
                                "signFieldStyle" => 1,
                                "signFieldPosition" => $signFieldPosition
                            ]
                        ]
                    ],
                    "signerType" => 1
                ],
                [
                    "psnSignerInfo" => [
                        "psnAccount" => $phone,
                        "psnInfo" => [
                            "psnName" => $name
                        ]
                    ],
                    "signConfig" => [
                        "signOrder" => 2
                    ],
                    "signFields" => [
                        [
                            "customBizNum" => "自定义编码002",
                            "fileId" => $fileId,
                            "normalSignFieldConfig" => [
                                "signFieldStyle" => 1,
                                "signFieldPosition" => $signFieldPosition
                            ]
                        ]
                    ],
                    "signerType" => 0
                ]
            ]
        ];

        $paramStr = json_encode($data);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());

        $flowId = false;
        if ($response->getStatus() == 200) {
            $result = json_decode($response->getBody());
            if ($result->code == 0) {
                $flowId = $result->data->signFlowId;
                EsignLogHelper::printMsg("基于文件发起签署接口调用成功，flowId: " . $flowId);
            } else {
                EsignLogHelper::printMsg("基于文件发起签署接口调用失败，错误信息: " . $result->message);
            }
        } else {
            EsignLogHelper::printMsg("基于文件发起签署接口调用失败，HTTP错误码" . $response->getStatus());
        }
        EsignLogHelper::printMsg("**********基于文件发起签署调用结束**********");
        return $flowId;
    }

    /**
     * 获取合同文件签署链接
     * /v3/sign-flow/{signFlowId}/sign-url
     */
    public function getSignUrl($flowId, $phone)
    {
        EsignLogHelper::printMsg("**********获取合同文件签署链接开始**********");
        $config = Config::$config;

        $apiaddr = "/v3/sign-flow/%s/sign-url";
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::POST;
        $data = [
            "operator" => [
                "psnAccount" => $phone
            ],
        ];
        $paramStr = json_encode($data);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());
        $url = null;
        if ($response->getStatus() == 200) {
            $url = json_decode($response->getBody())->data->url;
            EsignLogHelper::printMsg("获取合同文件签署链接调用成功，url: " . $url);
        } else {
            EsignLogHelper::printMsg("获取合同文件签署链接接口调用失败，HTTP错误码" . $response->getStatus());
        }
        EsignLogHelper::printMsg("**********获取合同文件签署链接调用结束**********");
        return $url;
    }
    /**
     * 查询签署流程详情
     * /v3/sign-flow/{signFlowId}/detail
     */
    public function queryFlowDetail($flowId){
        $config = Config::$config;

        EsignLogHelper::printMsg("**********查询签署流程详情开始**********");
        $apiaddr="/v3/sign-flow/%s/detail";
        $apiaddr = sprintf($apiaddr,$flowId);
        $requestType = HttpEmun::GET;
        $paramStr = null;
        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        EsignLogHelper::printMsg($response->getStatus());
        EsignLogHelper::printMsg($response->getBody());
        if($response->getStatus() == 200){
            EsignLogHelper::printMsg("查询签署流程详情调用成功: ".$response->getBody());
            return json_decode($response->getBody(),true)['data']['signFlowStatus'];
        }else{
            EsignLogHelper::printMsg("查询签署流程详情调用失败，HTTP错误码".$response->getStatus());
        }
        EsignLogHelper::printMsg("**********查询签署流程详情调用结束**********");
    }


}