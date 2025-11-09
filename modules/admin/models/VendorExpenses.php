<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorExpenses as BaseVendorExpenses;

/**
 * This is the model class for table "vendor_expenses".
 */
class VendorExpenses extends BaseVendorExpenses
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_details_id', 'expense_type_id', 'payment_mode', 'expense_date', 'amount'], 'required'],
            [['vendor_details_id', 'expense_type_id', 'payment_mode', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['expense_date', 'created_on', 'updated_on'], 'safe'],
            [['amount'], 'number'],
            [['notes'], 'string'],
            [['image_url'], 'string', 'max' => 512]
        ]);
    }
	

}
