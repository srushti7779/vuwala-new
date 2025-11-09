<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[EmailOtpVerifications]].
 *
 * @see EmailOtpVerifications
 */
class EmailOtpVerificationsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return EmailOtpVerifications[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return EmailOtpVerifications|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
