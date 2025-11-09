<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[CartItems]].
 *
 * @see CartItems
 */
class CartItemsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return CartItems[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CartItems|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
