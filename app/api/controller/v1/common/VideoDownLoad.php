<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\common\tools\AliOss;

/**
 * oss视频地址下载-控制器
 * User:
 * Date:
 * Time:
 */
class VideoDownLoad extends Api
{
  public $restMethodList = 'get';

  public function index()
  {
    $request = $this->selectParam([
      'url'
    ]);
    if(!$request['url']){
      $this->returnmsg(400, [], [], '', '','视频地址不能为空');
    }
    setDownloadHeader($request['url']);
    readfile($request['url']);
    exit();
    /**
      $oss = new AliOss();
      $oss->down($request['url']);
      $filePath = ROOT_PATH . 'public' .DS. explode('/',$request['url'])[4];

      setDownloadHeader($filePath);
      readfile($filePath);
      unlink($filePath);
      exit();// end process to prevent any problems.
     **/

  }

}
