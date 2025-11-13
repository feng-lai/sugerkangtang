<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/11/17
 * Time: 17:21
 *
 */
namespace app\common\tools;


use OSS\OssClient;
use OSS\Core\OssException;
use think\Config;
use think\Exception;

class AliOss
{
    public function uploadOss($file, $name)
    {
        // 配置文件

        $oss_config      =  Config::get('alioss');
        $accessKeyId     = $oss_config['appid'];
        $accessKeySecret = $oss_config['appkey'];
        $endpoint        = $oss_config['endpoint'];
        $bucket          = $oss_config['bucket'];

        // 文件路径生成
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $result    = $ossClient->uploadFile($bucket, $name, $file);
            if (isset($result['info']['http_code']) AND $result['info']['http_code'] == 200) {
                $img_url = $result['info']['url'] ?? '';
                return $img_url;
            } else {
                throw new Exception(lang(40074), 40074);
            }
        } catch (OssException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function down($url){
      $oss_config      =  Config::get('alioss');
      $accessKeyId     = $oss_config['appid'];
      $accessKeySecret = $oss_config['appkey'];
      $endpoint        = $oss_config['endpoint'];
      $bucket          = $oss_config['bucket'];

      // 填写不包含Bucket名称在内的Object完整路径，例如testfolder/exampleobject.txt。
      $object = str_replace($oss_config['url'],'',$url);
      // 下载Object到本地文件examplefile.txt，并保存到指定的本地路径中（D:\\localpath）。如果指定的本地文件存在会覆盖，不存在则新建。
      // 如果未指定本地路径，则下载后的文件默认保存到示例程序所属项目对应本地路径中。
      $localfile = explode('/',$url)[4];
      $options = array(
        OssClient::OSS_FILE_DOWNLOAD => $localfile
      );

      // 文件路径生成
      try {
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $ossClient->getObject($bucket, $object, $options);
      } catch (OssException $e) {
        throw new Exception($e->getMessage(), $e->getCode());
      }
    }
}