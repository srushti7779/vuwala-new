<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\RegistrationAnswers as BaseRegistrationAnswers;

/**
 * This is the model class for table "registration_answers".
 */
class RegistrationAnswers extends BaseRegistrationAnswers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['session_id'], 'required'],
            [['session_id', 'question_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['answer_text', 'answer_json'], 'string'],
            [['received_at', 'created_on', 'updated_on'], 'safe'],
            [['question_key'], 'string', 'max' => 100]
        ]);
    }
	

}
