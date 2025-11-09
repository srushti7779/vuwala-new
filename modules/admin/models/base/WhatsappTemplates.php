<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "whatsapp_templates".
 *
 * @property integer $id
 * @property string $name
 * @property string $language_code
 * @property string $description
 * @property string $category
 * @property string $template_status
 * @property string $fb_template_id
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\WhatsappTemplateComponents[] $whatsappTemplateComponents
 */
class WhatsappTemplates extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'whatsappTemplateComponents'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const TEMPLATE_STATUS_APPROVED = 'APPROVED';
    const TEMPLATE_STATUS_PENDING = 'PENDING';
    const TEMPLATE_STATUS_REJECTED = 'REJECTED';
    const TEMPLATE_STATUS_DELETED = 'DELETED';
    
    const CATEGORY_MARKETING = 'MARKETING';
    const CATEGORY_UTILITY = 'UTILITY';
    const CATEGORY_AUTHENTICATION = 'AUTHENTICATION';
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'language_code', 'category'], 'required'],
            [['description'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['create_user_id', 'update_user_id'], 'integer'],
            [['name', 'fb_template_id'], 'string', 'max' => 100],
            [['language_code'], 'string', 'max' => 10],
            [['category', 'template_status'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'whatsapp_templates';
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

        public function getComponents()
    {
        return $this->hasMany(WhatsappTemplateComponents::class, ['template_id' => 'id'])
                    ->orderBy(['param_order' => SORT_ASC]);
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
            'name' => Yii::t('app', 'Name'),
            'language_code' => Yii::t('app', 'Language Code'),
            'description' => Yii::t('app', 'Description'),
            'category' => Yii::t('app', 'Category'),
            'template_status' => Yii::t('app', 'Template Status'),
            'fb_template_id' => Yii::t('app', 'Fb Template ID'),
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
    public function getWhatsappTemplateComponents()
    {
        return $this->hasMany(\app\modules\admin\models\WhatsappTemplateComponents::className(), ['template_id' => 'id']);
    }
    
        public static function findActiveTemplate($name)
    {
        return static::find()
            ->where([
                'name' => $name,
                'status' => self::STATUS_ACTIVE,
                'template_status' => self::TEMPLATE_STATUS_APPROVED
            ])
            ->with('components')
            ->one();
    }


        public static function findActiveTemplates()
    {
        return static::find()
            ->where([
                'status' => self::STATUS_ACTIVE,
                'template_status' => self::TEMPLATE_STATUS_APPROVED
            ])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }


        public function isActiveAndApproved()
    {
        return $this->status == self::STATUS_ACTIVE && 
               $this->template_status == self::TEMPLATE_STATUS_APPROVED;
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
     * @return \app\modules\admin\models\WhatsappTemplatesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WhatsappTemplatesQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['name'] =  $this->name;
        
                $data['language_code'] =  $this->language_code;
        
                $data['description'] =  $this->description;
        
                $data['category'] =  $this->category;
        
                $data['template_status'] =  $this->template_status;
        
                $data['fb_template_id'] =  $this->fb_template_id;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


