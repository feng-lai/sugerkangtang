<?php

// +----------------------------------------------------------------------
// | 微信设置
// +----------------------------------------------------------------------

return [

    'AppID' => 'wx30c6581e98f1ff7b', //公众平台appid
    'AppSecret' => 'bccef24ff6650c28a4471b915cb5dd47', //公众平台AppSecret

    'MinAppID' => 'wx81d0f4de66d8a867', // 用户端小程序 appid
    'MinAppSecret' => '0739b62ccaf7d24d1acbcc0e6f18e696', // 用户端小程序 AppSecret

    'AppMinAppID' => 'wx18b3e64ccedbef01', // app appid 开放平台-app授权登录
    'AppMinAppSecret' => 'cedee0e3801b4c59528781d892c5060b', // app AppSecret

    'MinMchId' => '1725155765', // 用户端普通商户mcid
    'MinMchKey' => 'TKTTKT0826bbbbbbbbb1239808812312', // 用户端普通商户密钥
    'MinMchSerial' => '52A946E99B2871D59BAF40C6DBAEFBC5D92A2684', // 用户端普通商户证书序号

    'MerchantAppID' => '', //服务商模式下 服务商appid
    'MerchantMchId' => '',  //服务商模式下 mcid
    'MerchantMchKey' => '', //服务商模式下 商户密钥


    'WebAppID' => '', //微信开放平台 网页应用APPID
    'WebAppSecret' => '',

    'MinWxNotifyUrl' => 'https://sw-alcohol-api.gymooit.cn/v1/common/UnionOrderPaymentNotify', // 用户端支付回调
];
