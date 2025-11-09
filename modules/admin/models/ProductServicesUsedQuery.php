<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductServicesUsed]].
 *
 * @see ProductServicesUsed
 */
class ProductServicesUsedQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductServicesUsed[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductServicesUsed|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
