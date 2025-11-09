<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[ReelReports]].
 *
 * @see ReelReports
 */
class ReelReportsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return ReelReports[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ReelReports|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
