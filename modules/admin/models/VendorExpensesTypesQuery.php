<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorExpensesTypes]].
 *
 * @see VendorExpensesTypes
 */
class VendorExpensesTypesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorExpensesTypes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorExpensesTypes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
