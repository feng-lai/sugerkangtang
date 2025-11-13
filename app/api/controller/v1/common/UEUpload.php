<?php

/**
 * UE富文本图片上传 缩略图
 * Created by PhpStorm.
 * User: fenggaoyuan
 * Email: 253869997@qq.com
 * Date: 2018/8/25
 * Time: 17:08
 */

namespace app\api\controller\v1\common;

//use think\Image;

//require VENDOR_PATH.'topthink/think-image/src/Image.php';

use app\api\controller\Api;
use app\api\controller\Send;
use app\common\tools\AliOss;
// use app\common\tools\Ffmpeg;
use app\common\tools\SaveRemoteImage;
use think\Exception;
use think\Config;

class UEUpload extends Api
{
    use Send;

    public function save()
    {
        $request = $this->selectParam(['action', 'callback', 'source']);
        if ($request['action'] == "catchimage") {
            for ($i = 0; $i < count($request['source']); $i++) {
                $saveRemoteImage = new SaveRemoteImage(DS . 'upload', $request['source'][$i]);
                $img = $saveRemoteImage->saveUE();
                $data[$i]['url'] = "/" . $img['url'];
                $data[$i]['state'] = 'SUCCESS';
                $data[$i]['size'] =  $img['size'];
                //                $data[$i]['type'] =  $img['type'];
                $data[$i]['title'] =  $img['title'];
                $data[$i]['source'] =  $request['source'][$i];
                $data[$i]['original'] =  '';
            }
            $result['state'] = count($data) ? 'SUCCESS' : 'ERROR';
            $result['list'] = $data;
            $result = json_encode($result);
            echo $request['callback'] . "($result)";
            exit;
        }
        $file = request()->file('upfile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'upload');
            if ($info) {
                // 成功上传后 获取上传信息
                //将\ 转换成 /
                $filePath = str_replace('\\', '/', $info->getSaveName());
                $filename = $info->getFilename();
                $photo = "match_service/" . uuid();
                $gifName = $photo;
                $photo = $photo . strrchr($file->getInfo()['name'], '.');
                try {
                    $oss = new AliOss();
                    $oss->uploadOss(ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'upload/' . $filePath, $photo);

                    // if(strrchr($file->getInfo()['name'], '.') ==".mp4"){
                    //     $gif          = new Ffmpeg();
                    //     $gif->gifCreate(ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'upload/'.$filePath,$gifName,$photo);
                    // }
                    $data['state'] = 'SUCCESS';
                    $data['url'] = $photo;
                    $data['size'] = $info->getSize();
                    $data['type'] = $info->getType();
                    $data['title'] = $info->getFilename();
                    $this->render(200, $data);
                } catch (Exception $e) {
                    @unlink($filePath);
                }
            } else {
                // 上传失败获取错误信息
                $this->returnmsg(403, [], [], 'Forbidden', '', $file->getError());
            }
        } else {
            $this->returnmsg(402, [], [], 'Forbidden', '', '上传文件不存在');
        }
    }

    public function index()
    {
        $oss_config      =  Config::get('alioss');
        $url = $oss_config['url'];
        $json = '
	    {
    "imageActionName": "uploadimage",
    "imageFieldName": "upfile",
    "imageMaxSize": 2048000,
    "imageAllowFiles": [
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".bmp"
    ],
    "imageCompressEnable": true,
    "imageCompressBorder": 1600,
    "imageInsertAlign": "none",
    "imageUrlPrefix": "' . $url . '",
    "imagePathFormat": "\/storage\/image\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "scrawlActionName": "uploadscrawl",
    "scrawlFieldName": "upfile",
    "scrawlPathFormat": "\/storage\/image\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "scrawlMaxSize": 2048000,
    "scrawlUrlPrefix": "' . $url . '",
    "scrawlInsertAlign": "none",
    "snapscreenActionName": "uploadimage",
    "snapscreenPathFormat": "\/storage\/image\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "snapscreenUrlPrefix": "' . $url . '",
    "snapscreenInsertAlign": "none",
    "catcherLocalDomain": [
        "127.0.0.1",
        "localhost",
        "img.baidu.com"
    ],
    "catcherActionName": "catchimage",
    "catcherFieldName": "source",
    "catcherPathFormat": "\/storage\/image\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "catcherUrlPrefix": "' . $url . '",
    "catcherMaxSize": 2048000,
    "catcherAllowFiles": [
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".bmp"
    ],
    "videoActionName": "uploadvideo",
    "videoFieldName": "upfile",
    "videoPathFormat": "\/storage\/video\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "videoUrlPrefix": "' . $url . '",
    "videoMaxSize": 102400000,
    "videoAllowFiles": [
        ".flv",
        ".swf",
        ".mkv",
        ".avi",
        ".rm",
        ".rmvb",
        ".mpeg",
        ".mpg",
        ".ogg",
        ".ogv",
        ".mov",
        ".wmv",
        ".mp4",
        ".webm",
        ".mp3",
        ".wav",
        ".mid"
    ],
    "fileActionName": "uploadfile",
    "fileFieldName": "upfile",
    "filePathFormat": "\/storage\/file\/{yyyy}{mm}{dd}\/{time}{rand:6}",
    "fileUrlPrefix": "' . $url . '",
    "fileMaxSize": 51200000,
    "fileAllowFiles": [
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".bmp",
        ".flv",
        ".swf",
        ".mkv",
        ".avi",
        ".rm",
        ".rmvb",
        ".mpeg",
        ".mpg",
        ".ogg",
        ".ogv",
        ".mov",
        ".wmv",
        ".mp4",
        ".webm",
        ".mp3",
        ".wav",
        ".mid",
        ".rar",
        ".zip",
        ".tar",
        ".gz",
        ".7z",
        ".bz2",
        ".cab",
        ".iso",
        ".doc",
        ".docx",
        ".xls",
        ".xlsx",
        ".ppt",
        ".pptx",
        ".pdf",
        ".txt",
        ".md",
        ".xml"
    ],
    "imageManagerActionName": "listimage",
    "imageManagerListPath": "\/storage\/image\/",
    "imageManagerListSize": 20,
    "imageManagerUrlPrefix": "' . $url . '",
    "imageManagerInsertAlign": "none",
    "imageManagerAllowFiles": [
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".bmp"
    ],
    "fileManagerActionName": "listfile",
    "fileManagerListPath": "\/storage\/file\/",
    "fileManagerUrlPrefix": "' . $url . '",
    "fileManagerListSize": 20,
    "fileManagerAllowFiles": [
        ".png",
        ".jpg",
        ".jpeg",
        ".gif",
        ".bmp",
        ".flv",
        ".swf",
        ".mkv",
        ".avi",
        ".rm",
        ".rmvb",
        ".mpeg",
        ".mpg",
        ".ogg",
        ".ogv",
        ".mov",
        ".wmv",
        ".mp4",
        ".webm",
        ".mp3",
        ".wav",
        ".mid",
        ".rar",
        ".zip",
        ".tar",
        ".gz",
        ".7z",
        ".bz2",
        ".cab",
        ".iso",
        ".doc",
        ".docx",
        ".xls",
        ".xlsx",
        ".ppt",
        ".pptx",
        ".pdf",
        ".txt",
        ".md",
        ".xml"
    ]
}
	    ';
        $callback = request()->param('callback');
        echo $callback . "($json)";
        exit;
    }
}
