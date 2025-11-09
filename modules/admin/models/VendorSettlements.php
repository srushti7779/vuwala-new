<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorSettlements as BaseVendorSettlements;

/**
 * This is the model class for table "vendor_settlements".
 */
class VendorSettlements extends BaseVendorSettlements
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_earnings_id'], 'required'],
            [['vendor_earnings_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer']
        ]);
    }
	

}
