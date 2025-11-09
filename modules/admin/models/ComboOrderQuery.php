<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ComboOrder]].
 *
 * @see ComboOrder
 */
class ComboOrderQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ComboOrder[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ComboOrder|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
