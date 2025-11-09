<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[SubCategory]].
 *
 * @see SubCategory
 */
class SubCategoryQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return SubCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SubCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
