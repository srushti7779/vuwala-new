<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\CouponHasDays as BaseCouponHasDays;

/**
 * This is the model class for table "coupon_has_days".
 */
class CouponHasDays extends BaseCouponHasDays
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['coupon_id', 'day'], 'required'],
            [['coupon_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['day'], 'string', 'max' => 20]
        ]);
    }
	

}
