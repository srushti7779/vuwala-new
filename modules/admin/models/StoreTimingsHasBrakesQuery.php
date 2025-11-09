<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[StoreTimingsHasBrakes]].
 *
 * @see StoreTimingsHasBrakes
 */
class StoreTimingsHasBrakesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return StoreTimingsHasBrakes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoreTimingsHasBrakes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
