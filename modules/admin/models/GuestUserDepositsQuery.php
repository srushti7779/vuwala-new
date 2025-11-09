<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[GuestUserDeposits]].
 *
 * @see GuestUserDeposits
 */
class GuestUserDepositsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return GuestUserDeposits[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return GuestUserDeposits|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
