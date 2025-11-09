<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ProductServiceOrderMappings]].
 *
 * @see ProductServiceOrderMappings
 */
class ProductServiceOrderMappingsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ProductServiceOrderMappings[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductServiceOrderMappings|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
