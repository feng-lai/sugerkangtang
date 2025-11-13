<?php

namespace app\api\model;

/**
 * 合伙人列表-模型
 * User:
 * Date:
 * Time:
 */
class Partner extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function getAll($uuid)
    {
        // 获取第一层直接下级
        $firstLevel = User::build()
            ->alias('u')
            ->join('partner p', 'p.user_uuid = u.uuid', 'left')
            ->where(function ($query) {
                $query->whereOr('p.type',null)->whereOr('p.type',2);
            })
            ->where('u.invite_partner_uuid', $uuid)
            ->where('u.is_deleted',1)
            ->where('u.uuid', '<>', $uuid)
            ->column('u.uuid');

        $result = $firstLevel;
        // 遍历第一层下级，获取他们的下级（即第二层及以下）
        foreach ($firstLevel as $childUuid) {
            $this->getChildrenRecursive($childUuid, $result);
        }
        return $result;
    }



    //获取间推数量
    public function getAllIndirectSubordinates($uuid)
    {
        // 获取第一层直接下级
        $firstLevel = User::build()
            ->alias('u')
            ->join('partner p', 'p.user_uuid = u.uuid', 'left')
            ->where(function ($query) {
                $query->whereOr('p.type',null)->whereOr('p.type',2);
            })
            ->where('u.invite_partner_uuid', $uuid)
            ->where('u.is_deleted',1)
            ->where('u.uuid', '<>', $uuid)
            ->column('u.uuid');

        $result = [];
        // 遍历第一层下级，获取他们的下级（即第二层及以下）
        foreach ($firstLevel as $childUuid) {
            $this->getChildrenRecursive($childUuid, $result);
        }
        return $result;
    }

    public function getChildrenRecursive($parentUuid, &$result)
    {
        $children = User::build()
            ->alias('u')
            ->join('partner p', 'p.user_uuid = u.uuid', 'left')
            ->where(function ($query) {
                $query->whereOr('p.type',null)->whereOr('p.type',2);
            })
            ->where('u.invite_partner_uuid', $parentUuid)
            ->where('u.is_deleted',1)
            ->where('u.uuid', '<>', $parentUuid)
            ->column('u.uuid');

        if (!empty($children)) {
            $result = array_merge($result, $children);
            foreach ($children as $child) {
                $this->getChildrenRecursive($child, $result);
            }
        }
    }

}
