<?php

namespace app\api\logic\common;

use app\api\model\BankCard;
use app\api\model\Retail;
use think\Config;
use think\Exception;
use app\common\tools\ESign;

/**
 * e签宝-逻辑
 */
class ESignLogic
{
    static public function commonAdd($request)
    {
        try {
            $bank_card = BankCard::build()->where('uuid',$request['bank_card_uuid'])->findOrFail();
            $retail = Retail::build()->where('uuid', $request['retail_uuid'])->findOrFail();
            if($retail['flow_id']){
                $signFlowStatus = ESign::build()->queryFlowDetail($retail['flow_id']);
                if($signFlowStatus == 2){
                    return ['msg'=>'签署已完成'];
                }
            }
            if ($retail['type'] == 1) {
                $filePath = ROOT_PATH . 'public/糖康堂推广员合作协议.pdf';
                $fileName = '糖康堂推广员合作协议.pdf';
                $docTemplateId = '796dff702235458bb473504475d99b49';
                $signFieldPosition = [
                    "positionPage" => "3",
                    "positionX" => 249,
                    "positionY" => 716
                ];
            } else {
                $filePath = ROOT_PATH . '糖康堂渠道商合作协议.pdf';
                $fileName = '糖康堂渠道商合作协议.pdf';
                $docTemplateId = 'df54f55e664f4768986667f2e594d167';
                $signFieldPosition = [
                    "positionPage" => "2",
                    "positionX" => 289,
                    "positionY" => 160
                ];
            }
            /**
             * $flowId = ESign::build()->createByFile($retail->phone,$filePath,$fileName);
             * return $flowId;
             * **/
            $components = [
                [
                    'componentKey'=>'name',
                    'componentValue'=>$retail['name'],
                ],
                [
                    'componentKey'=>'contactName',
                    'componentValue'=>$retail['contact_name']?$retail['contact_name']:'无',
                ],
                [
                    'componentKey'=>'address',
                    'componentValue'=>$retail['address'].$retail['address_detail']?$retail['address'].$retail['address_detail']:'无',
                ],
                [
                    'componentKey'=>'phone',
                    'componentValue'=>$retail['phone']?$retail['phone']:'无',
                ],
                [
                    'componentKey'=>'date',
                    'componentValue'=>date('Y-m-d'),
                ]
            ];
            $fileId = ESign::build()->createByDocTemplate($docTemplateId,$components);
            $flowId = ESign::build()->createByFile($fileId,$fileName,$bank_card['phone'],$bank_card['name'],$request['redirect_url'],$signFieldPosition);
            Retail::build()->where('uuid',$retail['uuid'])->update(['flow_id'=>$flowId]);
            $url = ESign::build()->getSignUrl($flowId,$bank_card['phone']);
            print_r($url);exit;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
