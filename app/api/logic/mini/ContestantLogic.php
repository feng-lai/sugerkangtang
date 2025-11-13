<?php

namespace app\api\logic\mini;

use app\api\model\Contestant;
use app\api\model\ContestantImg;
use app\api\model\ContestantPoster;
use app\api\model\ContestantGift;
use app\api\model\Matchs;
use app\api\model\Attention;
use app\api\model\MatchsPoint;
use app\api\model\Agree;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 选手报名-逻辑
 * User: Yacon
 * Date: 2023-03-20
 * Time: 11:56
 */
class ContestantLogic
{
  //推荐
  static public function Recommend($request,$userInfo){
    $result = Contestant::build()
      ->field('u.uuid as user_uuid,u.nickname,c.city,c.id_photo,c.uuid,c.half_photo,c.name')
      ->alias('c')
      ->join('user u','u.uuid = c.user_uuid')
      ->where('c.level','<>',999999999)
      ->order('c.level asc')
      ->order('c.create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    foreach ($result as $v){
      $v->is_atten = Attention::build()->where(['user_uuid'=>$userInfo['uuid'],'contestant_uuid'=>$v->uuid,'type'=>3])->count();
      $v->agree = Agree::build()->where('contestant_uuid',$v->uuid)->count();
      $v->gift = ContestantGift::build()->where('contestant_uuid',$v->uuid)->sum('coins');
    }
    return $result;
  }
  static public function miniPage($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    // 如果存在openid则说明是小程序用户，则直接查询出小程序用户的报名列表，否则查询出全部
    array_key_exists('openid', $userInfo) ? $map['a.user_uuid'] = $userInfo['uuid'] : '';

    array_key_exists('state', $request) && $request['state'] ? $map['a.state'] = $request['state'] : '';
    //array_key_exists('content_state', $request) && $request['content_state'] ? $map['a.content_state'] = $request['content_state'] : '';
    array_key_exists('mobile', $request) && $request['mobile'] ? $map['a.mobile'] = ['like', "%{$request['mobile']}%"] : '';
    array_key_exists('province_uuid', $request) && $request['province_uuid'] ? $map['a.province_uuid'] = $request['province_uuid'] : '';
    array_key_exists('city_uuid', $request) && $request['city_uuid'] ? $map['a.city_uuid'] = $request['city_uuid'] : '';
    array_key_exists('area_uuid', $request) && $request['area_uuid'] ? $map['a.area_uuid'] = $request['area_uuid'] : '';
    array_key_exists('keyword_search', $request) && $request['keyword_search'] ? $map['a.name|u.nickname|u.user_id|a.mobile'] = ['like','%'.$request['keyword_search'].'%'] : '';
    if($request['content_state']){
      if($request['content_state'] == 1){
        $map['a.content_state'] = $request['content_state'];
      }
      if($request['content_state'] == 2){
        $map['a.content_state'] = ['in',[2,3]];
      }
    }

    $result = Contestant::build()
      ->field('a.*,u.nickname,u.user_id')
      ->alias('a')
      ->join('user u','u.uuid = a.user_uuid')
      ->where($map);
    if(array_key_exists('content_state', $request) && $request['content_state'] == 1){
      $result = $result->order('a.update_time desc');
    }else{
      if(array_key_exists('state', $request) && $request['state'] == 4){
        $result = $result->order('a.level asc');
      }
      $result = $result->order('a.create_time desc');
    }

    $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    $result = objToArray($result);
    $result['data'] = array_map(function ($item) {
      $item['matchs_name'] = Matchs::build()->where(['uuid' => $item['matchs_uuid']])->value("name");
      //$item['user_name'] = User::build()->where(['uuid' => $item['user_uuid']])->value("nickname");
      //$item['user_id'] = User::build()->where(['uuid' => $item['user_uuid']])->value("user_id");
      return $item;
    }, $result['data']);
    return $result;
  }

  static public function miniList($request, $userInfo)
  {
    $map['a.is_deleted'] = 1;
    $result = Contestant::build()
      ->field('*')
      ->alias('a')
      ->where($map)
      ->order('a.create_time desc')
      ->select();
    return $result;
  }

