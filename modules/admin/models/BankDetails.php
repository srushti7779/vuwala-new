<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BankDetails as BaseBankDetails;

/**
 * This is the model class for table "bank_details".
 */
class BankDetails extends BaseBankDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'bank_account_holder_name', 'bank_name', 'bank_account_number', 'ifsc'], 'required'],
            [['user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['bank_account_holder_name', 'bank_name', 'bank_account_number', 'ifsc', 'branch', 'upi'], 'string', 'max' => 255],
            [['branch_address'], 'string', 'max' => 500]
        ]);
    }
	

}
