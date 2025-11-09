<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\WhatsappRegistrationRequests as BaseWhatsappRegistrationRequests;

/**
 * This is the model class for table "whatsapp_registration_requests".
 */
class WhatsappRegistrationRequests extends BaseWhatsappRegistrationRequests
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['source', 'src_id'], 'required'],
            [['src_id', 'city_id', 'status'], 'integer'],
            [['address', 'extra'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['source'], 'string', 'max' => 20],
            [['username', 'email', 'business_name', 'gst_number'], 'string', 'max' => 255],
            [['contact_no'], 'string', 'max' => 50],
            [['first_name', 'last_name'], 'string', 'max' => 64]
        ]);
    }
	

}
