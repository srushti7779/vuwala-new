<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[HomeVisitors]].
 *
 * @see HomeVisitors
 */
class HomeVisitorsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return HomeVisitors[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return HomeVisitors|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
