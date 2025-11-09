<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductOrdersHasDiscounts]].
 *
 * @see ProductOrdersHasDiscounts
 */
class ProductOrdersHasDiscountsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductOrdersHasDiscounts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductOrdersHasDiscounts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
