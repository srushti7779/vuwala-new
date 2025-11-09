<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductOrders]].
 *
 * @see ProductOrders
 */
class ProductOrdersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductOrders[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductOrders|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
