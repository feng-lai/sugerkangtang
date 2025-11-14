<?php

/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2018/5/4
 * Time: 上午10:50
 */
return [
    'AccessKey' => 'LTAI5tJSGCvZFxGLXMAnq99x',
    'SecretKey' => 'wqxEeuoTKnWU5Ss6fdIOzM5UNfmsNM',
    'SignName' => '',
    'smsType' => [
        'login' => [
            'signName' => '执剑者',
            'templateCode' => 'SMS_461660217',
            'smsParam' => [
                'code' => '',
            ],
        ],
        'changePhone' => [
            'signName' => '小桔帮帮',
            'templateCode' => 'SMS_221600040',
            'smsParam' => [
                'code' => '',
            ],
        ],
        // 评估任务通知
        'evaluationAllot' => [
            'signName' => '小桔帮帮',
            'templateCode' => 'SMS_234137853',
            'smsParam' => [
                'code' => '',
            ],
        ],
        // 护理院预约成功通知
        'reserve' => [
            'signName' => '小桔帮帮',
            'templateCode' => 'SMS_243196234',
            'smsParam' => [
                'code' => '',
            ],
        ],
        // 月度结算
        'bill' => [
            'signName' => '小桔帮帮',
            'templateCode' => 'SMS_234142804',
            'smsParam' => [
                'code' => '',
            ],
        ]
    ],
];
