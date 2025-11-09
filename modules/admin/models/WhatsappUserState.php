<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\WhatsappUserState as BaseWhatsappUserState;

/**
 * This is the model class for table "whatsapp_user_state".
 */
class WhatsappUserState extends BaseWhatsappUserState
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['phone_number', 'current_state'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['phone_number'], 'string', 'max' => 20],
            [['current_state'], 'string', 'max' => 50],
            [['language'], 'string', 'max' => 10],
            [['phone_number'], 'unique']
        ]);
    }
	

}
