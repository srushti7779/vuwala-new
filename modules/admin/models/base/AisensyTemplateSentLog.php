<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "aisensy_template_sent_log".
 *
 * @property integer $id
 * @property integer $template_id
 * @property string $contact_number
 * @property string $sent_date
 * @property string $sent_at
 * @property string $message_id
 * @property string $message_status
 * @property string $api_response
 * @property array $template_params
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 */
class AisensyTemplateSentLog extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            ''
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
            [['template_id', 'contact_number', 'sent_date'], 'required'],
            [['template_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['sent_date', 'sent_at', 'created_on', 'updated_on'], 'safe'],
            [['message_status', 'api_response', 'template_params'], 'string'],
            [['contact_number'], 'string', 'max' => 15],
            [['message_id'], 'string', 'max' => 100],
            [['template_id', 'contact_number', 'sent_date'], 'unique', 'targetAttribute' => ['template_id', 'contact_number', 'sent_date'], 'message' => 'The combination of Template ID, Contact Number and Sent Date has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aisensy_template_sent_log';
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
            'contact_number' => Yii::t('app', 'Contact Number'),
            'sent_date' => Yii::t('app', 'Sent Date'),
            'sent_at' => Yii::t('app', 'Sent At'),
            'message_id' => Yii::t('app', 'Message ID'),
            'message_status' => Yii::t('app', 'Message Status'),
            'api_response' => Yii::t('app', 'Api Response'),
            'template_params' => Yii::t('app', 'Template Params'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
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
     * @return \app\modules\admin\models\AisensyTemplateSentLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\AisensyTemplateSentLogQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['template_id'] =  $this->template_id;
        
                $data['contact_number'] =  $this->contact_number;
        
                $data['sent_date'] =  $this->sent_date;
        
                $data['sent_at'] =  $this->sent_at;
        
                $data['message_id'] =  $this->message_id;
        
                $data['message_status'] =  $this->message_status;
        
                $data['api_response'] =  $this->api_response;
        
                $data['template_params'] =  $this->template_params;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


