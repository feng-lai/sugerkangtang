<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/11/17
 * Time: 17:21
 *
 */
namespace app\common\tools;



use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use think\Config;
use think\Exception;

class Ffmpeg
{
    /**
     * @param $file
     * @param $name
     * @param $osskey
     * @throws Exception 视频gif图
     */
    public function gifCreate($file, $name,$osskey)
    {


//        $config = [
//            'ffmpeg.binaries'  => '/usr/local/Cellar/ffmpeg',
//            'ffprobe.binaries' =>  '/usr/local/Cellar/ffmpeg'
//        ];
        // 配置文件
        $ffmpeg = \FFMpeg\FFMpeg::create();
//        var_dump(1);die;
        $video  =  $ffmpeg ->open($file);
        $config = config('alioss');
        $ossUrl = $config['url'];
        $url = $ossUrl . $osskey . "?x-oss-process=image/info";
        $info = curl_send($url);
        $info = json_decode($info, true);
        $width = intval($info['ImageHeight']['value'] ?? 400);
        $height = intval($info['ImageWidth']['value'] ?? 300);

        $video ->gif(TimeCode::fromSeconds(3), new Dimension($width,$height), 2)->save( $file.'.gif' );
        //上传到oss
        $oss          = new AliOss();
        $oss->uploadOss($file.'.gif',  $name.".gif");
    }


    public function video_time($file) {
        ob_start();
        passthru(sprintf(FFMPEG_PATH, $file));  //passthru()类似exec()
        $info = ob_get_contents();
        ob_end_clean();
        // 通过使用输出缓冲，获取到ffmpeg所有输出的内容。
        $ret = array();
        // Duration: 01:24:12.73, start: 0.000000, bitrate: 456 kb/s
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
            $ret['duration'] = $match[1]; // 提取出播放时间
            return $ret;
        }
        return "";
    }
}