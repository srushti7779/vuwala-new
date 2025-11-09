<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ShopLikes as BaseShopLikes;

/**
 * This is the model class for table "shop_likes".
 */
class ShopLikes extends BaseShopLikes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['vendor_details_id', 'user_id'], 'required'],
            [['vendor_details_id', 'user_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
