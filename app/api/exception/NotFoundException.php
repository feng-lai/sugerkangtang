<?php

namespace app\api\exception;

class NotFoundException extends \think\exception\HttpException
{
    public function __construct( $message = null, \Exception $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(404, $message, $previous, $headers, $code);
    }
}