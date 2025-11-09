<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ProductOrderItemsAssignedDiscounts as BaseProductOrderItemsAssignedDiscounts;

/**
 * This is the model class for table "product_order_items_assigned_discounts".
 */
class ProductOrderItemsAssignedDiscounts extends BaseProductOrderItemsAssignedDiscounts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['product_order_item_id', 'discount_amount'], 'required'],
            [['product_order_item_id', 'coupon_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['discount_percentage', 'discount_amount'], 'number'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
