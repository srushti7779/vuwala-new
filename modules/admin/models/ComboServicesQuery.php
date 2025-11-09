<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ComboServices]].
 *
 * @see ComboServices
 */
class ComboServicesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ComboServices[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ComboServices|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
