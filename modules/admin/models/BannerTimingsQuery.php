<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BannerTimings]].
 *
 * @see BannerTimings
 */
class BannerTimingsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BannerTimings[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BannerTimings|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
