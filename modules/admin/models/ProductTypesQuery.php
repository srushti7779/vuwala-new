<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductTypes]].
 *
 * @see ProductTypes
 */
class ProductTypesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductTypes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductTypes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
