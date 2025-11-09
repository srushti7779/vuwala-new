<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\NextVisitDateAndTime as BaseNextVisitDateAndTime;

/**
 * This is the model class for table "next_visit_date_and_time".
 */
class NextVisitDateAndTime extends BaseNextVisitDateAndTime
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['next_visit_details_id', 'date', 'time'], 'required'],
            [['next_visit_details_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['date', 'created_on', 'updated_on'], 'safe'],
            [['time'], 'string', 'max' => 50]
        ]);
    }
	

}
