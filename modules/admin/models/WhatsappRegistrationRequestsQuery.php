<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappRegistrationRequests]].
 *
 * @see WhatsappRegistrationRequests
 */
class WhatsappRegistrationRequestsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WhatsappRegistrationRequests[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappRegistrationRequests|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