  static public function miniDetail($id, $userInfo)
  {
    $result = Contestant::build()
      ->field('*')
      ->alias('a')
      ->where('a.uuid', $id)
      ->find();
    if ($result) {
      $result = objToArray($result);
      $result['user_name'] = User::build()->where(['uuid' => $result['user_uuid']])->value("nickname");
      $result['point_name'] = MatchsPoint::build()->where(['uuid' => $result['point_uuid']])->value("name");
      $result['matchs_name'] = Matchs::build()->where(['uuid' => $result['matchs_uuid']])->value("name");
      $result['fans'] = Attention::build()->where(['contestant_uuid' => $result['uuid']])->where('type',3)->count();
      $result['agree'] = Agree::build()->where(['contestant_uuid' => $result['uuid']])->count();
      //是否关注
      $result['is_atten'] = Attention::build()->where(['contestant_uuid' => $result['uuid'],'user_uuid'=>$userInfo['uuid'],'type'=>3])->count();
      //是否点赞
      $result['is_agree'] = Agree::build()->where(['contestant_uuid' => $result['uuid'],'user_uuid'=>$userInfo['uuid']])->count();
      //是否有投票
      $result['is_gift'] = ContestantGift::build()->where(['contestant_uuid' => $result['uuid'],'user_uuid'=>$userInfo['uuid']])->count()?1:0;
      $result['bg_video']?$result['bg_video'] = $result['bg_video']:$result['bg_video'] = '/match_service/12844749982bcc65398156b31d9846e5.mp4';
    }
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      if (!$request['name']) throw new Exception("请填写姓名");
      if (!$request['origin']) throw new Exception("请填写信息来源");
      if (!$request['mobile']) throw new Exception("请填写手机号");
      if (!$request['point_uuid']) throw new Exception("请选择报名点");
      if (!$request['height']) throw new Exception("请选择身高");
      if (!$request['weight']) throw new Exception("请选择体重");
      if (!$request['education']) throw new Exception("请选择学历");
      if (!$request['bust']) throw new Exception("请选择胸围");
      if (!$request['waist']) throw new Exception("请选择腰围");
      if (!$request['hip']) throw new Exception("请选择臀围");
      if (!$request['id_photo']) throw new Exception("请上传证件照");
      if (!$request['half_photo']) throw new Exception("请上传半身照");
      if (!$request['full_photo']) throw new Exception("请上传全身照");
      if (!$request['age']) throw new Exception("请选择年龄");
      if (!$request['nation']) throw new Exception("请选择民族");
      if ($request['height'] < 140 || $request['height'] > 220) throw new Exception('身高只能在140cm~220cm之间');
      if ($request['weight'] < 35 || $request['weight'] > 100) throw new Exception('体重只能在35kg~100kg之间');
      if ($request['bust'] < 60 || $request['bust'] > 100) throw new Exception('胸围只能在60cm~100cm之间');
      if ($request['waist'] < 50 || $request['waist'] > 80) throw new Exception('腰围只能在50cm~80cm之间');
      if ($request['hip'] < 60 || $request['hip'] > 120) throw new Exception('臀围只能在60cm~120cm之间');

      $matchsPoint = MatchsPoint::build()->where(['uuid' => $request['point_uuid']])->find();

      // 查询赛事
      $state = Matchs::build()->where(['uuid' => $matchsPoint['matchs_uuid']])->value('state');
      if ($state != 1) throw new Exception("该赛事无法报名");

      //是否已报名
      if(Contestant::build()->where('user_uuid',$userInfo['uuid'])->count()){
        throw new Exception("已报名");
      }

      // 更新姓名
      $user = User::build()->where(['uuid' => $userInfo['uuid']])->find();
      $user->name = $request['name'];
      $user->save();

      $contestant = Contestant::build();
      $contestant['uuid'] = uuid();
      $contestant['create_time'] = now_time(time());
      $contestant['update_time'] = now_time(time());
      $contestant['name'] = $request['name'];
      $contestant['mobile'] = $request['mobile'];
      $contestant['point_uuid'] = $request['point_uuid'];
      $contestant['height'] = $request['height'];
      $contestant['weight'] = $request['weight'];
      $contestant['education'] = $request['education'];
      $contestant['bust'] = $request['bust'];
      $contestant['waist'] = $request['waist'];
      $contestant['hip'] = $request['hip'];
      $contestant['id_photo'] = $request['id_photo'];
      $contestant['half_photo'] = $request['half_photo'];
      $contestant['full_photo'] = $request['full_photo'];
      $contestant['age'] = $request['age'];
      $contestant['nation'] = $request['nation'];
      $contestant['social'] = $request['social'];
      $contestant['province_uuid'] = $matchsPoint->province_uuid;
      $contestant['province'] = $matchsPoint->province;
      $contestant['city_uuid'] = $matchsPoint->city_uuid;
      $contestant['city'] = $matchsPoint->city;
      $contestant['area_uuid'] = $matchsPoint->area_uuid;
      $contestant['area'] = $matchsPoint->area;
      $contestant['matchs_uuid'] = $matchsPoint->matchs_uuid;
      $contestant['user_uuid'] = $userInfo['uuid'];
      $contestant['origin'] = $request['origin'];
      $contestant->save();

      //选手素材图片
      $img = ContestantImg::build();
      $img_data = [
        [
          'uuid'=>uuid(),
          'img'=>$request['full_photo'],
          'contestant_uuid'=>$contestant->uuid,
          'create_time'=>now_time(time()),
          'update_time'=>now_time(time()),
          'is_extra'=>2
        ],
        [
          'uuid'=>uuid(),
          'img'=>$request['half_photo'],
          'contestant_uuid'=>$contestant->uuid,
          'create_time'=>now_time(time()),
          'update_time'=>now_time(time()),
          'is_extra'=>1
        ],
        [
          'uuid'=>uuid(),
          'img'=>$request['full_photo'],
          'contestant_uuid'=>$contestant->uuid,
          'type'=>2,
          'create_time'=>now_time(time()),
          'update_time'=>now_time(time()),
          'is_extra'=>2
        ],
        [
        'uuid'=>uuid(),
          'img'=>$request['half_photo'],
          'contestant_uuid'=>$contestant->uuid,
          'type'=>2,
          'create_time'=>now_time(time()),
          'update_time'=>now_time(time()),
          'is_extra'=>1
        ]
      ];
      $img->saveAll($img_data,false);
      Db::commit();
      return $contestant['uuid'];
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  static public function miniEdit($request, $userInfo)
  {
    try {
      Db::startTrans();
      $contestant = Contestant::build()->where(['uuid' => $request['uuid']])->find();
      if(!$contestant){
        throw new Exception('数据不存在', 500);
      }
      $contestant['update_time'] = now_time(time());
      // 更新姓名
      if ($request['name']) {
        $contestant['name'] = $request['name'];

        $user = User::build()->where(['uuid' => $userInfo['uuid']])->find();
        $user->name = $request['name'];
        $user->save();
      }

      if ($request['mobile']) $contestant['mobile'] = $request['mobile'];
      if ($request['point_uuid']) $contestant['point_uuid'] = $request['point_uuid'];
      if ($request['height']) $contestant['height'] = $request['height'];
      if ($request['weight']) $contestant['weight'] = $request['weight'];
      if ($request['education']) $contestant['education'] = $request['education'];
      if ($request['bust']) $contestant['bust'] = $request['bust'];
      if ($request['waist']) $contestant['waist'] = $request['waist'];
      if ($request['hip']) $contestant['hip'] = $request['hip'];
      if ($request['age']) $contestant['age'] = $request['age'];
      if ($request['nation']) $contestant['nation'] = $request['nation'];
      if ($request['social']) $contestant['social'] = $request['social'];
      if ($request['origin']) $contestant['origin'] = $request['origin'];

      if ($request['id_photo'] && $contestant['id_photo'] != $request['id_photo']) {
        $contestant['id_photo'] = $request['id_photo'];
        $contestant['id_photo_state'] = 1;
        $contestant['state'] = 1;
      }
      if ($request['half_photo'] && $contestant['half_photo'] != $request['half_photo']) {
        $contestant['half_photo'] = $request['half_photo'];
        $contestant['half_photo_state'] = 1;
        $contestant['state'] = 1;
      }
      if ($request['full_photo'] && $contestant['full_photo'] != $request['full_photo']) {
        $contestant['full_photo'] = $request['full_photo'];
        $contestant['full_photo_state'] = 1;
        $contestant['state'] = 1;
      }
      //if ($request['half_poster'] && $contestant['half_poster'] != $request['half_poster']) {
      if ($request['half_poster']) {
        $contestant['half_poster'] = $request['half_poster'];
        /**$half_poster = json_decode(remove_quote($request['half_poster']));
        //同步海报数据
        foreach($half_poster as $v){
          if(!ContestantPoster::build()->where('contestant_uuid',$request['uuid'])->where('img',$v)->count()){
            $is = ContestantPoster::build()->insert([
              'uuid'=>uuid(),
              'is_mini'=>1,
              'is_extra'=>1,
              'img'=>$v,
              'contestant_uuid'=>$request['uuid'],
              'create_time'=>date('Y-m-d H:i:s'),
              'update_time'=>date('Y-m-d H:i:s'),
            ]);
          }
        }**/
      }
      //if ($request['full_poster'] && $contestant['full_poster'] != $request['full_poster']) {
      if ($request['full_poster']) {
        $contestant['full_poster'] = $request['full_poster'];
        /**$full_poster = json_decode(remove_quote($request['full_poster']));
        //同步海报数据
        foreach($full_poster as $v){
          if(!ContestantPoster::build()->where('contestant_uuid',$request['uuid'])->where('img',$v)->count()){
            ContestantPoster::build()->insert([
              'uuid'=>uuid(),
              'is_mini'=>1,
              'is_extra'=>1,
              'img'=>$v,
              'contestant_uuid'=>$request['uuid'],
              'create_time'=>date('Y-m-d H:i:s'),
              'update_time'=>date('Y-m-d H:i:s'),
            ]);
          }
        }**/
      }
      if($request['bg_img']) {
        $contestant['bg_img'] = $request['bg_img'];
        $contestant['content_state'] = 1;
        $contestant['update_time'] = now_time(time());
      }
      if($request['bg_video']) {
        $contestant['bg_video'] = $request['bg_video'];
        $contestant['content_state'] = 1;
        $contestant['update_time'] = now_time(time());
      }
      if($request['dsc']) $contestant['dsc'] = $request['dsc'];
      if($request['slogan']) $contestant['slogan'] = $request['slogan'];
      $contestant->save();
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
      $contestant = Contestant::build()->where(['uuid' => $id])->find();
      $contestant['update_time'] = now_time(time());
      $contestant['is_deleted'] = 2;
      $contestant->save();
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }
}
