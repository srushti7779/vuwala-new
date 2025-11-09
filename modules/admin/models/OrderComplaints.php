<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\OrderComplaints as BaseOrderComplaints;

/**
 * This is the model class for table "order_complaints".
 */
class OrderComplaints extends BaseOrderComplaints
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'user_id'], 'required'],
            [['order_id', 'user_id', 'store_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['description', 'response'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['title'], 'string', 'max' => 255]
        ]);
    }
	
}
