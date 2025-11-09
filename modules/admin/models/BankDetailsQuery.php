<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BankDetails]].
 *
 * @see BankDetails
 */
class BankDetailsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BankDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BankDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
