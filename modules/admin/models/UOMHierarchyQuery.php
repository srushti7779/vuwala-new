<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[UOMHierarchy]].
 *
 * @see UOMHierarchy
 */
class UOMHierarchyQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return UOMHierarchy[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UOMHierarchy|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
