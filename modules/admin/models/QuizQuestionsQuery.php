<?php

namespace app\modules\admin\models;

/**
 * This is the ActiveQuery class for [[QuizQuestions]].
 *
 * @see QuizQuestions
 */
class QuizQuestionsQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return QuizQuestions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return QuizQuestions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
