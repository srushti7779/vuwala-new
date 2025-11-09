<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\TemporaryUsers as BaseTemporaryUsers;

/**
 * This is the model class for table "temporary_users".
 */
class TemporaryUsers extends BaseTemporaryUsers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['username', 'contact_no', 'first_name', 'email', 'user_role'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['username', 'first_name', 'email', 'device_token', 'brand_name', 'brand_logo'], 'string', 'max' => 255],
            [['contact_no'], 'string', 'max' => 20],
            [['unique_user_id', 'device_type', 'user_role', 'referral_code', 'vendor_store_type'], 'string', 'max' => 50],
            [['is_featured'], 'string', 'max' => 1]
        ]);
    }
	

}
