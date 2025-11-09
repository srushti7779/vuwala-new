<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WastageProducts]].
 *
 * @see WastageProducts
 */
class WastageProductsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WastageProducts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WastageProducts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
