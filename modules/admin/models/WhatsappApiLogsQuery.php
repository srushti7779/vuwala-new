<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappApiLogs]].
 *
 * @see WhatsappApiLogs
 */
class WhatsappApiLogsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WhatsappApiLogs[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappApiLogs|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
