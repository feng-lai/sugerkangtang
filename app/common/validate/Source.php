<?php

namespace app\common\validate;

use think\Validate;

/**
 * 素材-校验
 * User:
 * Date:
 * Time: 13:25
 */
class Source extends Validate
{
  protected $rule = [
    'name' => 'require',
    'file'=>'require',
    'source_cate_uuid'=>'require',
    'type'=>'require|checkType',
    'source_tag_uuid'=>'require',
  ];

  protected $field = [
    'name' => '名称',
    'file'=>'素材',
    'source_cate_uuid'=>'一级分类',
    'type'=>'类型',//1=图片 2=视频
    'source_tag_uuid'=>'标签',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name','file','source_cate_uuid','type','source_tag_uuid'],
    'edit' => [],
  ];

  protected function checkType($value,$rule,$data)
  {
    $res = $data['file'];
    if($value == 1){
      $is = true;
      $ext = explode('.',$res['url']);
      if(!in_array($ext[1],['jpg','jpeg','png','JPG','PNG','JPEG'])){
        $is = false;
      }
      if(!$is){
        return '文件格式有误';
      }
      return $is;
    }
    if($value == 2){
      $is = true;
      $ext = explode('.',$res['url']);
      if(!in_array($ext[1],['mp4','MP4'])){
        $is = false;
      }

      if(!$is){
        return '文件格式有误';
      }
      return $is;
    }
  }
}
