<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Reels]].
 *
 * @see Reels
 */
class ReelsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Reels[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Reels|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
