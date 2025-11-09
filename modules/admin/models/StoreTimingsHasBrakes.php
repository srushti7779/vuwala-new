<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\StoreTimingsHasBrakes as BaseStoreTimingsHasBrakes;

/**
 * This is the model class for table "store_timings_has_brakes".
 */
class StoreTimingsHasBrakes extends BaseStoreTimingsHasBrakes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['store_timing_id', 'start_time', 'end_time'], 'required'],
            [['store_timing_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['start_time', 'end_time'], 'string', 'max' => 50]
        ]);
    }
	

}
