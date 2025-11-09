<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\AisensyWebhooks as BaseAisensyWebhooks;

/**
 * This is the model class for table "aisensy_webhooks".
 */
class AisensyWebhooks extends BaseAisensyWebhooks
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['payload', 'headers'], 'string'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['event_type'], 'string', 'max' => 100],
            [['message_id'], 'string', 'max' => 191],
            [['from_number', 'to_number', 'status_value', 'error_code'], 'string', 'max' => 50],
            [['error_message'], 'string', 'max' => 255]
        ]);
    }
	

}
