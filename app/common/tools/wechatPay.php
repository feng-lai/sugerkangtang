<?php
/**
 * Created by PhpStorm.
 * User: fio
 * Date: 2017/2/21
 * Time: 下午4:47
 */

namespace app\common\tools;

use think\Config;
use think\Exception;

class wechatPay
{
    private static function get_config()
    {
        $config = Config::get('wechat');
        return [
            'appid' => $config['MinAppID'],
            'pay_mchid' => $config['MinMchId'],
            'pay_apikey' => $config['MinMchKey'],
        ];
    }


    public static function store($price, $order_sn,$openid)
    {
        try {
            $config = self::get_config();
            //统一下单参数构造
            $unifiedorder = array(
                'appid' => $config['appid'],
                'mch_id' => $config['pay_mchid'],
                'nonce_str' => self::getNonceStr(),
                'body' => '商品购买',
                'out_trade_no' => $order_sn,
                'total_fee' => $price * 100,
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
                'notify_url' => request()->domain() . '/v1/mini/WxPayCallback',
                'trade_type' => 'JSAPI',
                'openid' => $openid,
            );
            $unifiedorder['sign'] = self::makeSign($unifiedorder);
            //请求数据
            $xmldata = self::array2xml($unifiedorder);
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $res = self::curl_post_ssl($url, $xmldata);
            if (!$res) {
                return ['msg' => '无法连接服务器'];
            }
            $content = self::xml2array($res);
            $result_code = isset($content['result_code']) ? $content['result_code'] : '';
            $return_code = isset($content['return_code']) ? $content['return_code'] : '';
            if (strval($result_code) == 'FAIL') {
                return ['msg' => strval($content['err_code_des'])];
            }
            if (strval($return_code) == 'FAIL') {
                return ['msg' => strval($content['return_msg'])];
            }
            $data = self::pay($content['prepay_id']);
            return $data;
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    /**
     * 进行支付接口签名
     * @param string $prepay_id 预支付ID(调用prepay()方法之后的返回数据中获取)
     * @return  json的数据
     */
    public static function pay($prepay_id)
    {
        $config = self::get_config();
        $data = array(
            'appId' => $config['appid'],
            'timeStamp' => (string)time(),
            'nonceStr' => self::getNonceStr(),
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5'
        );

        $data['paySign'] = self::makeSign($data);

        return $data;
    }

    /**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     */
    protected static function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . $this->array2xml($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }

    /**
     * 将xml转为array
     * @param string $xml xml字符串
     * @return array    转换得到的数组
     */
    protected static function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    protected static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 生成签名
     * @return 签名
     */
    protected static function makeSign($data)
    {
        $config = self::get_config();
        //获取微信支付秘钥
        $key = $config['pay_apikey'];

        // 去空
        $data = array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY
        //$config=$this->config;
        $string_sign_temp = $string_a . "&key=" . $key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);
        return $result;
    }

    /**
     * 微信支付发起请求
     */
    protected static function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}
