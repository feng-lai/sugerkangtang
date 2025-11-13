<?php
/**
 * Created by Terry.
 * User: Terry
 * Email: terr_exchange@outlook.com
 * Date: 2020/9/16
 * Time: 10:12
 */
namespace app\api\exception;

use Exception;
use think\Config;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;

class Http extends Handle
{
    public function render(Exception $e)
    {
        if ($e instanceof HttpException) {

            $error = [
                'type' => 'Forbidden',
                'reason' => '',
                'message' => $e->getMessage(),
                'data' => new \stdClass()
            ];
            //if(Config::get('app_debug')) $error['trace'] = $e -> getTrace();

            return response([
                'error' =>$error
            ], $e->getStatusCode() ?? 400,[],'json');
        }

        if ($e instanceof ValidateException) {

            $error = [
                'type' => 'Forbidden',
                'reason' => '',
                'message' => $e->getMessage(),
                'data' => new \stdClass()
            ];

            return response([
                'error' =>$error
            ], 403,[],'json');
        }

        // 全局异常通过json返回
        if ($e instanceof Exception) {

            $error = [
                'type' => 'Bad Request',
                'reason' => '',
                'message' => $e->getMessage(),
                'data' => new \stdClass()
            ];
            //if(Config::get('app_debug')) $error['trace'] = $e -> getTrace();

            return response([
                'error' =>$error
            ], 400,[],'json');
        }

        //可以在此交由系统处理
        return parent::render($e);
    }
}