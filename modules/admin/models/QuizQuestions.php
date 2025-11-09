<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\QuizQuestions as BaseQuizQuestions;

/**
 * This is the model class for table "quiz_questions".
 */
class QuizQuestions extends BaseQuizQuestions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['quiz_id', 'question_text'], 'required'],
            [['quiz_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['question_text'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['question_type'], 'string', 'max' => 50]
        ]);
    }
	

}
