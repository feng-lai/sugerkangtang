<?php

namespace app\api\logic\mini;

use app\api\model\ContestantPosterTemplate;
use think\Exception;
use think\Db;

/**
 * 海报模板-逻辑
 * User:
 * Date:
 * Time:
 */
class ContestantPosterTemplateLogic
{
  static public function cmsList($request)
  {
    $map['is_deleted'] = 1;
    $map['state'] = 1;
    $request['is_ps']?$map['is_ps'] = $request['is_ps']:'';
    $request['type']?$map['type'] = $request['type']:'';
    $request['link']?$map['link'] = $request['link']:'';
    $request['matchs_step_uuid']?$map['matchs_step_uuid'] = $request['matchs_step_uuid']:'';
    $result = ContestantPosterTemplate::build()->where($map)->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $result;
  }

  static public function cmsDetail($id)
  {
    return  Config::build()
      ->where('key', $id)
      ->field('*')
      ->find();
  }

   static public function cmsAdd($request){
     try {
       $data = [
         'uuid' => uuid(),
         'content'=>$request['content'],
         'create_time' => now_time(time()),
         'update_time' => now_time(time()),
       ];
       Config::build()->insert($data);
       return $data['uuid'];
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }

  static public function cmsEdit($request)
  {
    try {
      $user = Config::build()->where('key', $request['key'])->find();
      $user->save(['value'=>$request['value']]);
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function cmsDelete($id){
     try {
       $user = Config::build()->where('uuid', $id)->find();
       $user->save(['delete'=>1]);
       return true;
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }
}
