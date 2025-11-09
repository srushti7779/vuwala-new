<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorHasMenuPermissions]].
 *
 * @see VendorHasMenuPermissions
 */
class VendorHasMenuPermissionsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorHasMenuPermissions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorHasMenuPermissions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
