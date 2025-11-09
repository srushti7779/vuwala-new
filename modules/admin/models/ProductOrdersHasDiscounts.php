<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ProductOrdersHasDiscounts as BaseProductOrdersHasDiscounts;

/**
 * This is the model class for table "product_orders_has_discounts".
 */
class ProductOrdersHasDiscounts extends BaseProductOrdersHasDiscounts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['product_order_id', 'discount_amount'], 'required'],
            [['product_order_id', 'coupon_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['discount_percentage', 'discount_amount'], 'number'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
