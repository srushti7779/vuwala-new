<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ReelShareCounts as BaseReelShareCounts;

/**
 * This is the model class for table "reel_share_counts".
 */
class ReelShareCounts extends BaseReelShareCounts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['real_id'], 'required'],
            [['real_id', 'user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['platform'], 'string', 'max' => 50]
        ]);
    }
    public function getUser()
{
    return $this->hasOne(User::class, ['id' => 'user_id']);
}

	

}
