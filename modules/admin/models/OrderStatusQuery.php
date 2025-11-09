<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[OrderStatus]].
 *
 * @see OrderStatus
 */
class OrderStatusQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return OrderStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
