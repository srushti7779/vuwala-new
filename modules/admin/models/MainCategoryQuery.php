<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[MainCategory]].
 *
 * @see MainCategory
 */
class MainCategoryQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return MainCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return MainCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
