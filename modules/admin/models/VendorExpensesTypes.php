<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorExpensesTypes as BaseVendorExpensesTypes;

/**
 * This is the model class for table "vendor_expenses_types".
 */
class VendorExpensesTypes extends BaseVendorExpensesTypes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_details_id', 'main_vendor_user_id', 'type'], 'required'],
            [['vendor_details_id', 'main_vendor_user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['type'], 'string', 'max' => 100]
        ]);
    }
	

}
