<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\BannerTimings as BaseBannerTimings;

/**
 * This is the model class for table "banner_timings".
 */
class BannerTimings extends BaseBannerTimings
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['banner_id', 'start_time', 'end_time'], 'required'],
            [['banner_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['start_time', 'end_time', 'created_on', 'updated_on'], 'safe']
        ]);
    }
    public function getBannerTimings()
{
    return $this->hasMany(BannerTimings::class, ['banner_id' => 'id']);
}
public function getBannerChargeLogs()
{
    return $this->hasMany(\app\modules\admin\models\BannerChargeLogs::class, ['banner_id' => 'id']);
}


	

}
