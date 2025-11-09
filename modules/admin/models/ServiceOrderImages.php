<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\ServiceOrderImages as BaseServiceOrderImages;

/**
 * This is the model class for table "service_order_images".
 */
class ServiceOrderImages extends BaseServiceOrderImages
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['order_id', 'image'], 'required'],
            [['order_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['image'], 'string'],
            [['created_on', 'updated_on'], 'safe']
        ]);
    }
	

}
