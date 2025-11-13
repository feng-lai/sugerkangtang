<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\common\tools\AliOss;

class UploadFile extends Api
{
    public function save()
    {
        $file = request()->file('file');
        $size = $file->getSize();
        $type = $file->getMime();

        empty($file) ? $file = request()->file('upload') : '';
        empty($file) ? $this->returnmsg(400, [], [], "", "param error", "请传入文件") : '';

        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        empty($info) ? $this->returnmsg(403, [], [], 'Forbidden', '', $file->getError()) : '';

        $filePath = str_replace('\\', '/', $info->getSaveName());
        $photo = 'match_service/' . uuid();
        $photo = $photo . strrchr($file->getInfo()['name'], '.');

        $filePath = ROOT_PATH . 'public' . DS . 'upload/' . $filePath;
        try {
            $oss = new AliOss();

            $oss->uploadOss($filePath, $photo);
            @unlink($filePath);
            $this->render(200, ['result' => ['url'=>$photo,'size'=>$size,'type'=>$type]]);
        } catch (\Exception $e) {
            @unlink($filePath);
            $this->returnmsg(403, [], [], 'Forbidden', '', $e->getMessage());
        }
    }
}
