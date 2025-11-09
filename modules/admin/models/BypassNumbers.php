<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BypassNumbers as BaseBypassNumbers;

/**
 * This is the model class for table "bypass_numbers".
 */
class BypassNumbers extends BaseBypassNumbers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['mobile_number'], 'required'],
            [['created_on', 'updated_on'], 'safe'],
            [['create_user_id', 'update_user_id'], 'integer'],
            [['mobile_number'], 'string', 'max' => 10]
        ]);
    }
	

}
