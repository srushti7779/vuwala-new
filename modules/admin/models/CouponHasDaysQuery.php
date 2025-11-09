<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[CouponHasDays]].
 *
 * @see CouponHasDays
 */
class CouponHasDaysQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return CouponHasDays[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CouponHasDays|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
