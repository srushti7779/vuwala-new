<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "aisensy_templates".
 *
 * @property string $id
 * @property string $external_id
 * @property string $name
 * @property string $category
 * @property string $language
 * @property integer $status
 * @property array $quality_score
 * @property string $rejected_reason
 * @property string $footer_text
 * @property string $body_text
 * @property array $meta
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\AisensyTemplateComponents[] $aisensyTemplateComponents
 * @property \app\modules\admin\models\AisensyTemplateLinks[] $aisensyTemplateLinks
 */
class AisensyTemplates extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'aisensyTemplateComponents',
            'aisensyTemplateLinks'
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
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aisensy_templates';
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
            'external_id' => Yii::t('app', 'External ID'),
            'name' => Yii::t('app', 'Name'),
            'category' => Yii::t('app', 'Category'),
            'language' => Yii::t('app', 'Language'),
            'status' => Yii::t('app', 'Status'),
            'quality_score' => Yii::t('app', 'Quality Score'),
            'rejected_reason' => Yii::t('app', 'Rejected Reason'),
            'footer_text' => Yii::t('app', 'Footer Text'),
            'body_text' => Yii::t('app', 'Body Text'),
            'meta' => Yii::t('app', 'Meta'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAisensyTemplateComponents()
    {
        return $this->hasMany(\app\modules\admin\models\AisensyTemplateComponents::className(), ['template_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAisensyTemplateLinks()
    {
        return $this->hasMany(\app\modules\admin\models\AisensyTemplateLinks::className(), ['template_id' => 'id']);
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
     * @return \app\modules\admin\models\AisensyTemplatesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\AisensyTemplatesQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['external_id'] =  $this->external_id;
        
                $data['name'] =  $this->name;
        
                $data['category'] =  $this->category;
        
                $data['language'] =  $this->language;
        
                $data['status'] =  $this->status;
        
                $data['quality_score'] =  $this->quality_score;
        
                $data['rejected_reason'] =  $this->rejected_reason;
        
                $data['footer_text'] =  $this->footer_text;
        
                $data['body_text'] =  $this->body_text;
        
                $data['meta'] =  $this->meta;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


