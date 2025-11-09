<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductOrderItemsAssignedDiscounts]].
 *
 * @see ProductOrderItemsAssignedDiscounts
 */
class ProductOrderItemsAssignedDiscountsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductOrderItemsAssignedDiscounts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductOrderItemsAssignedDiscounts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
