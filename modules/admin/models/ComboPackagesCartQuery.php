<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ComboPackagesCart]].
 *
 * @see ComboPackagesCart
 */
class ComboPackagesCartQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ComboPackagesCart[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ComboPackagesCart|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
