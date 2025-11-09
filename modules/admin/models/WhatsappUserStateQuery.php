<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappUserState]].
 *
 * @see WhatsappUserState
 */
class WhatsappUserStateQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WhatsappUserState[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappUserState|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
