<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorExpenses]].
 *
 * @see VendorExpenses
 */
class VendorExpensesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorExpenses[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorExpenses|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
