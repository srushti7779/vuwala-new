<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[NextVisitDetails]].
 *
 * @see NextVisitDetails
 */
class NextVisitDetailsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return NextVisitDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return NextVisitDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
