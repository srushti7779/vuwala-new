<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BannerLogs]].
 *
 * @see BannerLogs
 */
class BannerLogsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BannerLogs[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BannerLogs|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
