<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ProductServicesUsed as BaseProductServicesUsed;

/**
 * This is the model class for table "product_services_used".
 */
class ProductServicesUsed extends BaseProductServicesUsed
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['product_services_id', 'order_id'], 'required'],
            [['product_services_id', 'order_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer']
        ]);
    }
	

}
