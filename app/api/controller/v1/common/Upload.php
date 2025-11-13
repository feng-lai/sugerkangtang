<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\common\tools\AliOss;

class Upload extends Api
{
    public function save()
    {
        $file = request()->file('file');
        $size = $file->getSize();
        $type = $file->getMime();
        $name = $file->getInfo()['name'];

        empty($file) ? $file = request()->file('upload') : '';
        empty($file) ? $this->returnmsg(400, [], [], "", "param error", "请传入文件") : '';

        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        empty($info) ? $this->returnmsg(403, [], [], 'Forbidden', '', $file->getError()) : '';

        $filePath = str_replace('\\', '/', 'upload'.DS.$info->getSaveName());
        $photo = 'tangtangtang/' . uuid();
        $photo = $photo . strrchr($file->getInfo()['name'], '.');

        try {
            $oss = new AliOss();
            $oss->uploadOss($filePath, $photo);
            sleep(1); // 延迟1秒
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $this->render(200, ['result' => $photo,'size' => $size, 'type' => $type,'file_name' => $name]);
        } catch (\Exception $e) {
            unlink($filePath);
            $this->returnmsg(403, [], [], 'Forbidden', '', $e->getMessage());
        }
    }
}
