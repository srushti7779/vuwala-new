<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorSettlements]].
 *
 * @see VendorSettlements
 */
class VendorSettlementsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorSettlements[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorSettlements|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
