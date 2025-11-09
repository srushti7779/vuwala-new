<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BannerRecharges]].
 *
 * @see BannerRecharges
 */
class BannerRechargesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return BannerRecharges[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BannerRecharges|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
