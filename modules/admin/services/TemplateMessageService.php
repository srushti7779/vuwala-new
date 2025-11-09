<?php

namespace app\modules\admin\services;

use Yii;
use app\modules\admin\models\AisensyTemplateSentLog;
use app\modules\admin\models\AisensyTemplates;
use yii\helpers\Json;

/**
 * TemplateMessageService handles template message sending with daily limits
 * Ensures one message per template per contact per day
 */
class TemplateMessageService
{
    // AiSensy Direct API endpoint
    private static $apiUrl = 'https://backend.aisensy.com/direct-apis/t1/messages';
    
    /**
     * Check if template can be sent to contact today
     * @param int $templateId
     * @param string $contactNumber
     * @return array ['can_send' => bool, 'reason' => string]
     */
    public static function canSendTemplate($templateId, $contactNumber)
    {
        // Format contact number
        $formattedContact = self::formatContactNumber($contactNumber);
        
        if (empty($formattedContact)) {
            return [
                'can_send' => false,
                'reason' => 'Invalid contact number format'
            ];
        }
        
        // Check if template exists
        $template = AisensyTemplates::findOne($templateId);
        if (!$template) {
            return [
                'can_send' => false,
                'reason' => 'Template not found'
            ];
        }
        
        if ($template->status != 1) {
            return [
                'can_send' => false,
                'reason' => 'Template is inactive'
            ];
        }
        
        // Check if already sent today  
        $todayDate = date('Y-m-d');
        $existingLog = AisensyTemplateSentLog::find()
            ->where([
                'template_id' => $templateId,
                'contact_number' => $formattedContact,
                'sent_date' => $todayDate,
                'status' => 1 // Active records only
            ])
            ->one();
        
        if ($existingLog) {
            return [
                'can_send' => false,
                'reason' => 'Template already sent to this contact today'
            ];
        }
        
        return [
            'can_send' => true,
            'reason' => 'Template can be sent'
        ];
    }
    
