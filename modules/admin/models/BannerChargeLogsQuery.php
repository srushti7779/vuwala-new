<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BannerChargeLogs]].
 *
 * @see BannerChargeLogs
 */
class BannerChargeLogsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return BannerChargeLogs[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BannerChargeLogs|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
