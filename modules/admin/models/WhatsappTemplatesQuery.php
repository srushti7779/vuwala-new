<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WhatsappTemplates]].
 *
 * @see WhatsappTemplates
 */
class WhatsappTemplatesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WhatsappTemplates[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WhatsappTemplates|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
