<?php
namespace esign\comm;

/**
 * esign日志类
 * @author  澄泓
 * @date  2022/08/18 15:10
 */
class EsignLogHelper
{
    static function writeLog($text) {
        if(is_array($text) || is_object($text)){
            $text = json_encode($text);
        }
        $logDir = ESIGN_ROOT .'/log/' ;

        file_put_contents ( $logDir.date("Y-m-d").".log", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
    }
    
    static function  printMsg($msg)
    {
        //echo "<pre/>";
        //if (is_array($msg) || is_object($msg)) {
            //var_dump($msg);
        //} else {
            //echo $msg . "\n";
        //}
    }

}
