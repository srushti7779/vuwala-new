<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ReelsViewCounts]].
 *
 * @see ReelsViewCounts
 */
class ReelsViewCountsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ReelsViewCounts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ReelsViewCounts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
