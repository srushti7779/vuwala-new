<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ComboOrder as BaseComboOrder;

/**
 * This is the model class for table "combo_order".
 */
class ComboOrder extends BaseComboOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'vendor_details_id', 'combo_package_id', 'amount'], 'required'],
            [['order_id', 'vendor_details_id', 'combo_package_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['amount'], 'number'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
