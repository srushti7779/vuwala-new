<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ReelShareCounts]].
 *
 * @see ReelShareCounts
 */
class ReelShareCountsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ReelShareCounts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ReelShareCounts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
