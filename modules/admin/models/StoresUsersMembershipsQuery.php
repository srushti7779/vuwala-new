<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[StoresUsersMemberships]].
 *
 * @see StoresUsersMemberships
 */
class StoresUsersMembershipsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return StoresUsersMemberships[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoresUsersMemberships|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
