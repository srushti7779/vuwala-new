<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\AisensyTemplateLinks as BaseAisensyTemplateLinks;

/**
 * This is the model class for table "aisensy_template_links".
 */
class AisensyTemplateLinks extends BaseAisensyTemplateLinks
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['template_id', 'type'], 'required'],
            [['template_id'], 'integer'],
            [['type'], 'string'],
            [['label'], 'string', 'max' => 191],
            [['value'], 'string', 'max' => 512]
        ]);
    }
	

}
