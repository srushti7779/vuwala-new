<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\Uploads as BaseUploads;

/**
 * This is the model class for table "uploads".
 */
class Uploads extends BaseUploads
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['entity_id', 'file_size'], 'integer'],
            [['file_url'], 'required'],
            [['created_on', 'updated_on'], 'safe'],
            [['entity_type'], 'string', 'max' => 50],
            [['file_url'], 'string', 'max' => 512],
            [['file_name'], 'string', 'max' => 255],
            [['file_type'], 'string', 'max' => 100],
            [['extension'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 1]
        ]);
    }
	

}
