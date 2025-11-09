<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Staff]].
 *
 * @see Staff
 */
class StaffQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return Staff[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Staff|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
