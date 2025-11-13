<?php

namespace app\api\model;

/**
 * 用户列表-模型
 * User: Yacon
 * Date: 2022-07-20
 * Time: 19:38
 */
class User extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    private $primaryKey = 'uuid';

    public function getExtendAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 生成ID号
     */
    public function createUserID()
    {
        $number = $this->max('serial_number');
        $number++;
        $count = strlen($number);
        $pre = 'AM';
        for ($i = 0; $i < 7 - $count; $i++) {
            $pre .= '0';
        }
        $result = $pre . $number;
        return [$number, $result];
    }

    public function getLast($invite_partner_uuid){
        $this->getChildrenRecursive2($invite_partner_uuid, $result);
        return $result;
    }

    public function getChildrenRecursive2($invite_partner_uuid, &$result)
    {
        $children = User::build()
            ->field(['uuid','invite_partner_uuid'])
            ->where('uuid', $invite_partner_uuid)
            ->where('is_deleted',1)
            ->find();
        if (!empty($children)) {
            $children['type'] = Partner::build()->where('user_uuid', $children['uuid'])->where('is_deleted',1)->value('type');
            $result[] = $children;
            if(!empty($children->invite_partner_uuid)){
                $this->getChildrenRecursive($children->invite_partner_uuid, $result);
            }

        }
    }

    public function getAll($uuid)
    {
        // 获取第一层直接下级
        $firstLevel = User::build()
            ->where('invite_uuid', $uuid)
            ->where('uuid', '<>', $uuid)
            ->where('is_deleted',1)
            ->column('uuid');

        $result = $firstLevel;
        // 遍历第一层下级，获取他们的下级（即第二层及以下）
        foreach ($firstLevel as $childUuid) {
            $this->getChildrenRecursive($childUuid, $result);
        }
        return $result;
    }

    public function getAllIndirectSubordinates($uuid,$pid='invite_uuid')
    {
        // 获取第一层直接下级
        $firstLevel = User::build()
            ->where($pid, $uuid)
            ->where('uuid', '<>', $uuid)
            ->where('is_deleted',1)
            ->column('uuid');

        $result = [];
        // 遍历第一层下级，获取他们的下级（即第二层及以下）
        foreach ($firstLevel as $childUuid) {
            $this->getChildrenRecursive($childUuid, $result,$pid);
        }
        return $result;
    }

    public function getChildrenRecursive($parentUuid, &$result,$pid)
    {
        $children = User::build()
            ->where($pid, $parentUuid)
            ->where('uuid', '<>', $parentUuid)
            ->where('is_deleted',1)
            ->column('uuid');

        if (!empty($children)) {
            $result = array_merge($result, $children);
            foreach ($children as $child) {
                $this->getChildrenRecursive($child, $result,$pid);
            }
        }
    }


    // 获取指定顶级节点及其所有子节点 推广员
    public function getFullTree($topUuid)
    {
        $list = $this->field('uuid,name,invite_uuid,phone,create_time,img,invite_partner_uuid')->where('is_deleted',1)->select();
        return $this->buildSubTree($list, $topUuid,$topUuid);
    }

    // 递归构建子树
    protected function buildSubTree($data, $puuid,$topUuid)
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['invite_uuid'] == $puuid) {

                $retail =  Retail::build()->where('user_uuid', $item->uuid)->find();
                if($retail){
                    $item['type'] = $retail['type'];
                    $item['name'] = $retail['name'];
                }else{
                    $item['type'] = 3;
                }
                $item['chilid'] = $this->where('invite_uuid',$item['uuid'])->count();
                $item['order'] = CommissionOrder::build()->where('user_uuid',$item['uuid'])->count();
                $item['commission'] = CommissionOrder::build()->where('user_uuid',$item['uuid'])->where('status','in',[1,2])->sum('commission');

                $children = $this->buildSubTree($data, $item['uuid'],$topUuid);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }



    // 获取指定顶级节点及其所有子节点 合伙人
    public function getFullTree2($topUuid)
    {
        $list = $this->field('uuid,name,phone,create_time,img,invite_partner_uuid')->where('is_deleted',1)->select();
        return $this->buildSubTree2($list, $topUuid,$topUuid);
    }

    // 递归构建子树
    protected function buildSubTree2($data, $puuid,$topUuid)
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['invite_partner_uuid'] == $puuid) {
                $partner =  Partner::build()->where('user_uuid', $item->uuid)->find();
                if($partner){
                    $item['type'] = $partner['type'];
                    $item['name'] = $partner['name'];
                }else{
                    $item['type'] = 3;
                }
                $item['chilid'] = $this->where('invite_partner_uuid',$item['uuid'])->count();
                $item['order'] =PartnerOrder::build()->where('user_uuid',$item['uuid'])->count();
                $item['commission'] = PartnerOrder::build()->where('user_uuid',$item['uuid'])->sum('commission');
                if($item['type'] != 2){
                    $children = $this->buildSubTree2($data, $item['uuid'],$topUuid);
                    if ($children) {
                        $item['children'] = $children;
                    }
                    $tree[] = $item;
                }
            }
        }
        return $tree;
    }




}
