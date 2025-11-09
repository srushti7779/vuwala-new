<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Sku]].
 *
 * @see Sku
 */
class SkuQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Sku[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Sku|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
