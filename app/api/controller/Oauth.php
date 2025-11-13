<?php

namespace app\api\controller;

use app\api\controller\UnauthorizedException;
use app\api\controller\Send;
use think\Exception;
use think\Request;
use think\Db;
use think\Cache;

class Oauth
{
    use Send;
    
    /**
     * accessToken存储前缀
     *
     * @var string
     */
    public static $accessTokenPrefix = 'accessToken_';

    /**
     * accessTokenAndClientPrefix存储前缀
     *
     * @var string
     */
    public static $accessTokenAndClientPrefix = 'accessTokenAndClient_';

    /**
     * 过期时间秒数
     *
     * @var int
     */
    public static $expires = 72000;

    /**
     * 客户端信息
     *
     * @var
     */
    public $clientInfo;
    /**
     * 认证授权 通过用户信息和路由
     * @param Request $request
     * @return \Exception|UnauthorizedException|mixed|Exception
     * @throws UnauthorizedException
     */
    final function authenticate()
    {      

        $request = Request::instance();

        try {
            //验证授权
            $clientInfo = $this->getClient();
            $checkclient = $this->certification($clientInfo);
            if($checkclient){
                return $clientInfo;
            }
        } catch (Exception $e) {
            $this->returnmsg('402',[],[],'service_message','Invalid1 authentication credentials.','验证码错误');
        }
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return $this
     * @throws UnauthorizedException
     */
    public function getClient()
    {   
        $request = Request::instance();
        //获取头部信息
        try {
            //========关键信息在头部传入例如key，用户信息，token等，==============
            $authorization = $request->header('x-access-token');
            $access_token = $authorization;
            $clientInfo = $request->param();
            $clientInfo['token'] = $access_token;
        } catch (Exception $e) {
            return $this->returnmsg(402,$e.'Invalid authentication credentials');
        }
        return $clientInfo;
    }

    /**
     * 获取用户信息后 验证权限
     * @return mixed
     */
    public function certification($data = []){
//        ======下面注释部分是缓存验证access_token是否有效，这次使用数据库验证======
         $time = time();
         $checkclient = Db::name('admin_user_token')->field('expiry_time')->where('uuid',$data['uuid'])->where('token', $data['token'])->find();

         if(empty($checkclient['expiry_time'])){
             $this->returnmsg(401);
         }

         if(strtotime($checkclient['expiry_time']) <= $time){
             $this->returnmsg('402',[],[],'service_message','Token time out','token已过期');
         }
         return true;
    }

    /**
     * 生成签名
     * _字符开头的变量不参与签名
     */
    public function makeSign ($data = [],$app_secret = '')
    {   
        unset($data['version']);
        unset($data['signature']);
        foreach ($data as $k => $v) {
            
            if(substr($data[$k],0,1) == '_'){

                unset($data[$k]);
            }
        }
        return $this->_getOrderMd5($data,$app_secret);
    }

    /**
     * 计算ORDER的MD5签名
     */
    private function _getOrderMd5($params = [] , $app_secret = '') {
        ksort($params);
        $params['key'] = $app_secret;
        return strtolower(md5(urldecode(http_build_query($params))));
    }

}