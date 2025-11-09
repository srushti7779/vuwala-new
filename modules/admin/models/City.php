<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\City as BaseCity;

/**
 * This is the model class for table "city".
 */
class City extends BaseCity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['name'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['name'], 'string', 'max' => 255]
        ]);
    }
	

}
