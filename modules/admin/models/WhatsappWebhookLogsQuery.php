<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappWebhookLogs]].
 *
 * @see WhatsappWebhookLogs
 */
class WhatsappWebhookLogsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WhatsappWebhookLogs[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappWebhookLogs|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
