<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorHasMenuPermissions as BaseVendorHasMenuPermissions;

/**
 * This is the model class for table "vendor_has_menu_permissions".
 */
class VendorHasMenuPermissions extends BaseVendorHasMenuPermissions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_has_menu_id', 'menu_permissions_id'], 'required'],
            [['vendor_has_menu_id', 'menu_permissions_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
