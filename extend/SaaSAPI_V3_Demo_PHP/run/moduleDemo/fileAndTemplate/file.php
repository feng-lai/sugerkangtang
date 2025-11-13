<?php
use esign\emun\HttpEmun;
use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\Config;
use esign\comm\EsignUtilHelper;
/**
 * 文件服务API
 * @author  陌上
 * @date  2022/09/02 9:51
 */

$config=Config::$config;

function contentMd5($filePath){
    if(!file_exists($filePath)){
        EsignLogHelper::printMsg($filePath."文件不存在");exit;
    }
    return EsignUtilHelper::getContentBase64Md5($filePath);
}

/**
 * @param $filePath
 * @param $contentMd5
 * @return mixed
 */
function fileUploadUrl($filePath,$contentMd5,$fileName=''){
    $config=Config::$config;
    $apiaddr="/v3/files/file-upload-url";
    $requestType = HttpEmun::POST;
    $data=[
        "contentMd5" =>$contentMd5,
        "contentType" =>"application/pdf",
        "convertToPDF" =>false,
        "fileName" =>$fileName,
        "fileSize" =>filesize($filePath)
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    //获取文件上传地址
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);


    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileUploadUrl=json_decode($response->getBody())->data->fileUploadUrl;

    $fileId=json_decode($response->getBody())->data->fileId;

   //文件流put上传
    $response=EsignHttpHelper::upLoadFileHttp($fileUploadUrl,$filePath,"application/pdf");
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $fileId;
}

/**
 * @param $filePath
 * @param $contentMd5
 * @return mixed
 */
function fileUploadToHtmlUrl($filePath,$contentMd5){
    global $config ;
    $apiaddr="/v3/files/file-upload-url";
    $requestType = HttpEmun::POST;
    $data=[
        "contentMd5" =>$contentMd5,
        "contentType" =>"application/octet-stream",
        "fileName" =>"房屋租赁协议.docx",
        "fileSize" =>filesize($filePath),
        "convertToHTML"=>true
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //获取文件上传地址
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileUploadUrl=json_decode($response->getBody())->data->fileUploadUrl;
    $fileId=json_decode($response->getBody())->data->fileId;
    //文件流put上传
    $response=EsignHttpHelper::upLoadFileHttp($fileUploadUrl,$filePath,"application/octet-stream");
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $fileId;
}
