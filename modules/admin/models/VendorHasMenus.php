<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorHasMenus as BaseVendorHasMenus;

/**
 * This is the model class for table "vendor_has_menus".
 */
class VendorHasMenus extends BaseVendorHasMenus
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_id', 'menu_id'], 'required'],
            [['vendor_id', 'menu_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['status'], 'string', 'max' => 1],
             [['vendor_id', 'menu_id'], 'unique', 'targetAttribute' => ['vendor_id', 'menu_id'], 'message' => 'The combination of Vendor ID and Menu ID has already been taken.']
        ]);
    }
	
}
