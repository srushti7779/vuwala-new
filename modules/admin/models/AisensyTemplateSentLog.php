<?php

namespace app\modules\admin\models;

use Yii;
use \app\modules\admin\models\base\AisensyTemplateSentLog as BaseAisensyTemplateSentLog;

/**
 * This is the model class for table "aisensy_template_sent_log".
 */
class AisensyTemplateSentLog extends BaseAisensyTemplateSentLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(),
	    [
            [['template_id', 'contact_number', 'sent_date'], 'required'],
            [['template_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['sent_date', 'sent_at', 'created_on', 'updated_on'], 'safe'],
            [['message_status', 'api_response', 'template_params'], 'string'],
            [['contact_number'], 'string', 'max' => 15],
            [['message_id'], 'string', 'max' => 100],
            [['template_id', 'contact_number', 'sent_date'], 'unique', 'targetAttribute' => ['template_id', 'contact_number', 'sent_date'], 'message' => 'The combination of Template ID, Contact Number and Sent Date has already been taken.']
        ]);
    }
    
    /**
     * Get relation to template
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(\app\modules\admin\models\AisensyTemplates::class, ['id' => 'template_id']);
    }
    
    /**
     * Check if template was sent to contact today
     * @param int $templateId
     * @param string $contactNumber
     * @return bool
     */
    public static function isTemplateSentToday($templateId, $contactNumber)
    {
        $formattedContact = self::formatContactNumber($contactNumber);
        $todayDate = date('Y-m-d');
        
        return self::find()
            ->where([
                'template_id' => $templateId,
                'contact_number' => $formattedContact,
                'sent_date' => $todayDate,
                'status' => 1
            ])
            ->exists();
    }
    
    /**
     * Record a sent template message
     * @param int $templateId
     * @param string $contactNumber
     * @param string|null $messageId
     * @param array $templateParams
     * @return self|false
     */
    public static function recordSent($templateId, $contactNumber, $messageId = null, $templateParams = [])
    {
        $formattedContact = self::formatContactNumber($contactNumber);
        
        $log = new self();
        $log->template_id = $templateId;
        $log->contact_number = $formattedContact;
        $log->sent_date = date('Y-m-d');
        $log->sent_at = date('Y-m-d H:i:s');
        $log->message_id = $messageId;
        $log->message_status = 'sent';
        $log->template_params = !empty($templateParams) ? \yii\helpers\Json::encode($templateParams) : null;
        $log->status = 1;
        $log->create_user_id = \Yii::$app->user->id ?? null;
        
        if ($log->save(false)) {
            return $log;
        }
        
        return false;
    }
    
    /**
     * Get contacts that can receive template (haven't received it today)
     * @param int $templateId
     * @param array $contactNumbers
     * @return array
     */
    public static function getAvailableContacts($templateId, $contactNumbers)
    {
        $todayDate = date('Y-m-d');
        $formattedContacts = [];
        
        foreach ($contactNumbers as $contact) {
            $formatted = self::formatContactNumber($contact);
            if ($formatted) {
                $formattedContacts[] = $formatted;
            }
        }
        
        if (empty($formattedContacts)) {
            return [];
        }
        
        // Get contacts that already received template today
        $sentToday = self::find()
            ->where([
                'template_id' => $templateId,
                'sent_date' => $todayDate,
                'status' => 1
            ])
            ->andWhere(['IN', 'contact_number', $formattedContacts])
            ->select('contact_number')
            ->column();
        
        // Return contacts that haven't received template today
        return array_diff($formattedContacts, $sentToday);
    }
    
    /**
     * Update message status from webhook
     * @param string $messageId
     * @param string $status
     * @param string|null $apiResponse
     * @return bool
     */
    public static function updateMessageStatus($messageId, $status, $apiResponse = null)
    {
        if (empty($messageId) || empty($status)) {
            return false;
        }
        
        $log = self::find()
            ->where(['message_id' => $messageId, 'status' => 1])
            ->one();
        
        if ($log) {
            $log->message_status = $status;
            if ($apiResponse) {
                $log->api_response = $apiResponse;
            }
            $log->update_user_id = \Yii::$app->user->id ?? null;
            return $log->save(false);
        }
        
        return false;
    }
    
    /**
     * Format contact number with country code
     * @param string $contactNumber
     * @return string|null
     */
    private static function formatContactNumber($contactNumber)
    {
        // Remove all non-digits
        $cleaned = preg_replace('/\D/', '', $contactNumber);
        
        if (strlen($cleaned) < 10) {
            return null; // Too short
        }
        
        // Add country code 91 if missing and number is 10 digits
        if (strlen($cleaned) == 10) {
            $cleaned = '91' . $cleaned;
        }
        
        // Remove leading 0 and add country code
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '91' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }
    
    /**
     * Get sending statistics for template
     * @param int $templateId
     * @param string|null $date
     * @return array
     */
    public static function getTemplateStatistics($templateId, $date = null)
    {
        $query = self::find()
            ->where(['template_id' => $templateId, 'status' => 1]);
        
        if ($date) {
            $query->andWhere(['sent_date' => $date]);
        }
        
        $total = $query->count();
        
        $stats = [
            'total_sent' => $total,
            'delivered' => 0,
            'read' => 0,
            'failed' => 0,
            'pending' => 0
        ];
        
        if ($total > 0) {
            $statusCounts = $query
                ->select(['message_status', 'COUNT(*) as count'])
                ->groupBy('message_status')
                ->asArray()
                ->all();
            
            foreach ($statusCounts as $statusCount) {
                $status = $statusCount['message_status'];
                $count = (int)$statusCount['count'];
                
                if (isset($stats[$status])) {
                    $stats[$status] = $count;
                } else {
                    $stats['pending'] += $count;
                }
            }
        }
        
        return $stats;
    }

}
