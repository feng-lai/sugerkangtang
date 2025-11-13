<?php

/**
 * 向客户端发送相应基类
 */

namespace app\api\controller;

use app\api\model\AdminLog;
use think\Db;
use think\Response;
use think\response\Json;
use think\response\Jsonp;
use think\response\Redirect;
use think\response\View;
use think\response\Xml;

trait Send
{

    /**
     * 默认返回资源类型
     * @var string
     */
    protected $restDefaultType = 'json';

    /**
     * 设置响应类型
     * @param null $type
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = (string)(!empty($type)) ? $type : $this->restDefaultType;
        return $this;
    }

    /**
     * 失败响应
     * @param int $error
     * @param string $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return Response|Json|Jsonp|Xml
     */
    public function sendError($error = 400, $message = 'error', $code = 400, $data = [], $headers = [], $options = [])
    {
        $responseData['error'] = (int)$error;
        $responseData['message'] = (string)$message;
        if (!empty($data)) $responseData['data'] = $data;
        $responseData = array_merge($responseData, $options);
        return $this->response($responseData, $code, $headers);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|Json|Jsonp|Redirect|Xml
     */
    public function render($code, $result = '')
    {

        // 组合返回数据
        http_response_code($code);
        //session_start();
        if (is_array($result)) {
            foreach ((array)$result as $name => $data) {
                // (array)强制转化，增强程序的健壮性
                if (strpos($name, '.list')) {
                    // strrpos() - 查找字符串在另一字符串中最后一次出现的位置（区分大小写）
                    $model = trim(str_replace('.list', '', $name));
                    // 替换函数
                    foreach ((array)$data as $key => $value) {
                        $result[$name][$key] = $value;
                    }
                } else {
                    $model = trim($name);
                    $result[$name] = $data;
                }
            }
        }
        //$this->adminLog($result);
        // 输出中文字符的时候会进行json格式转码
        //$data =  sensitive_word_check(json_encode(
            //$result,
            //JSON_UNESCAPED_UNICODE
        //));
        $data =  json_encode(
            $result,
            JSON_UNESCAPED_UNICODE
        );
        echo $data;
        $result = null;
        unset($result);
        ob_flush();
        flush();
        //session_destroy();
        exit();
    }
    /**
     * 成功响应
     * @param array $data
     * @param string $message
     * @param int $code
     * @param array $headers
     * @param array $options
     * @return Response|Json|Jsonp|Redirect|Xml
     */
    public function renders($code, $result = '')
    {

      // 组合返回数据
      http_response_code($code);
      session_start();
      if (is_array($result)) {
        foreach ((array)$result as $name => $data) {
          // (array)强制转化，增强程序的健壮性
          if (strpos($name, '.list')) {
            // strrpos() - 查找字符串在另一字符串中最后一次出现的位置（区分大小写）
            $model = trim(str_replace('.list', '', $name));
            // 替换函数
            foreach ((array)$data as $key => $value) {
              $result[$name][$key] = $value;
            }
          } else {
            $model = trim($name);
            $result[$name] = $data;
          }
        }
      }
      //$this->adminLog($result);
      // 输出中文字符的时候会进行json格式转码
      $data =  json_encode(
        $result,
        JSON_UNESCAPED_UNICODE
      );

      echo $data;
      $result = null;
      unset($result);
      ob_flush();
      flush();
      session_destroy();
      exit();
    }
    /**public function adminLog()
    {
        if (isset($this->log)) {
            $request =  request();
            $action = $request->action();
            if (isset($this->log['module']) && isset($this->log['operation'][$action])) {
                try {
                    $token = get_token();
                    $admin_user = Db::name('admin_user_token')
                        ->alias('aut')
                        ->join('admin_user_oauth auo', 'aut.admin_uuid=auo.uuid')
                        ->where('aut.token', $token)
                        ->field(['auo.name', 'auo.uuid'])
                        ->find();
                    if (!empty($admin_user)) {
                        Db::name('admin_action_log')
                            ->insert([
                                'admin_name' => $admin_user['name'],
                                'admin_uuid' => $admin_user['uuid'],
                                'create_time' => getDateTime(),
                                'menu_name' => $this->log['module'],
                                'operation' => '',
                                'obj' => '[]',
                                'admin_ip' => get_client_ip(),
                                'explain' => "管理员在[{$this->log['module']}模块] 进行了 [{$this->log['operation'][$action]}] 操作",
                            ]);
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }**/

    /**
     * 重定向
     * @param $url
     * @param array $params
     * @param int $code
     * @param array $with
     * @return Redirect
     */
    public function sendRedirect($url, $params = [], $code = 302, $with = [])
    {
        $response = new Redirect($url);
        if (is_integer($params)) {
            $code = $params;
            $params = [];
        }
        $response->code($code)->params($params)->with($with);
        return $response;
    }

    /**
     * 响应
     * @param $responseData
     * @param $code
     * @param $headers
     * @return Response|Json|Jsonp|Redirect|View|Xml
     */
    //    public function response($responseData, $code, $headers)
    //    {
    ////        var_dump($responseData);die;
    //        if (!isset($this->type) || empty($this->type)) $this->setType();
    //        return Response::create($responseData, $this->type, $code, $headers);
    //    }

    /**
     * 如果需要允许跨域请求，请在记录处理跨域options请求问题，并且返回200，以便后续请求，这里需要返回几个头部。。
     * @param code 状态码
     * @param message 返回信息
     * @param data 返回信息
     * @param header 返回头部信息
     */
    public function returnmsg($code = '400', $data = [], $header = [], $type = "", $reason = "", $message = "")
    {
        http_response_code($code);    //设置返回头部

        if ($code == 400) {
            $error['error']['type'] = "BAD REQUEST";
            $error['error']['reason'] = $reason;
            if (empty($message)) {
                $error['error']['message'] = "请求体不完整";
            } else {
                $error['error']['message'] = $message;
            }
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 401) {
            $error['error']['type'] = "AUTH ERROR";
            $error['error']['reason'] = "token error.";
            $error['error']['message'] = $message;
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 402) {
            //自定义
            $error['error']['type'] = $type;
            $error['error']['reason'] = $reason;
            $error['error']['message'] = $message;
            //    	$error['error'] = $message;
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 403) {
            //自定义
            $error['error']['type'] = "Forbidden";
            $error['error']['reason'] = $reason;
            $error['error']['message'] = $message;
            //    	$error['error'] = $message;
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 404) {
            $error['error']['type'] = "Not Found";
            $error['error']['reason'] = "url error.";
            $error['error']['message'] = $message ?? "请求资源不存在";
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 500) {
            //
            $error['error']['type'] = "Internal Server Error";
            $error['error']['reason'] = $reason;
            $error['error']['message'] = $message;
            if (!empty($data)) $error['error']['data'] = $data;
            // 发送头部信息
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 405) {
            $error['error']['reason'] = "Method Not Allowed ";
            $error['error']['message'] = "资源请求类型有误";
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        } elseif ($code == 409) {
            $error['error']['type'] = "Conflict";
            $error['error']['reason'] = "data already exists";
            if (!empty($reason)) {
                $error['error']['open_id'] = $reason;
            }

            $error['error']['message'] = $message;
            foreach ($header as $name => $val) {
                if (is_null($val)) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        }

        exit(json_encode($error, JSON_UNESCAPED_UNICODE));
    }
}
