<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\NextVisitDetails as BaseNextVisitDetails;

/**
 * This is the model class for table "next_visit_details".
 */
class NextVisitDetails extends BaseNextVisitDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['next_visit_details_json', 'description'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['prescription_file'], 'string', 'max' => 512]
        ]);
    }
	

}
