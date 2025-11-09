<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[OrderTransactionDetails]].
 *
 * @see OrderTransactionDetails
 */
class OrderTransactionDetailsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return OrderTransactionDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderTransactionDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
