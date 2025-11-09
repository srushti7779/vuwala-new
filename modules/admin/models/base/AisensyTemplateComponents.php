<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "aisensy_template_components".
 *
 * @property string $id
 * @property string $template_id
 * @property integer $component_index
 * @property string $type
 * @property string $format
 * @property string $text
 * @property array $example
 * @property array $buttons
 * @property array $raw
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\AisensyTemplates $template
 */
class AisensyTemplateComponents extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'template'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'type'], 'required'],
            [['template_id', 'component_index'], 'integer'],
            [['text', 'example', 'buttons', 'raw'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['type', 'format'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aisensy_template_components';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
        }elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }

    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',
           
        ];
    }

    public function getFeatureOptionsBadges()
    {
        if ($this->is_featured == self::IS_FEATURED) {
            return '<span class="badge badge-success">Featured</span>';
        } elseif ($this->is_featured == self::IS_NOT_FEATURED) {
            return '<span class="badge badge-danger">Not Featured</span>';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'template_id' => Yii::t('app', 'Template ID'),
            'component_index' => Yii::t('app', 'Component Index'),
            'type' => Yii::t('app', 'Type'),
            'format' => Yii::t('app', 'Format'),
            'text' => Yii::t('app', 'Text'),
            'example' => Yii::t('app', 'Example'),
            'buttons' => Yii::t('app', 'Buttons'),
            'raw' => Yii::t('app', 'Raw'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(\app\modules\admin\models\AisensyTemplates::className(), ['id' => 'template_id']);
    }
    
    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value' => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\AisensyTemplateComponentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\AisensyTemplateComponentsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['template_id'] =  $this->template_id;
        
                $data['component_index'] =  $this->component_index;
        
                $data['type'] =  $this->type;
        
                $data['format'] =  $this->format;
        
                $data['text'] =  $this->text;
        
                $data['example'] =  $this->example;
        
                $data['buttons'] =  $this->buttons;
        
                $data['raw'] =  $this->raw;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
            return $data;
}


}


