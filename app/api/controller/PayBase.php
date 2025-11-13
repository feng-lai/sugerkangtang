<?php
namespace app\api\controller;
session_start();
class  PayBase
{
    const KEY = 'qoldjhuejs234djiw7dye92eudh4idk2'; //请修改为自己的
    const MCHID = '1500335522'; //请修改为自己的
    const RPURL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    const APPID = 'APPID';//请修改为自己的
    const CODEURL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    const OPENIDURL = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
    const SECRET = 'app密码 appsecret';//请修改为自己的
    //获取用户openid 为避免重复请求接口获取后应做存储
    public function getPuyKey(){
        $this->params = [
            'mch_id'    => self::MCHID,//商户ID
            'nonce_str' => md5(time()),
            'sign_type' => 'MD5'
        ];
        //将数据发送到接口地址
        return $this->send(self::PKURL);
    }
	/**  
	* 获取签名 
	* @param array $arr
	* @return string
	*/  
    public function getSign($arr){
        //去除空值
        $arr = array_filter($arr);
        if(isset($arr['sign'])){
            unset($arr['sign']);
        }
        //按照键名字典排序
        ksort($arr);
        //生成url格式的字符串
       $str = $this->arrToUrl($arr) . '&key=' . self::KEY;
       return strtoupper(md5($str));
    }
    /**  
	* 获取带签名的数组 
	* @param array $arr
	* @return array
	*/  
    public function setSign($arr){
        $arr['sign'] = $this->getSign($arr);;
        return $arr;
    }
	/**  
	* 数组转URL格式的字符串
	* @param array $arr
	* @return string
	*/
    public function arrToUrl($arr){
        return urldecode(http_build_query($arr));
    }
    
    //数组转xml
    function ArrToXml($arr)
    {
            if(!is_array($arr) || count($arr) == 0) return '';

            $xml = "<xml>";
            foreach ($arr as $key=>$val)
            {
                    if (is_numeric($val)){
                            $xml.="<".$key.">".$val."</".$key.">";
                    }else{
                            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                    }
            }
            $xml.="</xml>";
            return $xml;

    }

    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }
	
    //Xml转数组
    function XmlToArr($xml)
    {	
            if($xml == '') return '';
            libxml_disable_entity_loader(true);
            $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//            var_dump($arr);die;
            return $arr;
    }
    function postData($url,$postfields){
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $postfields;
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        //以下是证书相关代码
        $params[CURLOPT_SSLCERTTYPE] = 'PEM';
        $params[CURLOPT_SSLCERT] = ROOT_PATH . 'public' . DS . 'certs' . DS.'apiclient_cert.pem';
//        $params[CURLOPT_SSLCERT] = 'https://flf.dankal.cn/certs/apiclient_cert.pem';
        $params[CURLOPT_SSLKEYTYPE] = 'PEM';
//        $params[CURLOPT_SSLKEY] = 'https://flf.dankal.cn/certs/apiclient_key.pem';
        $params[CURLOPT_SSLKEY] =     ROOT_PATH . 'public' . DS . 'certs' . DS.'apiclient_key.pem';
//            var_dump($params)
        curl_setopt_array($ch, $params); //传入curl参数

        $content = curl_exec($ch); //执行
//        var_dump(curl_error($ch));
        curl_close($ch); //关闭连接
        return $content;

//        $ch = curl_init();
//        $params[CURLOPT_URL] = $url;    //请求url地址
//        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
//        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
//        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
//        $params[CURLOPT_POST] = true;
//        $params[CURLOPT_POSTFIELDS] = $postfields;
//        $params[CURLOPT_SSL_VERIFYPEER] = false;
//        $params[CURLOPT_SSL_VERIFYHOST] = false;
//        $params[CURLOPT_SSLCERTPASSWD] = "pass";
//        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, 'pass');
//        //以下是证书相关代码
//             $params[CURLOPT_SSLCERTTYPE] = 'PEM';
//
//             $params[CURLOPT_SSLCERT] = 'https://flf.dankal.cn/certs/apiclient_cert.pem';
////             $params[CURLOPT_SSLCERT] = ROOT_PATH . 'public' . DS . 'certs' . DS.'apiclient_cert.pem';
//             $params[CURLOPT_SSLKEYTYPE] = 'PEM';
//             $params[CURLOPT_SSLKEY] =     'https://flf.dankal.cn/certs/apiclient_key.pem';
////             $params[CURLOPT_SSLKEY] =     ROOT_PATH . 'public' . DS . 'certs' . DS.'apiclient_key.pem';
////        var_dump($params);die;
//          curl_setopt_array($ch, $params); //传入curl参数
////        var_dump($params);die;
//          $content = curl_exec($ch); //执行
//        var_dump(curl_error($ch));
//          curl_close($ch); //关闭连接
//          return $content;
//        $ch = curl_init ();
//        $MENU_URL="https://fraud.mch.weixin.qq.com/risk/getpublickey";
//        curl_setopt ( $ch, CURLOPT_URL, $MENU_URL );
//        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
//        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
//        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
////两个证书（必填，请求需要双向证书。）
//        $zs1="https://flf.dankal.cn/certs/apiclient_cert.pem";
//        $zs2="https://flf.dankal.cn/certs/apiclient_key_8.pem";
//        curl_setopt($ch,CURLOPT_SSLCERT,$zs1);
//        curl_setopt($ch,CURLOPT_SSLKEY,$zs2);
//        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
//        curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
//        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postfields );
//        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
//        $info = curl_exec ( $ch );
////        var_dump($info);die;
//        if (curl_errno ( $ch )) {
//            echo 'Errno' . curl_error ( $ch );
//        }
//        curl_close ( $ch );
//        var_dump($info);
    }



}