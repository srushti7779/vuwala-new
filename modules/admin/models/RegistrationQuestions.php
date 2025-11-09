<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\RegistrationQuestions as BaseRegistrationQuestions;

/**
 * This is the model class for table "registration_questions".
 */
class RegistrationQuestions extends BaseRegistrationQuestions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['question_text', 'column_name', 'target_table'], 'required'],
            [['target_table', 'type', 'meta'], 'string'],
            [['sort_order', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['question_text'], 'string', 'max' => 255],
            [['column_name'], 'string', 'max' => 100],
            [['required'], 'string', 'max' => 1]
        ]);
    }
	

}
