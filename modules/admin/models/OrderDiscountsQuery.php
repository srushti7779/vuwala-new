<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[OrderDiscounts]].
 *
 * @see OrderDiscounts
 */
class OrderDiscountsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return OrderDiscounts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderDiscounts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
