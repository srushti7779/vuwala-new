<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ReelsViewCounts as BaseReelsViewCounts;

/**
 * This is the model class for table "reels_view_counts".
 */
class ReelsViewCounts extends BaseReelsViewCounts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['real_id'], 'required'],
            [['real_id', 'user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['ip_address'], 'string', 'max' => 45]
        ]);
    }
	

}
