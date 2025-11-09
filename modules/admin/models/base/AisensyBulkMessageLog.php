<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\Json;

/**
 * This is the base model class for table "aisensy_bulk_message_log".
 *
 * @property string $id
 * @property string $campaign_id
 * @property string $template_id
 * @property string $contact_number
 * @property string $message_id
 * @property string $status
 * @property string $skip_reason
 * @property string $sent_datetime
 * @property string $delivered_datetime
 * @property string $read_datetime
 * @property string $error_message
 * @property array $response_data
 * @property string $created_on
 * @property string $updated_on
 *
 * @property \app\modules\admin\models\AisensyBulkCampaignLog $campaign
 * @property \app\modules\admin\models\AisensyTemplates $template
 */
class AisensyBulkMessageLog extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

        // Message status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'campaign',
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
      [['campaign_id', 'template_id', 'contact_number'], 'required'],
            [['campaign_id', 'template_id'], 'integer'],
            [['status'], 'string'],
            [['template_params', 'api_response', 'webhook_updates'], 'string'],
            [['sent_at', 'delivered_at', 'read_at', 'failed_at', 'created_on', 'updated_on'], 'safe'],
            [['contact_number'], 'string'],
            [['message_id'], 'string'],
            [['skip_reason', 'error_message'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_READ, self::STATUS_FAILED, self::STATUS_SKIPPED]]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aisensy_bulk_message_log';
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
            'campaign_id' => Yii::t('app', 'Campaign ID'),
            'template_id' => Yii::t('app', 'Template ID'),
            'contact_number' => Yii::t('app', 'Contact Number'),
            'message_id' => Yii::t('app', 'Message ID'),
            'status' => Yii::t('app', 'Status'),
            'skip_reason' => Yii::t('app', 'Skip Reason'),
            'sent_datetime' => Yii::t('app', 'Sent Datetime'),
            'delivered_datetime' => Yii::t('app', 'Delivered Datetime'),
            'read_datetime' => Yii::t('app', 'Read Datetime'),
            'error_message' => Yii::t('app', 'Error Message'),
            'response_data' => Yii::t('app', 'Response Data'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaign()
    {
        return $this->hasOne(\app\modules\admin\models\AisensyBulkCampaignLog::className(), ['id' => 'campaign_id']);
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
    
























   public static function createMessage($campaignId, $templateId, $contactNumber, $templateParams = [])
    {
        $message = new self();
        $message->campaign_id = $campaignId;
        $message->template_id = $templateId;
        $message->contact_number = $contactNumber;
        $message->template_params = !empty($templateParams) ? Json::encode($templateParams) : null;
        $message->status = self::STATUS_PENDING;
        $message->save();
        
        return $message;
    }

    /**
     * Mark message as sent
     * @param string $messageId
     * @param array $apiResponse
     * @return bool
     */
    public function markAsSent($messageId, $apiResponse = [])
    {
        $this->message_id = $messageId;
        $this->status = self::STATUS_SENT;
        $this->sent_at = date('Y-m-d H:i:s');
        if (!empty($apiResponse)) {
            $this->api_response = Json::encode($apiResponse);
        }
        return $this->save(false);
    }

    /**
     * Mark message as skipped
     * @param string $reason
     * @return bool
     */
    public function markAsSkipped($reason)
    {
        $this->status = self::STATUS_SKIPPED;
        $this->skip_reason = $reason;
        return $this->save(false);
    }

    /**
     * Mark message as failed
     * @param string $errorMessage
     * @return bool
     */
    public function markAsFailed($errorMessage)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->failed_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Update message status from webhook
     * @param string $status
     * @param array $webhookData
     * @return bool
     */
    public function updateFromWebhook($status, $webhookData = [])
    {
        $now = date('Y-m-d H:i:s');
        
        // Update status
        $this->status = $status;
        
        // Set appropriate timestamp
        switch ($status) {
            case self::STATUS_DELIVERED:
                $this->delivered_at = $now;
                break;
            case self::STATUS_READ:
                $this->read_at = $now;
                // If not already delivered, mark as delivered too
                if (empty($this->delivered_at)) {
                    $this->delivered_at = $now;
                }
                break;
            case self::STATUS_FAILED:
                $this->failed_at = $now;
                if (!empty($webhookData['error'])) {
                    $this->error_message = $webhookData['error'];
                }
                break;
        }
        
        // Store webhook update
        $existingUpdates = $this->getWebhookUpdatesArray();
        $existingUpdates[] = [
            'status' => $status,
            'timestamp' => $now,
            'data' => $webhookData
        ];
        $this->webhook_updates = Json::encode($existingUpdates);
        
        return $this->save(false);
    }

    /**
     * Update message status from webhook (alternative method name)
     * @param string $status
     * @param string $messageId
     * @param array $payload
     * @return bool
     */
    public function updateMessageStatus($status, $messageId = null, $payload = [])
    {
        $now = date('Y-m-d H:i:s');
        
        // Store the API message ID if provided
        if (!empty($messageId) && empty($this->message_id)) {
            $this->message_id = $messageId;
        }
        
        // Map webhook status to our status constants
        $mappedStatus = $this->mapWebhookStatus($status);
        
        // Update status field (using the field from base model)
        $this->status = $mappedStatus;
        
        // Set appropriate timestamps (using field names from base model, only if columns exist)
        switch ($mappedStatus) {
            case self::STATUS_SENT:
                if ($this->hasAttribute('sent_datetime') && empty($this->sent_datetime)) {
                    $this->sent_datetime = $now;
                } elseif ($this->hasAttribute('sent_at') && empty($this->sent_at)) {
                    $this->sent_at = $now;
                }
                break;
            case self::STATUS_DELIVERED:
                if ($this->hasAttribute('delivered_datetime')) {
                    $this->delivered_datetime = $now;
                } elseif ($this->hasAttribute('delivered_at')) {
                    $this->delivered_at = $now;
                }
                break;
            case self::STATUS_READ:
                if ($this->hasAttribute('read_datetime')) {
                    $this->read_datetime = $now;
                } elseif ($this->hasAttribute('read_at')) {
                    $this->read_at = $now;
                }
                // If not already delivered, mark as delivered too
                if ($this->hasAttribute('delivered_datetime') && empty($this->delivered_datetime)) {
                    $this->delivered_datetime = $now;
                } elseif ($this->hasAttribute('delivered_at') && empty($this->delivered_at)) {
                    $this->delivered_at = $now;
                }
                break;
            case self::STATUS_FAILED:
                // Extract error message from payload (only if column exists)
                if ($this->hasAttribute('error_message')) {
                    if (isset($payload['error_message'])) {
                        $this->error_message = $payload['error_message'];
                    } elseif (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0]['message'])) {
                        $this->error_message = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0]['message'];
                    }
                }
                break;
        }
        
        // Store webhook update in response_data field (only if column exists)
        if ($this->hasAttribute('response_data')) {
            $existingData = $this->getResponseDataArray();
            $existingData['webhook_updates'] = $existingData['webhook_updates'] ?? [];
            $existingData['webhook_updates'][] = [
                'status' => $status,
                'mapped_status' => $mappedStatus,
                'message_id' => $messageId,
                'timestamp' => $now,
                'payload_summary' => $this->summarizeWebhookPayload($payload)
            ];
            $this->response_data = Json::encode($existingData);
        } elseif ($this->hasAttribute('webhook_updates')) {
            // Alternative: use webhook_updates column if it exists
            $existingUpdates = $this->webhook_updates ? Json::decode($this->webhook_updates) : [];
            $existingUpdates[] = [
                'status' => $status,
                'mapped_status' => $mappedStatus,
                'message_id' => $messageId,
                'timestamp' => $now,
                'payload_summary' => $this->summarizeWebhookPayload($payload)
            ];
            $this->webhook_updates = Json::encode($existingUpdates);
        }
        
        return $this->save(false);
    }

    /**
     * Get response data as array
     * @return array
     */
    public function getResponseDataArray()
    {
        if (empty($this->response_data)) {
            return [];
        }
        return Json::decode($this->response_data);
    }

    /**
     * Map webhook status to internal status constants
     * @param string $webhookStatus
     * @return string
     */
    private function mapWebhookStatus($webhookStatus)
    {
        $statusMap = [
            'sent' => self::STATUS_SENT,
            'delivered' => self::STATUS_DELIVERED,
            'read' => self::STATUS_READ,
            'failed' => self::STATUS_FAILED,
            'error' => self::STATUS_FAILED,
            'pending' => self::STATUS_PENDING,
            'queued' => self::STATUS_PENDING,
        ];
        
        $status = strtolower($webhookStatus);
        return $statusMap[$status] ?? self::STATUS_PENDING;
    }

    /**
     * Summarize webhook payload for storage
     * @param array $payload
     * @return array
     */
    private function summarizeWebhookPayload($payload)
    {
        if (empty($payload)) {
            return [];
        }
        
        return [
            'has_entry' => isset($payload['entry']),
            'has_status' => isset($payload['status']),
            'object_type' => $payload['object'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'size_kb' => round(strlen(Json::encode($payload)) / 1024, 2)
        ];
    }

    /**
     * Get template params as array
     * @return array
     */
    public function getTemplateParamsArray()
    {
        if (empty($this->template_params)) {
            return [];
        }
        return Json::decode($this->template_params);
    }

    /**
     * Get API response as array
     * @return array
     */
    public function getApiResponseArray()
    {
        if (empty($this->api_response)) {
            return [];
        }
        return Json::decode($this->api_response);
    }

    /**
     * Get webhook updates as array
     * @return array
     */
    public function getWebhookUpdatesArray()
    {
        if (empty($this->webhook_updates)) {
            return [];
        }
        return Json::decode($this->webhook_updates);
    }

    /**
     * Get status badge class for UI
     * @return string
     */
    public function getStatusBadgeClass()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-secondary';
            case self::STATUS_SENT:
                return 'badge-info';
            case self::STATUS_DELIVERED:
                return 'badge-primary';
            case self::STATUS_READ:
                return 'badge-success';
            case self::STATUS_FAILED:
                return 'badge-danger';
            case self::STATUS_SKIPPED:
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get status icon for UI
     * @return string
     */
    public function getStatusIcon()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'fas fa-clock';
            case self::STATUS_SENT:
                return 'fas fa-paper-plane';
            case self::STATUS_DELIVERED:
                return 'fas fa-check';
            case self::STATUS_READ:
                return 'fas fa-check-double';
            case self::STATUS_FAILED:
                return 'fas fa-times';
            case self::STATUS_SKIPPED:
                return 'fas fa-forward';
            default:
                return 'fas fa-question';
        }
    }


























    /**
     * @inheritdoc
     * @return \app\modules\admin\models\AisensyBulkMessageLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\AisensyBulkMessageLogQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['campaign_id'] =  $this->campaign_id;
        
                $data['template_id'] =  $this->template_id;
        
                $data['contact_number'] =  $this->contact_number;
        
                $data['message_id'] =  $this->message_id;
        
                $data['status'] =  $this->status;
        
                $data['skip_reason'] =  $this->skip_reason;
        
                $data['sent_datetime'] =  $this->sent_datetime;
        
                $data['delivered_datetime'] =  $this->delivered_datetime;
        
                $data['read_datetime'] =  $this->read_datetime;
        
                $data['error_message'] =  $this->error_message;
        
                $data['response_data'] =  $this->response_data;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
            return $data;
}


}