    /**
     * Send template message to single contact
     * @param int $templateId
     * @param string $contactNumber
     * @param array $templateParams
     * @return array
     */
    public static function sendTemplateMessage($templateId, $contactNumber, $templateParams = [])
    {
        try {
            // Check if can send
            $canSend = self::canSendTemplate($templateId, $contactNumber);
            if (!$canSend['can_send']) {
                return [
                    'success' => false,
                    'message' => $canSend['reason'],
                    'contact_number' => $contactNumber
                ];
            }
            
            $formattedContact = self::formatContactNumber($contactNumber);
            $template = AisensyTemplates::findOne($templateId);
            
            // Prepare API request
            $apiData = [
                'to' => $formattedContact,
                'type' => 'template',
                'template' => [
                    'name' => $template->external_id,
                    'language' => [
                        'code' => $template->language ?? 'en'
                    ]
                ]
            ];
            
            // Add template parameters if provided
            if (!empty($templateParams)) {
                $apiData['template']['components'] = self::buildTemplateComponents($templateParams);
            }
            
            // Send API request
            $response = self::callAisensyAPI($apiData);
            
            if ($response['success']) {
                // Log successful send
                $logData = [
                    'template_id' => $templateId,
                    'contact_number' => $formattedContact,
                    'sent_date' => date('Y-m-d'),
                    'sent_at' => date('Y-m-d H:i:s'),
                    'message_id' => $response['data']['message_id'] ?? null,
                    'message_status' => 'sent',
                    'api_response' => Json::encode($response['data']),
                    'template_params' => Json::encode($templateParams),
                    'status' => 1,
                    'create_user_id' => Yii::$app->user->id ?? null
                ];
                
                $sentLog = new AisensyTemplateSentLog();
                $sentLog->setAttributes($logData, false);
                $sentLog->save(false); // Skip validation for speed
                
                Yii::info("Template {$templateId} sent successfully to {$formattedContact}, Log ID: {$sentLog->id}", __METHOD__);
                
                return [
                    'success' => true,
                    'message' => 'Template message sent successfully',
                    'contact_number' => $formattedContact,
                    'message_id' => $response['data']['message_id'] ?? null,
                    'log_id' => $sentLog->id
                ];
            } else {
                Yii::error("Failed to send template {$templateId} to {$formattedContact}: " . $response['message'], __METHOD__);
                
                return [
                    'success' => false,
                    'message' => $response['message'],
                    'contact_number' => $formattedContact
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error("Template sending error: " . $e->getMessage(), __METHOD__);
            
            return [
                'success' => false,
                'message' => 'Error sending template: ' . $e->getMessage(),
                'contact_number' => $contactNumber
            ];
        }
    }
    
    /**
     * Send template to multiple contacts with duplicate prevention
     * @param int $templateId
     * @param array $contactNumbers
     * @param array $templateParams
     * @return array
     */
    public static function sendTemplateBulk($templateId, $contactNumbers, $templateParams = [])
    {
        $results = [
            'success_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'results' => []
        ];
        
        foreach ($contactNumbers as $contactNumber) {
            $result = self::sendTemplateMessage($templateId, $contactNumber, $templateParams);
            
            if ($result['success']) {
                $results['success_count']++;
            } elseif (strpos($result['message'], 'already sent') !== false) {
                $results['skipped_count']++;
            } else {
                $results['failed_count']++;
            }
            
            $results['results'][] = $result;
        }
        
        return $results;
    }
    
    /**
     * Get contacts that can receive template (haven't received it today)
     * @param int $templateId
     * @param array $contactNumbers
     * @return array
     */
    public static function getAvailableContacts($templateId, $contactNumbers)
    {
        $availableContacts = [];
        
        foreach ($contactNumbers as $contactNumber) {
            $canSend = self::canSendTemplate($templateId, $contactNumber);
            if ($canSend['can_send']) {
                $availableContacts[] = self::formatContactNumber($contactNumber);
            }
        }
        
        return $availableContacts;
    }
    
    /**
     * Get template sending statistics
     * @param int $templateId
     * @param string|null $date (Y-m-d format, null for all time)
     * @return array
     */
    public static function getTemplateStats($templateId, $date = null)
    {
        $query = AisensyTemplateSentLog::find()
            ->where(['template_id' => $templateId, 'status' => 1]);
        
        if ($date) {
            $query->andWhere(['sent_date' => $date]);
        }
        
        $totalSent = $query->count();
        
        // Count by message status
        $stats = [
            'sent_count' => $totalSent,
            'delivered_count' => 0,
            'read_count' => 0,
            'failed_count' => 0
        ];
        
        if ($totalSent > 0) {
            $statusCounts = $query
                ->select(['message_status', 'COUNT(*) as count'])
                ->groupBy('message_status')
                ->asArray()
                ->all();
            
            foreach ($statusCounts as $statusCount) {
                switch ($statusCount['message_status']) {
                    case 'delivered':
                        $stats['delivered_count'] = $statusCount['count'];
                        break;
                    case 'read':
                        $stats['read_count'] = $statusCount['count'];
                        break;
                    case 'failed':
                        $stats['failed_count'] = $statusCount['count'];
                        break;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Update message status from webhook
     * @param string $messageId
     * @param string $status
     * @param string $contactNumber
     * @return bool
     */
    public static function updateMessageStatus($messageId, $status, $contactNumber)
    {
        if (empty($messageId) || empty($status)) {
            return false;
        }
        
        $formattedContact = self::formatContactNumber($contactNumber);
        
        $sentLog = AisensyTemplateSentLog::find()
            ->where(['message_id' => $messageId])
            ->andWhere(['contact_number' => $formattedContact])
            ->one();
        
        if ($sentLog) {
            $sentLog->message_status = $status;
            $sentLog->update_user_id = Yii::$app->user->id ?? null;
            return $sentLog->save(false);
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
        
        // Remove country code prefix if present
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '91' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }
    
    /**
     * Build template components from parameters
     * @param array $templateParams
     * @return array
     */
    private static function buildTemplateComponents($templateParams)
    {
        $components = [];
        
        // Add header parameters
        if (isset($templateParams['header_text'])) {
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $templateParams['header_text']
                    ]
                ]
            ];
        }
        
        // Add media header
        if (isset($templateParams['header_media_id'])) {
            $mediaType = 'image'; // Default
            if (isset($templateParams['header_media_type'])) {
                $mediaType = $templateParams['header_media_type'];
            }
            
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => $mediaType,
                        $mediaType => [
                            'id' => $templateParams['header_media_id']
                        ]
                    ]
                ]
            ];
        }
        
        // Add body parameters
        $bodyParams = [];
        foreach ($templateParams as $key => $value) {
            if (strpos($key, 'body_param_') === 0) {
                $paramIndex = (int)str_replace('body_param_', '', $key);
                $bodyParams[$paramIndex] = [
                    'type' => 'text',
                    'text' => $value
                ];
            }
        }
        
        if (!empty($bodyParams)) {
            ksort($bodyParams); // Sort by parameter index
            $components[] = [
                'type' => 'body',
                'parameters' => array_values($bodyParams)
            ];
        }
        
        // Add button parameters
        $buttonParams = [];
        foreach ($templateParams as $key => $value) {
            if (strpos($key, 'button_param_') === 0) {
                $buttonIndex = (int)str_replace('button_param_', '', $key);
                $buttonParams[$buttonIndex] = [
                    'type' => 'text',
                    'text' => $value
                ];
            }
        }
        
        if (!empty($buttonParams)) {
            ksort($buttonParams);
            $components[] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => array_values($buttonParams)
            ];
        }
        
        return $components;
    }
    
    /**
     * Call AiSensy Direct API
     * @param array $data
     * @return array
     */
    private static function callAisensyAPI($data)
    {
        try {
            // Get API key from configuration
            $apiKey = Yii::$app->params['aisensy_api_key'] ?? 'your-api-key-here';
            
            // Check if API key is configured
            if ($apiKey === 'your-api-key-here' || empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => '❌ AiSensy API key not configured. Please update config/params.php with your actual API key.',
                ];
            }
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => self::$apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => Json::encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-AiSensy-Project-API-Pwd: ' . $apiKey
                ],
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            
            if ($error) {
                throw new \Exception('cURL Error: ' . $error);
            }
            
            $responseData = Json::decode($response, true);
            
            if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'http_code' => $httpCode
                ];
            } else {
                // Log detailed API error
                $errorDetails = [
                    'http_code' => $httpCode,
                    'response' => $responseData,
                    'request_data' => $data
                ];
                Yii::error("AiSensy API Request Failed: " . Json::encode($errorDetails), __METHOD__);
                
                $errorMessage = $responseData['message'] ?? "API request failed (HTTP {$httpCode})";
                
                // Check for common error scenarios
                if ($httpCode == 401) {
                    $errorMessage = "❌ Authentication Failed - Check your AiSensy API key";
                } elseif ($httpCode == 403) {
                    $errorMessage = "❌ Access Denied - API key may not have required permissions";
                } elseif ($httpCode == 400) {
                    $errorMessage = "❌ Bad Request - " . ($responseData['message'] ?? 'Invalid request data');
                } elseif ($httpCode == 500) {
                    $errorMessage = "❌ Server Error - AiSensy API is experiencing issues";
                }
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $responseData,
                    'http_code' => $httpCode
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error("AiSensy API Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }
}
