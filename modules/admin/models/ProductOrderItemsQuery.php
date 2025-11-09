<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductOrderItems]].
 *
 * @see ProductOrderItems
 */
class ProductOrderItemsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductOrderItems[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductOrderItems|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
