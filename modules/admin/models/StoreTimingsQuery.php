<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[StoreTimings]].
 *
 * @see StoreTimings
 */
class StoreTimingsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return StoreTimings[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoreTimings|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
