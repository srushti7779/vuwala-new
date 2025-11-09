<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ServicePinCode]].
 *
 * @see ServicePinCode
 */
class ServicePinCodeQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ServicePinCode[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ServicePinCode|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
