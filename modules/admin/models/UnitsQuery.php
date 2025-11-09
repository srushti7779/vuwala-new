<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Units]].
 *
 * @see Units
 */
class UnitsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Units[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Units|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
