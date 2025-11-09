<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[RegistrationAnswers]].
 *
 * @see RegistrationAnswers
 */
class RegistrationAnswersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return RegistrationAnswers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return RegistrationAnswers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
