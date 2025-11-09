<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ComboPackages]].
 *
 * @see ComboPackages
 */
class ComboPackagesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ComboPackages[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ComboPackages|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
