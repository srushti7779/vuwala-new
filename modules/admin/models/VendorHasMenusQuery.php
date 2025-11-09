<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorHasMenus]].
 *
 * @see VendorHasMenus
 */
class VendorHasMenusQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return VendorHasMenus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorHasMenus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
