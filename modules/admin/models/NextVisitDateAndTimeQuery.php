<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[NextVisitDateAndTime]].
 *
 * @see NextVisitDateAndTime
 */
class NextVisitDateAndTimeQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return NextVisitDateAndTime[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return NextVisitDateAndTime|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
