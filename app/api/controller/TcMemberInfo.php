<?php

namespace app\api\controller;

use app\api\controller\Send;
use app\api\controller\SimpleString;
use app\api\controller\SimpleStringCypher;
use app\api\controller\Sms;
use app\api\controller\v1\app\Communal;
use app\api\model\AppBrand;
use app\api\model\AppLevel;
use app\api\model\AppLogin;
use app\api\model\AppMemberCard;
use app\api\model\AppMemberTicket;
use app\api\model\AppTcTicket;
use app\api\model\AppTicket;
use app\api\model\AppUserInfo;
use app\api\model\AppUserTicket;
use think\Controller;
use think\Request;
use app\api\controller\Api;
use think\Response;
use app\api\controller\UnauthorizedException;

class TcMemberInfo
{

    public function tcMember($uuid){
        //轮询天财会员卡信息
        $MemberTicketModel=new AppMemberCard();
        $where="user_uuid='{$uuid}' and is_binding='1'";
        $memberinfo=$MemberTicketModel->where($where)->select();
        return $memberinfo;
    }

    public function enableBalanceMoney($uuid){

        $MemberTicketModel=new AppMemberCard();
        $where="user_uuid='{$uuid}' and is_binding='1'";
        $memberinfo=$MemberTicketModel->where($where)->field('*')->order('enableBalanceMoney desc')->select()->toArray();

        return $memberinfo;
    }
    public function cardScore($uuid){

        $MemberTicketModel=new AppMemberCard();
        $where="user_uuid='{$uuid}' and is_binding='1'";
        $memberinfo=$MemberTicketModel->where($where)->field('*')->order('cardScore desc')->select()->toArray();

        return $memberinfo;
    }

    public function transactionRecord($uuid){

        $MemberTicketModel=new AppMemberCard();
        $where="user_uuid='{$uuid}' and is_binding='1'";
        $memberinfo=$MemberTicketModel->where($where)->field('*')->order('enableBalanceMoney desc')->select()->toArray();
        $tcCurl=new Communal();
        $count=count($memberinfo);
        $url="QueryCardTransactionInfo";
        $allArray=array();
        for($i=0;$i<$count;$i++){
            $parm['cardno']=$memberinfo[$i]['cardNo'];
            $parm['needpwd']="NOTNEED";
            $json_data=json_encode($parm);
            $result=$tcCurl->tc_curl($json_data,$url);
            $allArray=array_merge_recursive($allArray,json_decode($result,true));
        }
        return $allArray;
    }

    public function allMember($memberArray){
        //查询天财会员卡当前余额当前积分
        $tcCurl=new Communal();
        $count=count($memberArray);
//        var_dump($count);die;
        $url="QueryCardInfo";
        $allArray=array();
        for($i=0;$i<$count;$i++){
            $parm['cardno']=$memberArray[$i]['cardNo'];
            $parm['needpwd']="NOTNEED";
//            var_dump($parm)
            $json_data=json_encode($parm);
            $result=$tcCurl->tc_curl($json_data,$url);
//

            $allArray=array_merge_recursive($allArray,json_decode($result,true));
        }

//        var_dump($result);die;
        return $allArray;
//        var_dump($allArray);die;

    }
}
