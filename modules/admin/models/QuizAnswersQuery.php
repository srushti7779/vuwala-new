<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[QuizAnswers]].
 *
 * @see QuizAnswers
 */
class QuizAnswersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return QuizAnswers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return QuizAnswers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
