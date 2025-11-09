<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ReelReports as BaseReelReports;

/**
 * This is the model class for table "reel_reports".
 */
class ReelReports extends BaseReelReports
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['user_id', 'reel_id'], 'required'],
            [['user_id', 'reel_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['feedback'], 'string'],
            [['reported_at', 'created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
