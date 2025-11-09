<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ReelsLikes as BaseReelsLikes;

/**
 * This is the model class for table "reels_likes".
 */
class ReelsLikes extends BaseReelsLikes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['reel_id', 'user_id'], 'required'],
            [['reel_id', 'user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
