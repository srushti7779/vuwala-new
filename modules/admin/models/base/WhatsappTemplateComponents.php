<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "whatsapp_template_components".
 *
 * @property integer $id
 * @property integer $template_id
 * @property string $type
 * @property string $subtype
 * @property integer $param_order
 * @property string $default_value
 * @property string $variable_name
 * @property integer $is_required
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\WhatsappTemplates $template
 */
class WhatsappTemplateComponents extends \yii\db\ActiveRecord
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

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    const COMPONENT_TYPE_HEADER = 'HEADER';
    const COMPONENT_TYPE_BODY = 'BODY';
    const COMPONENT_TYPE_FOOTER = 'FOOTER';
    const COMPONENT_TYPE_BUTTONS = 'BUTTONS';
    
    const FORMAT_TEXT = 'TEXT';
    const FORMAT_IMAGE = 'IMAGE';
    const FORMAT_VIDEO = 'VIDEO';
    const FORMAT_DOCUMENT = 'DOCUMENT';


    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    public function rules()
    {
        return [
            [['template_id', 'type', 'default_value'], 'required'],
            [['template_id', 'param_order', 'is_required', 'status'], 'integer'],
            [['type', 'subtype', 'variable_name', 'default_value'], 'string', 'max' => 255],
            [['created_on'], 'safe'],
            [['create_user_id'], 'integer'],
            [['type'], 'in', 'range' => ['header', 'body', 'button']],
            [['subtype'], 'in', 'range' => ['TEXT', 'IMAGE', 'VIDEO', 'DOCUMENT'], 'when' => function($model) {
                return $model->type === 'header';
            }],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'whatsapp_template_components';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
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
            'type' => Yii::t('app', 'Type'),
            'subtype' => Yii::t('app', 'Subtype'),
            'param_order' => Yii::t('app', 'Param Order'),
            'default_value' => Yii::t('app', 'Default Value'),
            'variable_name' => Yii::t('app', 'Variable Name'),
            'is_required' => Yii::t('app', 'Is Required'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(\app\modules\admin\models\WhatsappTemplates::className(), ['id' => 'template_id']);
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
     * Get parameters as array
     * @return array
     */
    public function getParametersArray()
    {
        return $this->parameters ? json_decode($this->parameters, true) : [];
    }

    /**
     * Set parameters from array
     * @param array $params
     */
    public function setParametersArray($params)
    {
        $this->parameters = json_encode($params);
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\WhatsappTemplateComponentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WhatsappTemplateComponentsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['template_id'] =  $this->template_id;
        
                $data['type'] =  $this->type;
        
                $data['subtype'] =  $this->subtype;
        
                $data['param_order'] =  $this->param_order;
        
                $data['default_value'] =  $this->default_value;
        
                $data['variable_name'] =  $this->variable_name;
        
                $data['is_required'] =  $this->is_required;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


