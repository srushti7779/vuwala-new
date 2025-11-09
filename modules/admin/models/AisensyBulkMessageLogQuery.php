<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[AisensyBulkMessageLog]].
 *
 * @see AisensyBulkMessageLog
 */
class AisensyBulkMessageLogQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return AisensyBulkMessageLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AisensyBulkMessageLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
