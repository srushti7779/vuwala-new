<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ServiceType]].
 *
 * @see ServiceType
 */
class ServiceTypeQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ServiceType[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ServiceType|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
