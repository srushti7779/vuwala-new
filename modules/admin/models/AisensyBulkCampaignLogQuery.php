<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[AisensyBulkCampaignLog]].
 *
 * @see AisensyBulkCampaignLog
 */
class AisensyBulkCampaignLogQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return AisensyBulkCampaignLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AisensyBulkCampaignLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
