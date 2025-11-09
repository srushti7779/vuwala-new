<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BusinessDocuments]].
 *
 * @see BusinessDocuments
 */
class BusinessDocumentsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BusinessDocuments[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BusinessDocuments|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
