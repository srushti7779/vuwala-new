<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BusinessImages]].
 *
 * @see BusinessImages
 */
class BusinessImagesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BusinessImages[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BusinessImages|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
