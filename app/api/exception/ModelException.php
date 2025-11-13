<?php

namespace app\api\exception;

class ModelException extends \think\exception\HttpException
{
    public function __construct( $message = null, \Exception $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}