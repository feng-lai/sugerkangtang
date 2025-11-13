<?php
/**
 * Created by Terry.
 * User: Terry
 * Email: terr_exchange@outlook.com
 * Date: 2020/10/10
 * Time: 9:45
 */

namespace app\common\tools;

class SaveRemoteImage
{
    private $stream;

    private $dir;

    private $filename;

    private $url;

    public function __construct($path,$url)
    {
        $this -> dir = $path;
        $this -> url = $url;
        $this->filename = uuid().'.jpeg';
    }

    public function save()
    {
        ob_start();
        readfile($this -> url);
        $this -> stream = ob_get_contents();
        ob_end_clean();

        $file = fopen(ROOT_PATH.'public'.$this ->dir.DS.$this->filename,'w');
        fwrite($file,$this -> stream);
        fclose($file);

        $photo = "scrm_official/upload" . '/' .uuid().'.jpg';
        $oss          = new AliOss();
        $oss->uploadOss(self::getFilePath(),  $photo);
        unlink(self::getFilePath());

        return $photo;
    }
    public function saveUE()
    {
        ob_start();
        readfile($this -> url);
        $this -> stream = ob_get_contents();
        ob_end_clean();
        $file = fopen(ROOT_PATH.'public'.$this ->dir.DS.$this->filename,'w');

        fwrite($file,$this -> stream);
        fclose($file);

        $uuid = uuid();
        $photo = "scrm_official/upload" . '/' .$uuid.'.jpg';
        $oss          = new AliOss();
        $oss->uploadOss(self::getFilePath(),  $photo);

        $data['url'] = $photo;
        $data['size'] = filesize(self::getFilePath());
        $data['type'] = filetype(self::getFilePath());
        $data['title'] = $uuid.'.jpg';
        unlink(self::getFilePath());
        return $data;
    }
    public function saveNormal($uuid)
    {
        ob_start();
        readfile($this -> url);
        $this -> stream = ob_get_contents();
        ob_end_clean();

        $file = fopen(ROOT_PATH.'public'.$this ->dir.DS.$this->filename,'w');
        fwrite($file,$this -> stream);
        fclose($file);

        $photo = "mini_project/upload" . '/' .$uuid.'.jpg';
        $oss          = new AliOss();
        $oss->uploadOss(self::getFilePath(),  $photo);
        unlink(self::getFilePath());

        return $photo;
    }
    public function saveUEFile()
    {
        ob_start();
        readfile($this -> url);
        $this -> stream = ob_get_contents();
        ob_end_clean();
        $file = fopen(ROOT_PATH.'public'.$this ->dir.DS.$this->filename,'w');

        fwrite($file,$this -> stream);
        fclose($file);

        $uuid = uuid();
        $photo = "scrm_official/upload/sit/sitemap.xml" ;
        $oss          = new AliOss();
        $oss->uploadOss(self::getFilePath(),  $photo);

        $data['url'] = $photo;
        $data['size'] = filesize(self::getFilePath());
        $data['type'] = filetype(self::getFilePath());
        $data['title'] = $uuid.'.jpg';
        unlink(self::getFilePath());
        return $data;
    }

    public function getFilePath()
    {
        return ROOT_PATH.'public'.$this ->dir.DS.$this->filename;
    }

}
