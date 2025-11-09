<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[OrderDetails]].
 *
 * @see OrderDetails
 */
class OrderDetailsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return OrderDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
