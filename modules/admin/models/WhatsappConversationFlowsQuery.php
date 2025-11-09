<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappConversationFlows]].
 *
 * @see WhatsappConversationFlows
 */
class WhatsappConversationFlowsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WhatsappConversationFlows[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappConversationFlows|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
