<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ProductTypes as BaseProductTypes;

/**
 * This is the model class for table "product_types".
 */
class ProductTypes extends BaseProductTypes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['product_type_name'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['product_type_name'], 'string', 'max' => 255]
        ]);
    }
	

}
