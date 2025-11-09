<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\WhatsappConversationFlows as BaseWhatsappConversationFlows;

/**
 * This is the model class for table "whatsapp_conversation_flows".
 */
class WhatsappConversationFlows extends BaseWhatsappConversationFlows
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['state', 'pattern', 'response_text', 'next_state'], 'required'],
            [['response_text', 'response_interactive'], 'string'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['language'], 'string', 'max' => 10],
            [['state', 'next_state'], 'string', 'max' => 50],
            [['pattern'], 'string', 'max' => 255],
            [['language', 'state', 'pattern'], 'unique', 'targetAttribute' => ['language', 'state', 'pattern'], 'message' => 'The combination of Language, State and Pattern has already been taken.']
        ]);
    }
	

}
