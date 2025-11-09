<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\UserRoles as BaseUserRoles;

/**
 * This is the model class for table "user_roles".
 */
class UserRoles extends BaseUserRoles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['status'], 'string', 'max' => 1]
        ]);
    }
	
}
