<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[OrderComplaints]].
 *
 * @see OrderComplaints
 */
class OrderComplaintsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return OrderComplaints[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderComplaints|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
