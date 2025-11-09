<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ServiceOrdersProductOrders as BaseServiceOrdersProductOrders;

/**
 * This is the model class for table "service_orders_product_orders".
 */
class ServiceOrdersProductOrders extends BaseServiceOrdersProductOrders
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['service_order_id', 'product_order_id'], 'required'],
            [['service_order_id', 'product_order_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer']
        ]);
    }
	

}
