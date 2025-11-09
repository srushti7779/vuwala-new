<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ReelTags]].
 *
 * @see ReelTags
 */
class ReelTagsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ReelTags[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ReelTags|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
