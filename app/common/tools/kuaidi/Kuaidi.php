<?php
namespace app\common\tools\kuaidi;

use think\Config;

class Kuaidi
{
    public function __construct()
    {
        $this->config = Config::get('kuaidi');
    }

    //订阅
    public function subscribe($com,$num)
    {
        // 参数设置
        $key = $this->config['key'];                            // 客户授权key
        $param = array (
            'company' => $com,             // 快递公司编码
            'number' => $num,      // 快递单号
            'from' => '',                     // 出发地城市
            'to' => '',                       // 目的地城市
            'key' => $this->config['key'],                    // 客户授权key
            'parameters' => array (
                'callbackurl' => 'http://dev.tangkt.com/v1/common/KuaiDiCallBack',          // 回调地址
                'salt' => '',                 // 加密串
                'resultv2' => '4',            // 行政区域解析
                'autoCom' => '0',             // 单号智能识别
                'interCom' => '0',            // 开启国际版
                'departureCountry' => '',     // 出发国
                'departureCom' => '',         // 出发国快递公司编码
                'destinationCountry' => '',   // 目的国
                'destinationCom' => '',       // 目的国快递公司编码
                'phone' => ''                 // 手机号
            )
        );

        // 请求参数
        $post_data = array();
        $post_data['schema'] = 'json';
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);

        $url = 'https://poll.kuaidi100.com/poll';    // 订阅请求地址
        $data = $this->post_url($url,$post_data);
        return $data;
    }

    //立即查询
    public function synquery($com,$num)
    {
        //参数设置
        $key = $this->config['key'];                        // 客户授权key
        $customer = $this->config['customer'];                   // 查询公司编号
        $param = array(
            'com' => $com,             // 快递公司编码
            'num' => $num,     // 快递单号
            'phone' => '',                // 手机号
            'from' => '',                 // 出发地城市
            'to' => '',                   // 目的地城市
            'resultv2' => '1',            // 开启行政区域解析
            'show' => '0',                // 返回格式：0：json格式（默认），1：xml，2：html，3：text
            'order' => 'desc'             // 返回结果排序:desc降序（默认）,asc 升序
        );

        //请求参数
        $post_data = array();
        $post_data['customer'] = $customer;
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $sign = md5($post_data['param'] . $key . $post_data['customer']);
        $post_data['sign'] = strtoupper($sign);

        $url = 'https://poll.kuaidi100.com/poll/query.do';    // 实时查询请求地址
        $data = $this->post_url($url,$post_data);
        return $data;
    }

    public function post_url($url,$post_data){
        // 发送post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        // 第二个参数为true，表示格式化输出json
        $data = json_decode($result, true);
        return $data;
    }
}
