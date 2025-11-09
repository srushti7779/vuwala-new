<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorDetails]].
 *
 * @see VendorDetails
 */
class VendorDetailsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
