<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[TemporaryUsers]].
 *
 * @see TemporaryUsers
 */
class TemporaryUsersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return TemporaryUsers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TemporaryUsers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
