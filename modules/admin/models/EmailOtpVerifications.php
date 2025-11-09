<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\EmailOtpVerifications as BaseEmailOtpVerifications;

/**
 * This is the model class for table "email_otp_verifications".
 */
class EmailOtpVerifications extends BaseEmailOtpVerifications
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['email', 'otp'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['email'], 'string', 'max' => 255],
            [['otp'], 'string', 'max' => 6],
            [['is_verified'], 'string', 'max' => 1]
        ]);
    }
	

}
