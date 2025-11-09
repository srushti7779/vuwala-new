<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductServices]].
 *
 * @see ProductServices
 */
class ProductServicesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductServices[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductServices|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
