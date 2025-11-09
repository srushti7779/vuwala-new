<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[StoreServiceTypes]].
 *
 * @see StoreServiceTypes
 */
class StoreServiceTypesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return StoreServiceTypes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoreServiceTypes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
