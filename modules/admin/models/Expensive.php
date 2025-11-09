<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\Expensive as BaseExpensive;

/**
 * This is the model class for table "expensive".
 */
class Expensive extends BaseExpensive
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['expensive_name'], 'required'],
            [['status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer'],
            [['expensive_name'], 'string', 'max' => 50]
        ]);
    }
	

}
