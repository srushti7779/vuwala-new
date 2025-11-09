<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\RescheduleOrderLogs as BaseRescheduleOrderLogs;

/**
 * This is the model class for table "reschedule_order_logs".
 */
class RescheduleOrderLogs extends BaseRescheduleOrderLogs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'reschedule_by_user_id', 'old_dt_time', 'new_dt_time'], 'required'],
            [['order_id', 'reschedule_by_user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['message'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['old_dt_time', 'new_dt_time'], 'string', 'max' => 255]
        ]);
    }
	

}
