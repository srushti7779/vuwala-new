<?php

namespace app\modules\admin\models\base;

/**
 * This is the ActiveQuery class for [[\app\modules\admin\models\base\Sku]].
 *
 * @see \app\modules\admin\models\base\Sku
 */
class SkuQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\base\Sku[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\admin\models\base\Sku|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
