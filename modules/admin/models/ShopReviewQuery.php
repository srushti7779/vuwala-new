<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ShopReview]].
 *
 * @see ShopReview
 */
class ShopReviewQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ShopReview[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ShopReview|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
