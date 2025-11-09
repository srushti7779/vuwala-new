<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ServicePinCode as BaseServicePinCode;

/**
 * This is the model class for table "service_pin_code".
 */
class ServicePinCode extends BaseServicePinCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['city_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['area_pin_code'], 'required'],
            [['created_on', 'updated_on'], 'safe'],
            [['area_pin_code'], 'string', 'max' => 255]
        ]);
    }
	

}
