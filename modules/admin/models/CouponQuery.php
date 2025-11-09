<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Coupon]].
 *
 * @see Coupon
 */
class CouponQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Coupon[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Coupon|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
