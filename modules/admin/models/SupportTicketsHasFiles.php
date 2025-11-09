<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\SupportTicketsHasFiles as BaseSupportTicketsHasFiles;

/**
 * This is the model class for table "support_tickets_has_files".
 */
class SupportTicketsHasFiles extends BaseSupportTicketsHasFiles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['support_ticket_id', 'file'], 'required'],
            [['support_ticket_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer'],
            [['file'], 'string', 'max' => 512]
        ]);
    }
	

}
