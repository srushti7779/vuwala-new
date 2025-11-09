<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ReelsLikes]].
 *
 * @see ReelsLikes
 */
class ReelsLikesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ReelsLikes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ReelsLikes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
