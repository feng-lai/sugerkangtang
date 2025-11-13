<?php


namespace app\exception;


class BaseException extends \Exception
{
  private $data;

  public function __construct($message = null, $code = 0, $data=null, \Exception $previous = null)
  {
    $this->data = $data;
    parent::__construct($message, $code, $previous);
  }



  public function getData()
  {
    return $this->data;
  }
}
