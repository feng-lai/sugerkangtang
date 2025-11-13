<?php
namespace app\common\tools;
use think\Cache;
use app\api\model\MessageHistory;
use app\api\model\User;

class SendMsg
{
    private $url = 'https://msg.bit.edu.cn/prod-api/auth/open/platform/getToken';
    private $send_url = 'https://msg.bit.edu.cn/prod-api/msg-center/api/send/msg';
    private $platform_url =  'https://msg.bit.edu.cn/prod-api/msg-center/api/get/platform';
    private $appSecret = '2830d45e7c391d69911ce10e8b523894';
    private $appKey = 'ligonghui';
    private $token = '';
    private $platform = [];
    public static function build()
    {
        return new self();
    }

    /**
     * Author: Administrator
     * Date: 2025/2/20 0020
     * Time: 15:48
     * @param $id 工号/学号 多个用英文逗号分隔
     * @param $content 消息内容
     */
    public function send($id,$content){
        $this->getToken();
        $this->get_platform();
        $arr = [];
        $arr['receivers'] = $id;
        $messageList = [];
        foreach($this->platform as $v){
            $messageList[] = [
                'platform'=>$v['platform'],
                'title'=>'通知',
                'content'=>$content
            ];
        }
        $arr['messageList'] = $messageList;
        $res = post_url($this->send_url,json_encode($arr),['open-access-token:'.$this->token,'msg-center-app-code:'.$this->appKey,'Content-Type: application/json']);
        if($res){
            $his = new MessageHistory();
            $his->uuid = uuid();
            $his->content = $content;
            $his->msg_id = $res->msgId;
            $his->detail = json_encode($arr,JSON_UNESCAPED_UNICODE);
            $his->save();
        }
    }
    public function getToken(){
        $token = Cache::get('token');
        if($token){
            $this->token = $token;
            return '';
        }
        $token = post_url($this->url,json_encode(['appSecret'=>$this->appSecret,'appKey'=>$this->appKey]));
        if($token){
            Cache::set('token',$token->access_token,120*60);
        }
        $this->token = $token?$token->access_token:'';
        return '';
    }
    public function get_platform(){
        $res = get_url($this->platform_url,['open-access-token:'.$this->token,'msg-center-app-code:'.$this->appKey,'Content-Type: application/json']);
        if($res){
            $this->platform = $res;
        }
    }
}