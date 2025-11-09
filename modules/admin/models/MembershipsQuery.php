<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[MemberShips]].
 *
 * @see MemberShips
 */
class MembershipsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return MemberShips[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return MemberShips|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
