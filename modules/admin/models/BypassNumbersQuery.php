<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[BypassNumbers]].
 *
 * @see BypassNumbers
 */
class BypassNumbersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return BypassNumbers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return BypassNumbers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
