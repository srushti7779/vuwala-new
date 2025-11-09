<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[StoresHasUsers]].
 *
 * @see StoresHasUsers
 */
class StoresHasUsersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return StoresHasUsers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoresHasUsers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
