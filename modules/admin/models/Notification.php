<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\Notification as BaseNotification;

/**
 * This is the model class for table "notification".
 */
class Notification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'order_id', 'created_user_id', 'check_on_ajax', 'is_deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['title', 'created_user_id', 'is_deleted', 'info_delete'], 'required'],
            [['created_date', 'created_on', 'updated_on'], 'safe'],
            [['title', 'module', 'icon', 'model_type', 'info_delete'], 'string', 'max' => 255],
            [['mark_read', 'status'], 'string', 'max' => 4]
        ]);
    }
	

}
