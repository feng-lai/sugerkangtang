<?php

/**
 * 授权基类，所有获取access_token以及验证access_token 异常都在此类中完成
 */

namespace app\api\controller;

use app\api\model\AdminToken;
use app\api\model\AdminUserToken;
use app\api\model\Order;
use app\api\model\StaffUserToken;
use app\api\model\UserToken;
use app\api\model\MarketUserToken;
use think\Controller;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Request;
use think\Response;
use think\response\Json;
use think\response\Jsonp;
use think\response\Xml;
use app\api\logic\cms\LoginLogic;

class Api extends Controller
{
    use Send;
    protected $param = [];
    protected $userId;
    protected $userInfo;
    protected $pageIndex;
    protected $pageSize;
    /**
     * 对应操作
     * @var array
     */
    public $methodToAction = [
        'get' => 'read',
        'post' => 'save',
        'put' => 'update',
        'delete' => 'delete',
        'patch' => 'patch',
        'head' => 'head',
        'options' => 'options',
    ];

    /**
     * 允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post|put|delete|patch|head|options';
    /**
     * 默认不验证
     * @var bool
     */
    public $apiAuth = true;

    protected $request;
    /**
     * 当前请求类型
     * @var string
     */
    protected $method;
    /**
     * 当前资源类型
     * @var string
     */
    protected $type;

    public static $app;
    /**
     * 返回的资源类的
     * @var string
     */
    protected $restTypeList = 'json';
    /**
     * REST允许输出的资源类型列表
     * @var array
     */
    protected $restOutputType = [
        'json' => 'application/json',
    ];

    /**
     * 客户端信息
     */
    protected $clientInfo;
    /**
     * 控制器初始化操作
     */

    public function _initialize()
    {
        $request = Request::instance();
        $this->request = $request;
        //         $this->userInfo = $this->appValidateToken();
        //检查资源类型
        $this->init();
        //$this->clientInfo = $this->checkAuth();  //接口检查
    }

    public function __construct(Request $request = null)
    {
        parent::__construct();
        $this->param = $request->param();
        //$this->getPageIndexAndSize();
    }

    /**
     * 获取页码和每页数量
     * @param int $defaultIndex
     * @param int $defaultSize
     * @param int $sizeLimit
     */
    protected function getPageIndexAndSize($defaultIndex = 1, $defaultSize = 20, $sizeLimit = 9999)
    {
        $this->pageIndex = isset($this->param['page_index']) ? $this->param['page_index'] : null;
        $this->pageSize = isset($this->param['page_size']) ? $this->param['page_size'] : null;
        $this->pageIndex = $this->pageIndex ?: $defaultIndex;
        $this->pageSize = $this->pageSize ?: $defaultSize;

        $this->checkSingle($this->pageIndex, 'page_index', 'Base.page_index');
        $this->checkSingle($this->pageSize, 'page_size', 'Base.page_size');

        if ($this->pageSize > $sizeLimit) {
            self::returnmsg(403, [], [], "", "param error", "参数不合法");
        }
    }
    /**
     * 单个参数验证
     *
     * @param $value
     * @param $key
     * @param $validate
     */
    protected function checkSingle($value, $key, $validate)
    {
        $this->check([$key => $value], $validate);
    }

    /**
     * 初始化方法
     * 检测请求类型，数据格式等操作
     */
    public function init()
    {
        $request = Request::instance();
        $ext = $request->ext();
        if ('' == $ext) {
            // 自动检测资源类型
            $this->type = $request->type();
        } elseif (!preg_match('/\(' . $this->restTypeList . '\)$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }
        $this->setType();
        // 请求方式检测
        $method = strtolower($request->method());
        $this->method = $method;
        if (false === stripos($this->restMethodList, $method)) {
            throw new HttpResponseException(json((object)array('error' => 405, 'message' => 'No routing path can be found for the request.'), 200));
        }
    }

    /**
     * 检测客户端是否有权限调用接口
     */
    public function checkAuth()
    {
        $baseAuth = Factory::getInstance(\app\api\controller\Oauth::class);

        $clientInfo = $baseAuth->authenticate();

        return $clientInfo;
    }
    /**
     * 空操作
     * @return Response|Json|Jsonp|Xml
     */
    public function _empty()
    {
        return $this->render(200);
    }
    protected function getParam($key, $defaultValue = null)
    {

        $value = $this->param[$key] ?? $defaultValue;
        if(!$value){
            $value = $defaultValue;
        }

        return is_string($value) ? trim($value) : $value;

    }
    protected function check($data, $validate)
    {
        $validateResult = $this->validate($data, $validate);
        if ($validateResult !== true) {
            self::returnmsg(400, [], [], "", "param error", $validateResult);
        }
    }
    protected function verify($data, $validate)
    {
        $validateResult = $this->validate($data, $validate);
        if ($validateResult !== true) {
            throw new ValidateException($validateResult);
        }
    }
    /**
     * 获取多个参数，keysArray为空时获得全部参数
     * 注意键名不能为数字
     *
     * @param array|null $keysArray
     * @return array
     */
    protected function selectParam($keysArray = null)
    {
        if ($keysArray) {
            $paramResult = [];
            foreach ($keysArray as $key => $value) {
                if (is_int($key)) {
                    // 没有 key , 用数字作为key
                    $paramResult[$value] = $this->getParam($value);
                } else {
                    // 有 key 和 value , value为参数默认值
                    $paramResult[$key] = $this->getParam($key, $value);
                }
            }
            return $paramResult;
        } else {
            return $this->param;
        }
    }

    //todo

    /**
     * @return array|false|\PDOStatement|string|\think\Model|void
     * 市场用户后台管理Token验证
     */
    protected function marketValidateToken()
    {
      $token = get_token();
      $bool = MarketUserToken::build()->vali($token);
      if (!$bool) {
        self::returnmsg(401, [], [], "", "AUTH ERROR", "鉴权失败");
      }
      return $bool;
    }

    /**
     * @return array|false|\PDOStatement|string|\think\Model|void
     * CMS后台管理Token验证
     */
    protected function cmsValidateToken()
    {
        $token = get_token();
        $bool = AdminToken::build()->vali($token);
        if (!$bool) {
            self::returnmsg(401, [], [], "", "AUTH ERROR", "鉴权失败");
        }
        return $bool;
    }

    /**
     * @return array|false|\PDOStatement|string|\think\Model|void
     * 客户端Token验证
     */
    protected function miniValidateToken()
    {
        $token = get_token();
        $bool = UserToken::build()->vali($token);
        if (!$bool) {
            self::returnmsg(401, [], [], "", "AUTH ERROR", "鉴权失败");
        }
        $bool = objToArray($bool);
        return $bool;
    }

    /**
     * @return array|false|\PDOStatement|string|\think\Model|void
     * 客户端Token验证
     */
    protected function miniValidateToken2()
    {
        $token = get_token();
        $bool = UserToken::build()->vali2($token);
        if ($bool) {
            $bool = objToArray($bool);
        }else{
            $bool = '';
        }
        return $bool;
    }
    protected function getCasToken(){

    }
}
