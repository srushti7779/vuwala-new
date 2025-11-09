<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\SupportTickets as BaseSupportTickets;

/**
 * This is the model class for table "support_tickets".
 */
class SupportTickets extends BaseSupportTickets
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_details_id', 'subject', 'message'], 'required'],
            [['vendor_details_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer'],
            [['message'], 'string'],
            [['subject'], 'string', 'max' => 512]
        ]);
    }
	

}
