<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BotSessions as BaseBotSessions;

/**
 * This is the model class for table "bot_sessions".
 */
class BotSessions extends BaseBotSessions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'current_question_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['last_message'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['session_uuid'], 'string', 'max' => 100]
        ]);
    }
	

}
