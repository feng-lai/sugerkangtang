<?php

namespace app\api\logic\mini;

use app\api\model\Cooperator;
use app\api\model\Matchs;
use app\api\model\Region;
use think\Exception;
use think\Db;

/**
 * 商户合作-逻辑
 * User: Yacon
 * Date: 2023-03-20
 * Time: 09:35
 */
class CooperatorLogic
{
  static public function miniPage($request, $userInfo)
  {
    $map['is_deleted'] = 1;
    $request['type'] ? $map['type'] = $request['type'] : '';
    $request['matchs_uuid'] ? $map['matchs_uuid'] = $request['matchs_uuid'] : '';
    $result = Cooperator::build()
      ->field('*')
      ->where($map)
      ->order('create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $result;
  }

  static public function miniList($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    $result = Cooperator::build()
      ->field('*')
      ->alias('a')
      ->where($map)
      ->order('a.create_time desc')
      ->select();
    return $result;
  }

  static public function miniDetail($id, $userInfo)
  {
    $result = Cooperator::build()
      ->field('*')
      ->alias('a')
      ->where('a.uuid', $id)
      ->find();
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      if (!$request['type']) throw new Exception('请选择合作类型');
      if (!$request['name']) throw new Exception('请填写姓名');
      if (!$request['mobile']) throw new Exception('请填写手机号');

      // 1=大赛冠名 
      if ($request['type'] == 1) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        if (!$request['naming_level']) throw new Exception('请选择冠名层级');
        if (!$request['enterprise_name']) throw new Exception('请填写企业名称');
        if (!$request['brand']) throw new Exception('请填写品牌名称');
      }
      // 2=大赛赞助 
      else if ($request['type'] == 2) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        // if (!$request['sponsor_product']) throw new Exception('请填写赞助商品');
        if (!$request['brand_type']) throw new Exception('请选择品牌类型');
        if (!$request['product_name']) throw new Exception('请填写商品名称');
        if (!$request['sponsor_level']) throw new Exception('请选择赞助等级');
      }
      // 3=大赛区域报名点 
      else if ($request['type'] == 3) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        if (!$request['registration_point']) throw new Exception('请填写报名点名称');
      }
      // 4=企业品牌商品推广 
      else if ($request['type'] == 4) {
        if (!$request['enterprise_name']) throw new Exception('请填写企业名称');
        if (!$request['brand_type']) throw new Exception('请选择推广商品');
        if (!$request['product_name']) throw new Exception('请填写商品名称');
      }
      // 5=大赛义工 
      else if ($request['type'] == 5) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        if (!$request['gender']) throw new Exception('请选择性别');
        if (!$request['occupation']) throw new Exception('请选择职业');
        if (!$request['age']) throw new Exception('请选择年龄');
        if (!count($request['cover'])) throw new Exception('请上传个人照片');
      }
      // 6=比赛场地 
      else if ($request['type'] == 6) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        // if (!$request['user_address']) throw new Exception('请填写用户地址');
        if (!$request['field_area']) throw new Exception('请填写场地面积');
        if (!count($request['cover'])) throw new Exception('请上传场地照片');
      }
      // 7=区域比赛承办
      else if ($request['type'] == 7) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        if (!$request['enterprise_name']) throw new Exception('请填写企业名称');
        if (!$request['grade']) throw new Exception('请填选择承办层级');
      }
      // 8=个人/其他合作
      else if ($request['type'] == 8) {
        if (!$request['province_uuid']) throw new Exception('请选择省份');
        if (!$request['city_uuid']) throw new Exception('请选择市');
        if (!$request['area_uuid']) throw new Exception('请选择区');
        if (!$request['cooperation_content']) throw new Exception('请填写合作内容');
      }
      // 9=联名ip合作
      else if ($request['type'] == 9) {
        if (!$request['ip_name']) throw new Exception('请填写IP名称');
        if (!$request['product_type']) throw new Exception('请选择产品类型');
      
      } else {
        throw new Exception("合作类型错误");
      }


      $cooperator = Cooperator::build();
      $cooperator['uuid'] = uuid();
      $cooperator['create_time'] = now_time(time());
      $cooperator['update_time'] = now_time(time());
      $cooperator['name'] = $request['name'];
      $cooperator['mobile'] = $request['mobile'];
      $cooperator['type'] = $request['type'];
      if ($request['province_uuid']) {
        $cooperator['province_uuid'] = $request['province_uuid'];
        $cooperator['province'] = Region::build()->where(['area_id' => $request['province_uuid']])->value('area_name');
      }
      if ($request['city_uuid']) {
        $cooperator['city_uuid'] = $request['city_uuid'];
        $cooperator['city'] = Region::build()->where(['area_id' => $request['city_uuid']])->value('area_name');
      }
      if ($request['area_uuid']) {
        $cooperator['area_uuid'] = $request['area_uuid'];
        $cooperator['area'] = Region::build()->where(['area_id' => $request['area_uuid']])->value('area_name');
      }
      $cooperator['cover'] = json_encode($request['cover']);
      if ($request['naming_level']) $cooperator['naming_level'] = $request['naming_level'];
      if ($request['enterprise_name']) $cooperator['enterprise_name'] = $request['enterprise_name'];
      // if ($request['sponsor_product']) $cooperator['sponsor_product'] = $request['sponsor_product'];
      if ($request['brand_type']) $cooperator['brand_type'] = $request['brand_type'];
      if ($request['registration_point']) $cooperator['registration_point'] = $request['registration_point'];
      if ($request['product_name']) $cooperator['product_name'] = $request['product_name'];
      if ($request['gender']) $cooperator['gender'] = $request['gender'];
      if ($request['occupation']) $cooperator['occupation'] = $request['occupation'];
      if ($request['age']) $cooperator['age'] = $request['age'];
      if ($request['cooperation_content']) $cooperator['cooperation_content'] = $request['cooperation_content'];
      // if ($request['user_address']) $cooperator['user_address'] = $request['user_address'];
      if ($request['field_area']) $cooperator['field_area'] = $request['field_area'];
      if ($request['product_type']) $cooperator['product_type'] = $request['product_type'];
      if ($request['ip_name']) $cooperator['ip_name'] = $request['ip_name'];
      if ($request['grade']) $cooperator['grade'] = $request['grade'];
      if ($request['sponsor_level']) $cooperator['sponsor_level'] = $request['sponsor_level'];
      if ($request['brand']) $cooperator['brand'] = $request['brand'];
      $cooperator['state'] = 2;
      $cooperator['matchs_uuid'] = Matchs::build()->where(['is_deleted'=>1,'wx_state'=>1])->value('uuid');
      if(!$cooperator['matchs_uuid']){
        $cooperator['matchs_uuid'] = Matchs::build()->where(['is_deleted'=>1,'state'=>1])->value('uuid');
      }
      $cooperator->save();
      Db::commit();
      return $cooperator['uuid'];
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function miniEdit($request, $userInfo)
  {
    try {
      Db::startTrans();
      $cooperator = Cooperator::build()->where(['uuid' => $request['uuid']])->find();
      $cooperator['update_time'] = now_time(time());
      $cooperator['name'] = $request['name'];
      $cooperator['mobile'] = $request['mobile'];
      $cooperator['type'] = $request['type'];
      if ($request['province_uuid']) {
        $cooperator['province_uuid'] = $request['province_uuid'];
        $cooperator['province'] = Region::build()->where(['area_id' => $request['province_uuid']])->value('area_name');
      }
      if ($request['city_uuid']) {
        $cooperator['city_uuid'] = $request['city_uuid'];
        $cooperator['city'] = Region::build()->where(['area_id' => $request['city_uuid']])->value('area_name');
      }
      if ($request['area_uuid']) {
        $cooperator['area_uuid'] = $request['area_uuid'];
        $cooperator['area'] = Region::build()->where(['area_id' => $request['area_uuid']])->value('area_name');
      }
      $cooperator['cover'] = json_encode($request['cover']);
      if ($request['naming_level']) $cooperator['naming_level'] = $request['naming_level'];
      if ($request['enterprise_name']) $cooperator['enterprise_name'] = $request['enterprise_name'];
      // if ($request['sponsor_product']) $cooperator['sponsor_product'] = $request['sponsor_product'];
      if ($request['brand_type']) $cooperator['brand_type'] = $request['brand_type'];
      if ($request['registration_point']) $cooperator['registration_point'] = $request['registration_point'];
      if ($request['product_name']) $cooperator['product_name'] = $request['product_name'];
      if ($request['gender']) $cooperator['gender'] = $request['gender'];
      if ($request['occupation']) $cooperator['occupation'] = $request['occupation'];
      if ($request['age']) $cooperator['age'] = $request['age'];
      if ($request['cooperation_content']) $cooperator['cooperation_content'] = $request['cooperation_content'];
      // if ($request['user_address']) $cooperator['user_address'] = $request['user_address'];
      if ($request['field_area']) $cooperator['field_area'] = $request['field_area'];
      if ($request['product_type']) $cooperator['product_type'] = $request['product_type'];
      if ($request['ip_name']) $cooperator['ip_name'] = $request['ip_name'];
      if ($request['grade']) $cooperator['grade'] = $request['grade'];
      if ($request['sponsor_level']) $cooperator['sponsor_level'] = $request['sponsor_level'];
      if ($request['brand']) $cooperator['brand'] = $request['brand'];
      $cooperator->save();
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function miniDelete($id, $userInfo)
  {
    try {
      Db::startTrans();
      $cooperator = Cooperator::build()->where(['uuid' => $id])->find();
      $cooperator['update_time'] = now_time(time());
      $cooperator['is_deleted'] = 2;
      $cooperator->save();
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }
}
