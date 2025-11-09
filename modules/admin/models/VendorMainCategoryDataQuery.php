<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorMainCategoryData]].
 *
 * @see VendorMainCategoryData
 */
class VendorMainCategoryDataQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorMainCategoryData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorMainCategoryData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
