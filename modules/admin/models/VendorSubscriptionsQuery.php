<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[VendorSubscriptions]].
 *
 * @see VendorSubscriptions
 */
class VendorSubscriptionsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return VendorSubscriptions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return VendorSubscriptions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
