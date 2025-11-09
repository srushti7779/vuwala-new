<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\QuizAnswers as BaseQuizAnswers;

/**
 * This is the model class for table "quiz_answers".
 */
class QuizAnswers extends BaseQuizAnswers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['question_id', 'answer_text'], 'required'],
            [['question_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['answer_text'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['is_correct'], 'string', 'max' => 1]
        ]);
    }
	

}
