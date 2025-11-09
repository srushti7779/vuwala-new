<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\Days as BaseDays;

/**
 * This is the model class for table "days".
 */
class Days extends BaseDays
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['title', 'sort_order'], 'required'],
            [['sort_order', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['title'], 'string', 'max' => 255]
        ]);
    }
	

}
