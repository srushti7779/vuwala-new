<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[WasteTypes]].
 *
 * @see WasteTypes
 */
class WasteTypesQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return WasteTypes[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WasteTypes|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
