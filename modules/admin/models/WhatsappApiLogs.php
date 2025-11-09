<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\WhatsappApiLogs as BaseWhatsappApiLogs;

/**
 * This is the model class for table "whatsapp_api_logs".
 */
class WhatsappApiLogs extends BaseWhatsappApiLogs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['phone_number', 'payload'], 'required'],
            [['payload', 'response'], 'string'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['phone_number'], 'string', 'max' => 20]
        ]);
    }
	

}
