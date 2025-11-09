<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Expensive]].
 *
 * @see Expensive
 */
class ExpensiveQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Expensive[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Expensive|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
