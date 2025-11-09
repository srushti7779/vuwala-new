<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\WasteTypes as BaseWasteTypes;

/**
 * This is the model class for table "waste_types".
 */
class WasteTypes extends BaseWasteTypes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['wastage_type'], 'required'],
            [['status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer'],
            [['wastage_type'], 'string', 'max' => 255]
        ]);
    }
	

}
