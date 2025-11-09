<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorSuppliers]].
 *
 * @see VendorSuppliers
 */
class VendorSuppliersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorSuppliers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorSuppliers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
