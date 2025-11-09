<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[Menus]].
 *
 * @see Menus
 */
class MenusQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return Menus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Menus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
