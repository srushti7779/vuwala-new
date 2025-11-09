<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "aisensy_bulk_campaign_log".
 *
 * @property string $id
 * @property string $campaign_name
 * @property string $template_id
 * @property integer $total_contacts
 * @property integer $sent_count
 * @property integer $delivered_count
 * @property integer $failed_count
 * @property integer $skipped_count
 * @property string $campaign_status
 * @property string $started_at
 * @property string $completed_at
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\AisensyTemplates $template
 * @property \app\modules\admin\models\AisensyBulkMessageLog[] $aisensyBulkMessageLogs
 */
class AisensyBulkCampaignLog extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

      // Campaign status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'template',
            'aisensyBulkMessageLogs'
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
         [['campaign_name', 'template_id'], 'required'],
            [['template_id', 'total_contacts', 'sent_count', 'delivered_count', 'read_count', 'failed_count', 'skipped_count', 'batch_size', 'create_user_id', 'update_user_id'], 'integer'],
            [['delay_seconds'], 'number'],
            [['campaign_status'], 'string'],
            [['started_at', 'completed_at', 'created_on', 'updated_on'], 'safe'],
            [['error_message', 'performance_metrics'], 'string'],
            [['campaign_name'], 'string'],
            [['excel_filename'], 'string'],
            [['campaign_status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_RUNNING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aisensy_bulk_campaign_log';
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
            'campaign_name' => Yii::t('app', 'Campaign Name'),
            'template_id' => Yii::t('app', 'Template ID'),
            'total_contacts' => Yii::t('app', 'Total Contacts'),
            'sent_count' => Yii::t('app', 'Sent Count'),
            'delivered_count' => Yii::t('app', 'Delivered Count'),
            'failed_count' => Yii::t('app', 'Failed Count'),
            'skipped_count' => Yii::t('app', 'Skipped Count'),
            'campaign_status' => Yii::t('app', 'Campaign Status'),
            'started_at' => Yii::t('app', 'Started At'),
            'completed_at' => Yii::t('app', 'Completed At'),
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
        return $this->hasOne(\app\modules\admin\models\AisensyTemplates::className(), ['id' => 'template_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAisensyBulkMessageLogs()
    {
        return $this->hasMany(\app\modules\admin\models\AisensyBulkMessageLog::className(), ['campaign_id' => 'id']);
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
     * @return \app\modules\admin\models\AisensyBulkCampaignLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\AisensyBulkCampaignLogQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['campaign_name'] =  $this->campaign_name;
        
                $data['template_id'] =  $this->template_id;
        
                $data['total_contacts'] =  $this->total_contacts;
        
                $data['sent_count'] =  $this->sent_count;
        
                $data['delivered_count'] =  $this->delivered_count;
        
                $data['failed_count'] =  $this->failed_count;
        
                $data['skipped_count'] =  $this->skipped_count;
        
                $data['campaign_status'] =  $this->campaign_status;
        
                $data['started_at'] =  $this->started_at;
        
                $data['completed_at'] =  $this->completed_at;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


