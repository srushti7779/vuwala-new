<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Wallet]].
 *
 * @see Wallet
 */
class WalletQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Wallet[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Wallet|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
