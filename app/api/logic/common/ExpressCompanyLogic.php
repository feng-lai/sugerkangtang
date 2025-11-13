<?php

namespace app\api\logic\common;

use app\api\model\ExpressCompany;
use think\Exception;
use think\Db;

/**
 * 快递100-快递公司-逻辑
 * User: Yacon
 * Date: 2022-08-31
 * Time: 17:19
 */
class ExpressCompanyLogic
{
  static public function miniList($request, $userInfo)
  {
    $request['state'] ? $map['state'] = $request['state'] : '';
    $result = ExpressCompany::build()
      ->field('code,name')
      ->where($map)
      ->select();
    return $result;
  }

  // static public function miniDetail($id,$userInfo){
  //     $result=ExpressCompany::build()
  //         ->field('*')
  //         ->where('uuid',$id)
  //         ->find();
  //     return $result;
  // }

  // static public function miniAdd($request,$userInfo){
  //   try {
  //       Db::startTrans();
  //     $data = [
  //       'uuid' => uuid(),
  //       'create_time' => now_time(time()),
  //       'update_time' => now_time(time()),
  //     ];
  //     ExpressCompany::build()->insert($data);
  //     Db::commit();
  //     return $data['uuid'];
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniEdit($request,$userInfo){
  //   try {
  //     Db::startTrans();
  //     $expressCompany = ExpressCompany::build()->where('uuid',$request['uuid'])->find();
  //     $expressCompany['update_time'] = now_time(time());
  //     $expressCompany->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniDelete($id,$userInfo){
  //   try {
  //     Db::startTrans();
  //     ExpressCompany::build()->where('uuid',$id)->update(['is_deleted'=>2]);
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //       Db::rollback();
  //       throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
