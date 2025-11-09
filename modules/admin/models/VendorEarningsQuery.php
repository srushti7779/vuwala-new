<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorEarnings]].
 *
 * @see VendorEarnings
 */
class VendorEarningsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorEarnings[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorEarnings|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
