<?php

use esign\emun\HttpEmun;
use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\Config;
header("Content-type:text/html;charset=utf-8");
include("../../../EsignOpenAPI.php");

/**
 * 认证和授权服务-企业API
 * @author  陌上
 * @date  2022/09/02 9:51
 */

$config=Config::$config;
//OF-2203140677080009

//场景：1代表认证；2代表授权
$scence=1;     //场景编号
$orgName="xxxx";
switch ($scence){
    case "1":
        //获取企业实名url
        $realNameStatus=organizationsIdentityInfo($orgName,$scence);

        if ($realNameStatus==1){
            echo "客户已实名，请继续后续业务逻辑";
        }else {
            echo "客户未实名，请实名";
            orgAuthUrl($orgName);;
        }
        break;
    case "2":
        //获取企业授权url
        //$orgId查询个人实名状态获取
        $orgId="xxxx";
        $authorizeUserInfo=organizationsIdentityInfo($orgName,$scence);
        if ($authorizeUserInfo){
            $response=organizationsAuthorizedInfo($orgId);
            //获取过期时间
            $expireTime=json_decode($response->getBody())->data->authorizedInfo[0]->expireTime;
            //计算是否授权过期
            $num=expireTime($expireTime);
            if($num>0){
                echo "授权有效";
            }else{
                echo "请重新授权";
                orgAuthorizehUrl($orgName);
            }

        }else{
            echo "请授权";
            orgAuthorizehUrl($orgName);
        }
        break;
    default:
        EsignLogHelper::printMsg("场景选择错误");
}

/**
 * @param $expireTime
 * @return float
 */
function expireTime($expireTime){
    list($t1, $t2) = explode(' ', microtime());
    $nowTime=(float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    $num=$expireTime-$nowTime;
    return $num;
}

/**
 * 查询企业授权详情
 * @param $orgId
 * @return \esign\comm\EsignResponse
 */
function organizationsAuthorizedInfo($orgId){
    global $config ;
    $apiaddr="/v3/organizations/".$orgId."/authorized-info";
    $requestType = HttpEmun::GET;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], "", $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, "");
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $response;
}

/**
 * 查询企业实名状态
 * @param $orgName
 * @param $scence
 * @return mixed
 */
function organizationsIdentityInfo($orgName,$scence){
    global $config ;
    $apiaddr="/v3/organizations/identity-info?orgName=".$orgName;
    $requestType = HttpEmun::GET;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], "", $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, "");
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $realnameStatus=json_decode($response->getBody())->data->realnameStatus;
    $authorizeUserInfo=json_decode($response->getBody())->data->authorizeUserInfo;
    if ($scence==1){
        return $realnameStatus;
    }else{
        return $authorizeUserInfo;
    }

}

/**
 * 企业实名
 * @param $orgName
 */
function orgAuthUrl($orgName){
    global $config;
    $apiaddr="/v3/org-auth-url";
    $requestType = HttpEmun::POST;
    $data=[
        "orgAuthConfig"=>[
            "orgName"=>$orgName,
            "orgInfo"=>[
                "orgIDCardNum"=>"",
                "orgIDCardType"=>"CRED_ORG_USCC"

            ],
            "transactorInfo"=>[
                "psnAccount"=>"",
                "psnInfo"=>[
                    "psnIDCardNum"=>"",
                    "psnName"=>""
                ]
            ],
        ],
        "redirectConfig"=>[
            "redirectUrl"=>"https://www.baidu.com/"
        ],
        "notifyUrl"=>"",
        "clientType"=>"ALL",
        "appScheme"=>""
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}

/**
 * 企业授权
 * @param $orgName
 */
function orgAuthorizehUrl($orgName){
    global $config;
    $apiaddr="/v3/org-auth-url";
    $requestType = HttpEmun::POST;
    $data=[
        "orgAuthConfig"=>[
            "orgName"=>$orgName,
            "orgInfo"=>[
                "orgIDCardNum"=>"",
                "orgIDCardType"=>" "

            ],
            "transactorInfo"=>[
                "psnAccount"=>"",
                "psnInfo"=>[
                    "psnIDCardNum"=>"",
                    "psnName"=>""
                ]
            ],
        ],
        "authorizeConfig"=>[
            "authorizedScopes"=>[
                "get_org_identity_info","get_psn_identity_info","org_initiate_sign","psn_initiate_sign","manage_org_resource","manage_psn_resource","use_org_order"
            ]
        ],
        "redirectConfig"=>[
            "redirectUrl"=>"https://www.baidu.com/"
        ],
        "notifyUrl"=>"",
        "clientType"=>"ALL",
        "appScheme"=>""
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}
