<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\RoleMenuPermissions as BaseRoleMenuPermissions;

/**
 * This is the model class for table "role_menu_permissions".
 */
class RoleMenuPermissions extends BaseRoleMenuPermissions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['role_id', 'menu_permission_id'], 'required'],
            [['role_id', 'menu_permission_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['status'], 'string', 'max' => 1]
        ]);
    }
	
}
