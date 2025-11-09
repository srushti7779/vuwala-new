<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\AisensyTemplates as BaseAisensyTemplates;

/**
 * This is the model class for table "aisensy_templates".
 */
class AisensyTemplates extends BaseAisensyTemplates
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['name'], 'required'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['quality_score', 'body_text', 'meta'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['external_id'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 191],
            [['category'], 'string', 'max' => 50],
            [['language'], 'string', 'max' => 32],
            [['rejected_reason'], 'string', 'max' => 255],
            [['footer_text'], 'string', 'max' => 512],
            [['external_id', 'name'], 'unique', 'targetAttribute' => ['external_id', 'name'], 'message' => 'The combination of External ID and Name has already been taken.']
        ]);
    }
	

}
