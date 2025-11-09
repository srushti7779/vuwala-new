<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[MenuPermissions]].
 *
 * @see MenuPermissions
 */
class MenuPermissionsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return MenuPermissions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return MenuPermissions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
