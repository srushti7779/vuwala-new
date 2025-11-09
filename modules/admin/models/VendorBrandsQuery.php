<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorBrands]].
 *
 * @see VendorBrands
 */
class VendorBrandsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorBrands[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorBrands|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
