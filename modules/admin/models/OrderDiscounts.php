<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\OrderDiscounts as BaseOrderDiscounts;

/**
 * This is the model class for table "order_discounts".
 */
class OrderDiscounts extends BaseOrderDiscounts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'discount_amount'], 'required'],
            [['order_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['discount_amount'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
            [['discount_type', 'discount_code'], 'string', 'max' => 50]
        ]);
    }
	

}
