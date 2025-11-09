<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\Brands as BaseBrands;

/**
 * This is the model class for table "brands".
 */
class Brands extends BaseBrands
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['brand_name'], 'required'],
            [['is_global', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['image'], 'string', 'max' => 512]
        ]);
    }
	

}
