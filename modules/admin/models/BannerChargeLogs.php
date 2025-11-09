<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BannerChargeLogs as BaseBannerChargeLogs;

/**
 * This is the model class for table "banner_charge_logs".
 */
class BannerChargeLogs extends BaseBannerChargeLogs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'banner_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['banner_id', 'action', 'user_agent'], 'required'],
            [['action', 'user_agent'], 'string'],
            [['charge_amount'], 'number'],
            [['performed_at', 'created_on', 'updated_on'], 'safe'],
            [['ip_address'], 'string', 'max' => 45]
        ]);
    }
	
}
