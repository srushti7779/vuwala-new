<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BannerLogs as BaseBannerLogs;

/**
 * This is the model class for table "banner_logs".
 */
class BannerLogs extends BaseBannerLogs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['banner_id', 'action_type'], 'required'],
            [['banner_id', 'user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['action_type', 'user_agent'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['status'], 'string', 'max' => 4]
        ]);
    }
	

}
