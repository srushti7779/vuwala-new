<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Days]].
 *
 * @see Days
 */
class DaysQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Days[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Days|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
