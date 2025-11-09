<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[HomeVisitorsHasOrders]].
 *
 * @see HomeVisitorsHasOrders
 */
class HomeVisitorsHasOrdersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return HomeVisitorsHasOrders[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return HomeVisitorsHasOrders|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
