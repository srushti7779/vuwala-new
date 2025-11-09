<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Subscriptions]].
 *
 * @see Subscriptions
 */
class SubscriptionsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Subscriptions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Subscriptions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
