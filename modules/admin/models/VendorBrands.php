<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\VendorBrands as BaseVendorBrands;

/**
 * This is the model class for table "vendor_brands".
 */
class VendorBrands extends BaseVendorBrands
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'name'], 'required'],
            [['user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['brand_logo'], 'string', 'max' => 512]
        ]);
    }
	

}
