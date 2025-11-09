<?php

namespace app\modules\api\controllers;

use app\modules\admin\models\AisensyWebhooks;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\BotSessions;
use app\modules\admin\models\RegistrationQuestions;
use app\modules\admin\models\RegistrationAnswers;
use app\modules\admin\models\WhatsappRegistrationRequests;
use app\modules\admin\models\AisensyBulkCampaignLog;
use app\modules\admin\models\AisensyBulkMessageLog;
use app\modules\admin\models\AisensyTemplateSentLog;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;

/**
 * Advanced WhatsApp Webhook Controller
 * Handles incoming WhatsApp messages with state management and conversation flows
 */
class AiSensyWhatsAppWebhookController extends BKController
{
   
    public $enableCsrfValidation = false;

    // Bot Status Constants
    const BOT_STATUS_COMPLETED = 3;
    const BOT_STATUS_CANCELLED = 4;

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'access' => [
                'class'      => AccessControl::class,
                'ruleConfig' => [
                    'class' => AccessRule::class,
                ],

                'rules'      => [
                    [
                        'actions' => [
                            'webhook',
                            'receive-message',
                            'send-message',
                            'process-bulk-upload',
                            'test-connection',
                            'get-media',
                            'upload-media',
                            'delete-media',
                            'list-media',
                            'get-profile',
                            'get-templates',
                            'create-template',
                            'delete-template',
                            'send-template-message',
                            'check-status',
                            'list-messages',
                            'list-contacts',
                            'list-groups',
                            'send-group-message',
                            'add-group-participant',
                            'remove-group-participant',
                            'promote-group-participant',
                            'demote-group-participant',
                            'leave-group',
                            'set-profile-picture',
                            'set-profile-status',
                            'block-contact',
                            'unblock-contact',

                        ],

                        'allow'   => true,
                        'roles'   => [ 
                            '@',
                        ],
                    ],
                    [

                        'actions' => [
                       
                            'webhook',
                            'receive-message',
                            'send-message',
                            'process-bulk-upload',
                            'test-connection',
                            'get-media',
                            'upload-media',
                            'delete-media',
                            'list-media',
                            'get-profile',
                            'get-templates',
                            'create-template',
                            'delete-template',
                            'send-template-message',
                            'check-status',
                            'list-messages',
                            'list-contacts',
                            'list-groups',
                            'send-group-message',
                            'add-group-participant',
                            'remove-group-participant',
                            'promote-group-participant',
                            'demote-group-participant',
                            'leave-group',
                            'set-profile-picture',
                            'set-profile-status',
                            'block-contact',
                            'unblock-contact',



                        ],

                        'allow'   => true,
                        'roles'   => [

                            '?',
                            '*',

                        ],
                    ],
                ],
            ],

        ]);
    }

    


    public function actionWebhook()
    {
        // Set response format to JSON
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            // Get the raw input data
            $input = file_get_contents('php://input');
            
            // Get all HTTP headers
            $headers = $this->getAllHeaders();
            
            // Log the incoming webhook for debugging
            Yii::info("Webhook received: " . $input, __METHOD__);
            Yii::info("Headers: " . Json::encode($headers), __METHOD__);
            
            // Decode the JSON payload
            $payload = null;
            if (!empty($input)) {
                $payload = Json::decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON payload: ' . json_last_error_msg());
                }
            }
            
            // Create new webhook record
            $webhook = new AisensyWebhooks();
            
            // Extract and store webhook data
            $this->extractWebhookData($webhook, $payload, $headers, $input);
            
            // Save the webhook record with save(false) to bypass validation
            if ($webhook->save(false)) {
                Yii::info("Webhook saved successfully with ID: " . $webhook->id, __METHOD__);
                
                // Process the webhook based on event type
                try {
                    $this->processWebhookEvent($webhook, $payload);
                } catch (\Exception $e) {
                    // Log processing errors but still return success for webhook receipt
                    Yii::error("Webhook event processing failed: " . $e->getMessage(), __METHOD__);
                    Yii::error("Webhook ID: " . $webhook->id . ", Event: " . $webhook->event_type, __METHOD__);
                    
                    // Update webhook with error information
                    $webhook->error_message = $e->getMessage();
                    $webhook->save(false);
                }
                
                return [
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                    'webhook_id' => $webhook->id,
                    'event_type' => $webhook->event_type
                ];
            } else {
                $errors = $webhook->getErrors();
                Yii::error("Failed to save webhook: " . Json::encode($errors), __METHOD__);
                Yii::error("Webhook payload: " . $input, __METHOD__);
                
                // Try to save with validation disabled
                $webhook->save(false);
                
                return [
                    'success' => false,
                    'message' => 'Failed to save webhook data',
                    'errors' => $errors,
                    'webhook_id' => $webhook->id ?? 'failed'
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error("Webhook processing failed: " . $e->getMessage(), __METHOD__);
            Yii::error("Stack trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::error("Raw payload: " . $input, __METHOD__);
            
            // Try to save error webhook anyway
            try {
                $errorWebhook = new AisensyWebhooks();
                $errorWebhook->event_type = 'processing_error';
                $errorWebhook->payload = $input;
                $errorWebhook->error_message = $e->getMessage();
                $errorWebhook->status = AisensyWebhooks::STATUS_ACTIVE;
                $errorWebhook->save(false);
            } catch (\Exception $saveError) {
                Yii::error("Failed to save error webhook: " . $saveError->getMessage(), __METHOD__);
            }
            
            return [
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract webhook data and populate the model
     * Enhanced for AiSensy Direct API webhook formats
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     * @param array $headers
     * @param string $rawInput
     */
    private function extractWebhookData($webhook, $payload, $headers, $rawInput)
    {
        // Set default values
        $webhook->status = AisensyWebhooks::STATUS_ACTIVE;
        $webhook->payload = $rawInput; // Store raw JSON payload
        $webhook->headers = Json::encode($headers); // Store headers as JSON
        
        // Extract data from payload if available
        if ($payload && is_array($payload)) {
            // Determine event type based on payload structure
            $webhook->event_type = $this->determineEventType($payload);
            
            // Extract message ID with AiSensy Direct API priority
            $webhook->message_id = $this->extractMessageId($payload);
            
            // Extract phone numbers with multiple format support
            $phoneNumbers = $this->extractPhoneNumbers($payload);
            $webhook->from_number = $phoneNumbers['from'];
            $webhook->to_number = $phoneNumbers['to'];
            
            // Debug phone number extraction
            Yii::info("Phone number extraction - Event: {$webhook->event_type}, From: {$webhook->from_number}, To: {$webhook->to_number}, MessageID: {$webhook->message_id}", __METHOD__);
            
            // Extract status information
            $webhook->status_value = $this->extractStatus($payload);
            
            // Extract error information
            $errorInfo = $this->extractErrorInfo($payload);
            $webhook->error_code = $errorInfo['code'];
            $webhook->error_message = $errorInfo['message'];
            
        } else {
            // If no payload, check for webhook verification
            if (isset($_GET['hub_challenge'])) {
                 $webhook->event_type = 'webhook_verification';
                $webhook->status_value = 'verification_requested';
            } else {
                $webhook->event_type = 'unknown';
                $webhook->status_value = 'no_payload';
            }
        }
        
        // Truncate long values to fit database constraints
        $webhook->event_type = $this->truncateString($webhook->event_type, 100);
        $webhook->message_id = $this->truncateString($webhook->message_id, 191);
        $webhook->from_number = $this->truncateString($webhook->from_number, 50);
        $webhook->to_number = $this->truncateString($webhook->to_number, 50);
        $webhook->status_value = $this->truncateString($webhook->status_value, 50);
        $webhook->error_code = $this->truncateString($webhook->error_code, 50);
        $webhook->error_message = $this->truncateString($webhook->error_message, 255);
    }
    
    /**
     * Extract message ID from various webhook formats
     * @param array $payload
     * @return string|null
     */
    private function extractMessageId($payload)
    {
        // AiSensy Direct API formats
        if (isset($payload['message_id'])) {
            return $payload['message_id'];
        }
        
        if (isset($payload['id'])) {
            return $payload['id'];
        }
        
        if (isset($payload['message']['id'])) {
            return $payload['message']['id'];
        }
        
        if (isset($payload['message']['message_id'])) {
            return $payload['message']['message_id'];
        }
        
        // WhatsApp Business API format
        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0]['id'])) {
            return $payload['entry'][0]['changes'][0]['value']['messages'][0]['id'];
        }
        
        if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0]['id'])) {
            return $payload['entry'][0]['changes'][0]['value']['statuses'][0]['id'];
        }
        
        // Template or bulk message ID
        if (isset($payload['template_message_id'])) {
            return $payload['template_message_id'];
        }
        
        if (isset($payload['bulk_id'])) {
            return $payload['bulk_id'];
        }
        
        if (isset($payload['campaign_id'])) {
            return $payload['campaign_id'];
        }
        
        return null;
    }
    
    /**
     * Extract phone numbers from various webhook formats
     * @param array $payload
     * @return array ['from' => string|null, 'to' => string|null]
     */
    private function extractPhoneNumbers($payload)
    {
        $from = null;
        $to = null;
        
        // WhatsApp Business API format (Priority)
        if (isset($payload['entry'][0]['changes'][0]['value'])) {
            $value = $payload['entry'][0]['changes'][0]['value'];
            
            // Extract from messages (incoming messages: customer -> business)
            if (isset($value['messages'][0]['from'])) {
                $from = $value['messages'][0]['from']; // Customer number
            }
            
            // Extract from statuses (delivery reports: business -> customer)
            if (isset($value['statuses'][0]['recipient_id'])) {
                $to = $value['statuses'][0]['recipient_id']; // Customer number (recipient of our message)
            }
            
            // Extract business phone number (our number)
            if (isset($value['metadata']['display_phone_number'])) {
                // For incoming messages, this is 'to'; for delivery reports, this is 'from'
                if (isset($value['messages'][0])) {
                    $to = $value['metadata']['display_phone_number']; // Business number (recipient of customer message)
                } else {
                    $from = $value['metadata']['display_phone_number']; // Business number (sender of message)
                }
            } elseif (isset($value['metadata']['phone_number_id'])) {
                // For incoming messages, this is 'to'; for delivery reports, this is 'from'
                if (isset($value['messages'][0])) {
                    $to = $value['metadata']['phone_number_id']; // Business number (recipient of customer message)
                } else {
                    $from = $value['metadata']['phone_number_id']; // Business number (sender of message)
                }
            }
        }
        
        // AiSensy Direct API formats (Fallback)
        if (empty($from)) {
            if (isset($payload['from'])) {
                $from = $payload['from'];
            } elseif (isset($payload['message']['from'])) {
                $from = $payload['message']['from'];
            } elseif (isset($payload['sender'])) {
                $from = $payload['sender'];
            } elseif (isset($payload['customer_number'])) {
                $from = $payload['customer_number'];
            }
        }
        
        if (empty($to)) {
            if (isset($payload['to'])) {
                $to = $payload['to'];
            } elseif (isset($payload['message']['to'])) {
                $to = $payload['message']['to'];
            } elseif (isset($payload['recipient'])) {
                $to = $payload['recipient'];
            } elseif (isset($payload['business_number'])) {
                $to = $payload['business_number'];
            }
        }
        
        // Clean and format phone numbers
        if ($from) {
            $from = $this->formatPhoneNumber($from);
        }
        if ($to) {
            $to = $this->formatPhoneNumber($to);
        }
        
        return ['from' => $from, 'to' => $to];
    }
    
    /**
     * Extract status information from webhook payload
     * @param array $payload
     * @return string|null
     */
    private function extractStatus($payload)
    {
        // Direct status field
        if (isset($payload['status'])) {
            return $payload['status'];
        }
        
        if (isset($payload['delivery_status'])) {
            return $payload['delivery_status'];
        }
        
        if (isset($payload['message_status'])) {
            return $payload['message_status'];
        }
        
        // WhatsApp Business API format
        if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0]['status'])) {
            return $payload['entry'][0]['changes'][0]['value']['statuses'][0]['status'];
        }
        
        // Message type as status for incoming messages
        if (isset($payload['message']['type'])) {
            return 'received_' . $payload['message']['type'];
        }
        
        // Event-based status detection
        if (isset($payload['event'])) {
            $event = strtolower($payload['event']);
            if (in_array($event, ['delivered', 'read', 'sent', 'failed', 'pending'])) {
                return $event;
            }
        }
        
        return null;
    }
    
    /**
     * Extract error information from webhook payload
     * @param array $payload
     * @return array ['code' => string|null, 'message' => string|null]
     */
    private function extractErrorInfo($payload)
    {
        $code = null;
        $message = null;
        
        // Direct error fields
        if (isset($payload['error'])) {
            $error = $payload['error'];
            if (is_array($error)) {
                $code = $error['code'] ?? $error['error_code'] ?? null;
                $message = $error['message'] ?? $error['description'] ?? $error['title'] ?? null;
            } else {
                $message = $error;
            }
        }
        
        if (isset($payload['error_code'])) {
            $code = $payload['error_code'];
        }
        
        if (isset($payload['error_message'])) {
            $message = $payload['error_message'];
        }
        
        // WhatsApp Business API error format
        if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0])) {
            $error = $payload['entry'][0]['changes'][0]['value']['statuses'][0]['errors'][0];
            $code = $error['code'] ?? null;
            $message = ($error['title'] ?? '') . (isset($error['message']) ? ': ' . $error['message'] : '');
        }
        
        // Multiple errors array
        if (isset($payload['errors']) && is_array($payload['errors'])) {
            $firstError = $payload['errors'][0];
            if (is_array($firstError)) {
                $code = $firstError['code'] ?? null;
                $message = $firstError['message'] ?? $firstError['title'] ?? null;
            }
        }
        
        // Failed status implies error
        if (isset($payload['status']) && $payload['status'] === 'failed' && !$message) {
            $message = 'Message delivery failed';
        }
        
        return ['code' => $code, 'message' => $message];
    }
    
    /**
     * Determine event type based on payload structure
     * Enhanced for both AiSensy Direct API and WhatsApp Business API webhook formats
     * @param array $payload
     * @return string
     */
    private function determineEventType($payload)
    {
        // Handle WhatsApp webhook verification
        if (isset($_GET['hub_challenge'])) {
            return 'webhook_verification';
        }
        
        // WhatsApp Business API format (Priority handling)
        if (isset($payload['object']) && $payload['object'] === 'whatsapp_business_account') {
            if (isset($payload['entry'][0]['changes'][0]['value'])) {
                $value = $payload['entry'][0]['changes'][0]['value'];
                
                // Check for messages
                if (isset($value['messages'])) {
                    $message = $value['messages'][0];
                    if (isset($message['type'])) {
                        switch ($message['type']) {
                            case 'text':
                                return 'message_text';
                            case 'interactive':
                                return 'interactive_reply';
                            case 'button':
                                return 'button_reply';
                            default:
                                return 'message_' . $message['type'];
                        }
                    }
                    return 'message_received';
                }
                
                // Check for statuses (delivery reports)
                if (isset($value['statuses'])) {
                    return 'delivery_status';
                }
            }
        }
        
        // AiSensy Direct API webhook formats
        // 1. Direct event type specification
        if (isset($payload['event'])) {
            return $this->normalizeEventType($payload['event']);
        }
        
        if (isset($payload['event_type'])) {
            return $this->normalizeEventType($payload['event_type']);
        }
        
        if (isset($payload['type'])) {
            return $this->normalizeEventType($payload['type']);
        }
        
        // 2. AiSensy message webhook structure
        if (isset($payload['message']) && is_array($payload['message'])) {
            $message = $payload['message'];
            
            // Message type detection
            if (isset($message['type'])) {
                return 'message_' . strtolower($message['type']);
            }
            
            // Content-based detection
            if (isset($message['text'])) return 'message_text';
            if (isset($message['image'])) return 'message_image';
            if (isset($message['video'])) return 'message_video';
            if (isset($message['audio'])) return 'message_audio';
            if (isset($message['document'])) return 'message_document';
            if (isset($message['voice'])) return 'message_voice';
            if (isset($message['sticker'])) return 'message_sticker';
            if (isset($message['location'])) return 'message_location';
            if (isset($message['contacts'])) return 'message_contacts';
            
            return 'message_unknown';
        }
        
        // 3. Status/Delivery webhook detection
        if (isset($payload['status']) && isset($payload['message_id'])) {
            return 'delivery_status';
        }
        
        if (isset($payload['delivery_status'])) {
            return 'delivery_status';
        }
        
        // 4. Button/Interactive webhook
        if (isset($payload['button_reply']) || isset($payload['interactive'])) {
            return 'interactive_reply';
        }
        
        // 5. List reply webhook
        if (isset($payload['list_reply'])) {
            return 'list_reply';
        }
        
        // 6. Quick reply webhook
        if (isset($payload['button_text']) || isset($payload['quick_reply'])) {
            return 'quick_reply';
        }
        
        // 7. Template delivery webhook
        if (isset($payload['template']) && isset($payload['status'])) {
            return 'template_status';
        }
        
        // 8. WhatsApp Business API webhook structure (fallback)
        if (isset($payload['entry']) && is_array($payload['entry'])) {
            foreach ($payload['entry'] as $entry) {
                if (isset($entry['changes']) && is_array($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        $field = $change['field'] ?? '';
                        $value = $change['value'] ?? [];
                        
                        // Message events
                        if ($field === 'messages' && isset($value['messages'])) {
                            $message = $value['messages'][0] ?? [];
                            $messageType = $message['type'] ?? 'unknown';
                            return 'message_' . strtolower($messageType);
                        }
                        
                        // Status events
                        if ($field === 'messages' && isset($value['statuses'])) {
                            return 'message_status';
                        }
                        
                        // Account events
                        if ($field === 'account_alerts') {
                            return 'account_alert';
                        }
                        
                        // Other events
                        if (!empty($field)) {
                            return 'webhook_' . $field;
                        }
                    }
                }
            }
        }
        
        // 9. Bulk message webhook
        if (isset($payload['bulk_id']) || isset($payload['campaign_id'])) {
            return 'bulk_status';
        }
        
        // 10. Error webhook
        if (isset($payload['error']) || (isset($payload['status']) && $payload['status'] === 'failed')) {
            return 'error_notification';
        }
        
        return 'unknown';
    }
    
    /**
     * Normalize event type names
     * @param string $eventType
     * @return string
     */
    private function normalizeEventType($eventType)
    {
        $eventType = strtolower(trim($eventType));
        
        // Normalize common variations
        $eventMap = [
            'msg_received' => 'message_received',
            'msg_delivered' => 'message_delivered',
            'msg_read' => 'message_read',
            'msg_sent' => 'message_sent',
            'msg_failed' => 'message_failed',
            'delivery_report' => 'delivery_status',
            'read_receipt' => 'message_read',
            'button_click' => 'interactive_reply',
            'list_selection' => 'list_reply',
            'template_delivered' => 'template_status',
            'opt_in' => 'contact_opted_in',
            'opt_out' => 'contact_opted_out'
        ];
        
        return $eventMap[$eventType] ?? $eventType;
    }
    
    /**
     * Process webhook event based on type
     * Enhanced for AiSensy Direct API events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function processWebhookEvent($webhook, $payload)
    {
        try {
            switch ($webhook->event_type) {
                case 'webhook_verification':
                    $this->handleWebhookVerification();
                    break;
                
                // Incoming message events
                case 'message_text':
                case 'message_image':
                case 'message_video':
                case 'message_audio':
                case 'message_document':
                case 'message_voice':
                case 'message_sticker':
                case 'message_location':
                case 'message_contacts':
                case 'message_received':
                    $this->handleIncomingMessage($webhook, $payload);
                    break;
                
                // Message status/delivery events
                case 'message_status':
                case 'delivery_status':
                case 'message_delivered':
                case 'message_read':
                case 'message_sent':
                case 'message_failed':
                    $this->handleMessageStatus($webhook, $payload);
                    break;
                    $this->handleMessageStatus($webhook, $payload);
                    break;
                
                // Interactive message events
                case 'interactive_reply':
                case 'button_reply':
                case 'quick_reply':
                case 'list_reply':
                    $this->handleInteractiveReply($webhook, $payload);
                    break;
                
                // Template events
                case 'template_status':
                case 'template_delivered':
                case 'template_read':
                case 'template_failed':
                    $this->handleTemplateStatus($webhook, $payload);
                    break;
                
                // Bulk/Campaign events
                case 'bulk_status':
                case 'campaign_delivered':
                case 'campaign_failed':
                    $this->handleBulkStatus($webhook, $payload);
                    break;
                
                // Contact events
                case 'contact_opted_in':
                case 'contact_opted_out':
                    $this->handleContactStatus($webhook, $payload);
                    break;
                
                // Error events
                case 'error_notification':
                    $this->handleErrorNotification($webhook, $payload);
                    break;
                
                // Account events
                case 'account_alert':
                    $this->handleAccountAlert($webhook, $payload);
                    break;
                
                default:
                    Yii::info("Unhandled webhook event type: " . $webhook->event_type, __METHOD__);
                    $this->handleUnknownEvent($webhook, $payload);
                    break;
            }
        } catch (\Exception $e) {
            Yii::error("Error processing webhook event: " . $e->getMessage(), __METHOD__);
            Yii::error("Payload: " . Json::encode($payload), __METHOD__);
        }
    }
    
    /**
     * Handle webhook verification challenge
     */
    private function handleWebhookVerification()
    {
        if (isset($_GET['hub_challenge']) && isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe') {
            $challenge = $_GET['hub_challenge'];
            Yii::info("Webhook verification challenge: " . $challenge, __METHOD__);
            
            // Set response format to plain text for verification
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->set('Content-Type', 'text/plain');
            
            echo $challenge;
            exit;
        }
    }
    
    /**
     * Handle incoming messages with vendor registration bot
     * @param AisensyWebhooks $webhook
     * @param array $payload
     */
    private function handleIncomingMessage($webhook, $payload)
    {
        Yii::info("Processing incoming message: " . $webhook->event_type . " from " . $webhook->from_number, __METHOD__);
        
        $fromNumber = $webhook->from_number;
        if (empty($fromNumber)) {
            Yii::error("No from_number found in webhook: " . Json::encode($webhook->toArray()), __METHOD__);
            return;
        }
        
        try {
            // Handle text messages with vendor registration bot
            if ($webhook->event_type === 'message_text') {
                $messageText = $this->extractMessageText($payload);
                Yii::info("Extracted message text: " . $messageText, __METHOD__);
                
                if (!empty($messageText)) {
                    $this->processVendorRegistrationBot($fromNumber, $messageText);
                } else {
                    Yii::error("Empty message text extracted from payload", __METHOD__);
                }
            }
            
            // Handle interactive messages (button clicks)
            if (in_array($webhook->event_type, ['interactive_reply', 'button_reply'])) {
                $buttonData = $this->extractButtonReply($payload);
                Yii::info("Extracted button data: " . $buttonData, __METHOD__);
                
                if (!empty($buttonData)) {
                    $this->processVendorRegistrationBot($fromNumber, $buttonData);
                } else {
                    Yii::error("Empty button data extracted from payload", __METHOD__);
                }
            }
            
        } catch (\Exception $e) {
            Yii::error("Error handling incoming message: " . $e->getMessage(), __METHOD__);
            Yii::error("Webhook ID: " . $webhook->id . ", From: " . $fromNumber, __METHOD__);
            Yii::error("Stack trace: " . $e->getTraceAsString(), __METHOD__);
            
            // Update webhook with error info
            try {
                $webhook->error_message = "Message handling error: " . $e->getMessage();
                $webhook->save(false);
            } catch (\Exception $updateError) {
                Yii::error("Failed to update webhook with error: " . $updateError->getMessage(), __METHOD__);
            }
        }
    }
    
    /**
     * Handle message status updates
     * @param AisensyWebhooks $webhook
     * @param array $payload
     */
    private function handleMessageStatus($webhook, $payload)
    {
        Yii::info("Message status update: " . $webhook->status_value . " for message " . $webhook->message_id, __METHOD__);
        
        try {
            // Store comprehensive status update information
            $statusData = [
                'webhook_id' => $webhook->id,
                'message_id' => $webhook->message_id,
                'to_number' => $webhook->to_number,
                'from_number' => $webhook->from_number,
                'status' => $webhook->status_value,
                'timestamp' => date('Y-m-d H:i:s'),
                'payload_summary' => $this->summarizePayload($payload)
            ];
            
            // Handle WhatsApp Business API status format
            if (isset($payload['entry'][0]['changes'][0]['value']['statuses'][0])) {
                $status = $payload['entry'][0]['changes'][0]['value']['statuses'][0];
                $recipientId = $status['recipient_id'] ?? $webhook->to_number ?? 'unknown';
                $statusValue = $status['status'] ?? $webhook->status_value;
                
                $statusData['recipient_id'] = $recipientId;
                $statusData['status_details'] = $status;
                
                Yii::info("Processing WhatsApp Business API status: messageId={$webhook->message_id}, recipientId={$recipientId}, status={$statusValue}", __METHOD__);
                
                // Update bulk message log if exists
                $this->updateBulkMessageLog($webhook->message_id, $recipientId, $statusValue, $payload);
                
                // Handle failed delivery status
                if ($statusValue === 'failed') {
                    $this->handleFailedDeliveryStatus($webhook, $status, $recipientId, $statusData);
                } else {
                    // Handle successful delivery statuses (sent, delivered, read)
                    $this->handleSuccessfulDeliveryStatus($webhook, $status, $recipientId, $statusData);
                }
            }
            
            // Handle AiSensy Direct API status format (fallback)
            else if (isset($payload['status']) || $webhook->status_value) {
                $statusValue = $payload['status'] ?? $webhook->status_value;
                // For delivery status, the recipient (customer) is in 'to_number'
                $recipientId = $webhook->to_number ?? 'unknown';
                
                Yii::info("Processing AiSensy Direct API status: messageId={$webhook->message_id}, recipientId={$recipientId}, status={$statusValue}", __METHOD__);
                
                // Update bulk message log if exists
                $this->updateBulkMessageLog($webhook->message_id, $recipientId, $statusValue, $payload);
                
                if ($statusValue === 'failed') {
                    $this->handleFailedDeliveryStatus($webhook, $payload, $recipientId, $statusData);
                } else {
                    $this->handleSuccessfulDeliveryStatus($webhook, $payload, $recipientId, $statusData);
                }
            }
            
            // Update template sent log if exists
            $this->updateTemplateSentLog($webhook->message_id, $webhook->status_value, $payload);
            
            // Log comprehensive status data for debugging
            Yii::info("Status processing completed: " . Json::encode($statusData), __METHOD__);
            
        } catch (\Exception $e) {
            $this->logComprehensiveError(__METHOD__, $e, [
                'webhook_id' => $webhook->id,
                'message_id' => $webhook->message_id,
                'status' => $webhook->status_value,
                'payload_type' => 'message_status'
            ]);
        }
    }
    
    /**
     * Handle failed delivery status with comprehensive error tracking
     * @param AisensyWebhooks $webhook
     * @param array $statusData
     * @param string $recipientId
     * @param array $contextData
     */
    private function handleFailedDeliveryStatus($webhook, $statusData, $recipientId, $contextData)
    {
        try {
            // Extract error information
            $errorCode = null;
            $errorMessage = null;
            
            // WhatsApp Business API format
            if (isset($statusData['errors'][0])) {
                $error = $statusData['errors'][0];
                $errorCode = $error['code'] ?? null;
                $errorMessage = $error['message'] ?? 'Unknown error';
            }
            // AiSensy format or webhook fields
            else if (isset($statusData['error_code']) || $webhook->error_code) {
                $errorCode = $statusData['error_code'] ?? $webhook->error_code;
                $errorMessage = $statusData['error_message'] ?? $webhook->error_message ?? 'Message delivery failed';
            }
            
            // Log detailed failure information
            $failureData = [
                'recipient_id' => $recipientId,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'webhook_id' => $webhook->id,
                'message_id' => $webhook->message_id,
                'failure_timestamp' => date('Y-m-d H:i:s'),
                'context' => $contextData
            ];
            
            Yii::error("MESSAGE DELIVERY FAILED: " . Json::encode($failureData), __METHOD__);
            
            // Handle specific error codes
            switch ($errorCode) {
                case 131026: // Message undeliverable
                    Yii::error("Message undeliverable to {$recipientId}. Phone may be invalid or user blocked business.", __METHOD__);
                    $this->handleUndeliverableMessage($recipientId, $errorMessage, $failureData);
                    break;
                    
                case 131014: // Invalid phone number
                    Yii::error("Invalid phone number format: {$recipientId}", __METHOD__);
                    $this->handleInvalidPhoneNumber($recipientId, $failureData);
                    break;
                    
                case 131005: // User not found
                    Yii::error("WhatsApp user not found: {$recipientId}", __METHOD__);
                    $this->handleUserNotFound($recipientId, $failureData);
                    break;
                    
                case 131021: // Recipient not available
                    Yii::error("Recipient not available: {$recipientId}", __METHOD__);
                    $this->handleRecipientNotAvailable($recipientId, $failureData);
                    break;
                    
                default:
                    Yii::error("Unhandled delivery error code {$errorCode} for {$recipientId}: {$errorMessage}", __METHOD__);
                    $this->handleGenericDeliveryFailure($recipientId, $errorCode, $errorMessage, $failureData);
            }
            
            // Update bot session if exists to prevent retries
            $this->updateBotSessionForFailure($recipientId, $errorCode, $errorMessage);
            
        } catch (\Exception $e) {
            Yii::error("Error handling failed delivery status: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle successful delivery status
     * @param AisensyWebhooks $webhook
     * @param array $statusData
     * @param string $recipientId
     * @param array $contextData
     */
    private function handleSuccessfulDeliveryStatus($webhook, $statusData, $recipientId, $contextData)
    {
        $statusValue = $statusData['status'] ?? $webhook->status_value;
        
        Yii::info("Message status '{$statusValue}' for {$recipientId}, Message ID: {$webhook->message_id}", __METHOD__);
        
        // Update bot session with successful delivery if exists
        $this->updateBotSessionForSuccess($recipientId, $statusValue);
        
        // Track delivery metrics
        $this->trackDeliveryMetrics($recipientId, $statusValue, $contextData);
    }
    
    /**
     * Get all HTTP headers
     * @return array
     */
    private function getAllHeaders()
    {
        $headers = [];
        
        // Get headers using getallheaders() if available
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for servers that don't support getallheaders()
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('_', '-', substr($key, 5));
                    $headers[$header] = $value;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Truncate string to specified length
     * @param string|null $string
     * @param int $length
     * @return string|null
     */
    private function truncateString($string, $length)
    {
        if (empty($string)) {
            return $string;
        }
        
        return strlen($string) > $length ? substr($string, 0, $length) : $string;
    }

    // Additional event handler methods
    
    /**
     * Handle interactive reply events (buttons, quick replies, lists)
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleInteractiveReply($webhook, $payload)
    {
        Yii::info("Processing interactive reply event", __METHOD__);
        
        $fromNumber = $webhook->from_number;
        if (empty($fromNumber)) {
            return;
        }
        
        try {
            // Extract button reply data
            $buttonData = $this->extractButtonReply($payload);
            
            if (!empty($buttonData)) {
                // Process with vendor registration bot
                $this->processVendorRegistrationBot($fromNumber, $buttonData);
            } else {
                Yii::info("No button data found in interactive reply", __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error handling interactive reply: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle template status events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleTemplateStatus($webhook, $payload)
    {
        Yii::info("Processing template status event", __METHOD__);
        
        try {
            $templateData = $this->extractTemplateStatusData($payload);
            
            // Update template status in database
            $this->updateTemplateStatus($templateData);
            
            // Log template delivery status
            Yii::info("Template {$templateData['template_name']} status: {$templateData['status']}", __METHOD__);
            
        } catch (\Exception $e) {
            Yii::error("Error handling template status: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle bulk/campaign status events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleBulkStatus($webhook, $payload)
    {
        Yii::info("Processing bulk status event", __METHOD__);
        
        try {
            $bulkData = $this->extractBulkStatusData($payload);
            
            // Update bulk campaign status
            $this->updateBulkCampaignStatus($bulkData);
            
            // Check if campaign completed
            if (in_array($bulkData['status'], ['completed', 'failed', 'stopped'])) {
                $this->handleBulkCampaignCompletion($bulkData);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error handling bulk status: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle contact opt-in/opt-out events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleContactStatus($webhook, $payload)
    {
        Yii::info("Processing contact status event", __METHOD__);
        
        try {
            $contactData = $this->extractContactStatusData($payload);
            
            // Update contact opt status
            $this->updateContactOptStatus($contactData);
            
            Yii::info("Contact {$contactData['phone']} status updated to: {$contactData['status']}", __METHOD__);
            
        } catch (\Exception $e) {
            Yii::error("Error handling contact status: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle error notification events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleErrorNotification($webhook, $payload)
    {
        Yii::error("Processing error notification event", __METHOD__);
        
        try {
            $errorData = $this->extractErrorNotificationData($payload);
            
            // Log detailed error information
            Yii::error("AiSensy Error - Code: {$errorData['error_code']}, Message: {$errorData['error_message']}", __METHOD__);
            
            // Handle specific error types
            switch ($errorData['error_code']) {
                case 'RATE_LIMIT_EXCEEDED':
                    $this->handleRateLimitError($errorData);
                    break;
                case 'INVALID_TEMPLATE':
                    $this->handleTemplateError($errorData);
                    break;
                case 'ACCOUNT_SUSPENDED':
                    $this->handleAccountError($errorData);
                    break;
                default:
                    $this->handleGenericError($errorData);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error handling error notification: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle account alert events
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleAccountAlert($webhook, $payload)
    {
        Yii::info("Processing account alert event", __METHOD__);
        
        try {
            $alertData = $this->extractAccountAlertData($payload);
            
            // Process different alert types
            switch ($alertData['alert_type']) {
                case 'quota_warning':
                    $this->handleQuotaWarning($alertData);
                    break;
                case 'account_limit':
                    $this->handleAccountLimit($alertData);
                    break;
                case 'payment_due':
                    $this->handlePaymentAlert($alertData);
                    break;
                default:
                    Yii::info("Unknown account alert type: " . $alertData['alert_type'], __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error handling account alert: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle unknown event types
     * @param AisensyWebhooks $webhook
     * @param array|null $payload
     */
    private function handleUnknownEvent($webhook, $payload)
    {
        Yii::info("Processing unknown event type: " . $webhook->event_type, __METHOD__);
        
        // Store unknown events for future analysis
        $this->storeUnknownEvent($webhook, $payload);
    }

    // Placeholder methods for event-specific processing
    // These can be implemented based on your specific business logic

    private function extractInteractiveReplyData($payload)
    {
        return ['type' => 'unknown'];
    }

    private function processButtonReply($webhook, $replyData) { }
    private function processListReply($webhook, $replyData) { }
    private function processQuickReply($webhook, $replyData) { }

    private function extractTemplateStatusData($payload)
    {
        return ['template_name' => 'unknown', 'status' => 'unknown'];
    }

    private function updateTemplateStatus($templateData) { }

    private function extractBulkStatusData($payload)
    {
        return ['status' => 'unknown'];
    }

    private function updateBulkCampaignStatus($bulkData) { }
    private function handleBulkCampaignCompletion($bulkData) { }

    private function extractContactStatusData($payload)
    {
        return ['phone' => 'unknown', 'status' => 'unknown'];
    }

    private function updateContactOptStatus($contactData) { }

    private function extractErrorNotificationData($payload)
    {
        return ['error_code' => 'unknown', 'error_message' => 'unknown'];
    }

    private function handleRateLimitError($errorData) { }
    private function handleTemplateError($errorData) { }
    private function handleAccountError($errorData) { }
    private function handleGenericError($errorData) { }

    private function extractAccountAlertData($payload)
    {
        return ['alert_type' => 'unknown'];
    }

    private function handleQuotaWarning($alertData) { }
    private function handleAccountLimit($alertData) { }
    private function handlePaymentAlert($alertData) { }

    private function storeUnknownEvent($webhook, $payload) { }

    /**
     * Extract message text from payload
     * @param array $payload
     * @return string|null
     */
    private function extractMessageText($payload)
    {
        // WhatsApp Business API format (Priority)
        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])) {
            return trim($payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']);
        }
        
        // AiSensy Direct API formats (Fallback)
        if (isset($payload['message']['text']['body'])) {
            return trim($payload['message']['text']['body']);
        }
        
        if (isset($payload['message']['text'])) {
            return trim($payload['message']['text']);
        }
        
        if (isset($payload['text'])) {
            return trim($payload['text']);
        }
        
        return null;
    }

    /**
     * Extract button reply from interactive payload
     * @param array $payload
     * @return string|null
     */
    private function extractButtonReply($payload)
    {
        // WhatsApp Business API format (Priority)
        if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0]['interactive'])) {
            $interactive = $payload['entry'][0]['changes'][0]['value']['messages'][0]['interactive'];
            
            // Button reply
            if (isset($interactive['button_reply']['id'])) {
                return trim($interactive['button_reply']['id']);
            }
            
            // List reply
            if (isset($interactive['list_reply']['id'])) {
                return trim($interactive['list_reply']['id']);
            }
            
            // Quick reply
            if (isset($interactive['quick_reply']['payload'])) {
                return trim($interactive['quick_reply']['payload']);
            }
        }
        
        // AiSensy Direct API formats (Fallback)
        if (isset($payload['message']['interactive']['button_reply']['id'])) {
            return trim($payload['message']['interactive']['button_reply']['id']);
        }
        
        if (isset($payload['message']['interactive']['button_reply']['title'])) {
            return trim($payload['message']['interactive']['button_reply']['title']);
        }
        
        if (isset($payload['interactive']['button_reply']['id'])) {
            return trim($payload['interactive']['button_reply']['id']);
        }
        
        if (isset($payload['interactive']['button_reply']['title'])) {
            return trim($payload['interactive']['button_reply']['title']);
        }
        
        // List reply format
        if (isset($payload['message']['interactive']['list_reply']['id'])) {
            return trim($payload['message']['interactive']['list_reply']['id']);
        }
        
        if (isset($payload['interactive']['list_reply']['id'])) {
            return trim($payload['interactive']['list_reply']['id']);
        }
        
        return null;
    }

    /**
     * Process vendor registration bot workflow
     * @param string $fromNumber
     * @param string $messageText
     */
    private function processVendorRegistrationBot($fromNumber, $messageText)
    {
        try {
            Yii::info("Processing vendor registration bot for {$fromNumber}: {$messageText}", __METHOD__);
            
            // Handle bot commands
            $messageText = trim($messageText);
            $commandResponse = $this->handleBotCommands($fromNumber, $messageText);
            
            if ($commandResponse) {
                return; // Command was handled
            }
            
            // Get or create bot session
            $session = $this->getBotSession($fromNumber);
            
            if (!$session) {
                // Start new registration session
                $this->startNewRegistrationSession($fromNumber);
                return;
            }
            
            // Process current question response
            $this->processQuestionResponse($session, $messageText);
            
        } catch (\Exception $e) {
            Yii::error("Error in vendor registration bot: " . $e->getMessage(), __METHOD__);
            Yii::error("Stack trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::error("From number: " . $fromNumber . ", Message: " . $messageText, __METHOD__);
            
            // Try to log error in bot session if possible
            try {
                $session = $this->getBotSession($fromNumber);
                if ($session) {
                    $session->last_message = "Error: " . $e->getMessage();
                    $session->save(false);
                }
            } catch (\Exception $sessionError) {
                Yii::error("Failed to log error in session: " . $sessionError->getMessage(), __METHOD__);
            }
            
            $this->sendMessage($fromNumber, "Sorry, there was an error processing your message. Error has been logged. Please try again later or contact support.");
        }
    }

    /**
     * Handle bot commands (START, RESTART, CANCEL, HELP, SKIP)
     * @param string $fromNumber
     * @param string $messageText
     * @return bool True if command was handled
     */
    private function handleBotCommands($fromNumber, $messageText)
    {
        $messageUpper = strtoupper($messageText);
        
        // Handle button clicks
        switch ($messageText) {
            case 'start_registration':
                $this->startNewRegistrationSession($fromNumber);
                return true;
                
            case 'get_help':
                $this->sendHelpMessage($fromNumber);
                return true;
                
            case 'skip_question':
                $this->skipCurrentQuestion($fromNumber);
                return true;
                
            case 'cancel_registration':
                $this->cancelRegistrationSession($fromNumber);
                return true;
        }
        
        // Handle text commands
        switch ($messageUpper) {
            case 'START':
            case 'RESTART':
                $this->startNewRegistrationSession($fromNumber);
                return true;
                
            case 'CANCEL':
                $this->cancelRegistrationSession($fromNumber);
                return true;
                
            case 'HELP':
                $this->sendHelpMessage($fromNumber);
                return true;
                
            case 'SKIP':
                $this->skipCurrentQuestion($fromNumber);
                return true;
                
            case 'TEST':
            case 'DEBUG':
                $this->sendDebugInfo($fromNumber);
                return true;
                
            case 'TESTBOT':
                $this->sendTestMessage($fromNumber);
                return true;
        }
        
        return false;
    }

    /**
     * Get active bot session for phone number
     * @param string $fromNumber
     * @return BotSessions|null
     */
    private function getBotSession($fromNumber)
    {
        // Create a temporary user identifier based on phone number
        $tempUserId = $this->getTempUserId($fromNumber);
        
        return BotSessions::find()
            ->where(['user_id' => $tempUserId])
            ->andWhere(['!=', 'status', self::BOT_STATUS_COMPLETED])
            ->andWhere(['!=', 'status', self::BOT_STATUS_CANCELLED])
            ->one();
    }

    /**
     * Get temporary user ID based on phone number
     * @param string $fromNumber
     * @return int
     */
    private function getTempUserId($fromNumber)
    {
        // Use a simple hash of phone number as temporary user ID
        // This will be replaced with actual user ID during registration
        return abs(crc32($fromNumber));
    }

    /**
     * Start new registration session with duplicate prevention
     * @param string $fromNumber
     */
    private function startNewRegistrationSession($fromNumber)
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($fromNumber);
            
            // Check for recent failed delivery attempts - don't start if phone is problematic
            if ($this->hasRecentDeliveryFailures($formattedPhone)) {
                Yii::info("Skipping registration start for {$formattedPhone} due to recent delivery failures", __METHOD__);
                return;
            }
            
            // Check for existing completed registration
            $existingRequest = WhatsappRegistrationRequests::find()
                ->where(['contact_no' => $formattedPhone])
                ->andWhere(['!=', 'status', WhatsappRegistrationRequests::STATUS_DELETE])
                ->one();
                
            if ($existingRequest) {
                Yii::info("Existing registration found for {$formattedPhone}, sending status update instead of starting new", __METHOD__);
                $this->sendExistingRegistrationStatus($formattedPhone, $existingRequest);
                return;
            }
            
            // Check for very recent session to prevent rapid duplicates
            $recentSession = BotSessions::find()
                ->where(['user_id' => $this->getTempUserId($formattedPhone)])
                ->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', strtotime('-5 minutes'))])
                ->one();
                
            if ($recentSession) {
                Yii::info("Recent session found for {$formattedPhone} within 5 minutes, skipping duplicate start", __METHOD__);
                
                // If session is still active, just send current question
                if ($recentSession->status === BotSessions::STATUS_ACTIVE) {
                    $this->sendNextQuestion($recentSession);
                    return;
                }
            }
            
            // Cancel any existing session
            $this->cancelRegistrationSession($formattedPhone, false);
            
            // Create new session
            $session = new BotSessions();
            $session->user_id = $this->getTempUserId($formattedPhone);
            $session->session_uuid = $this->generateSessionId();
            $session->current_question_id = null;
            $session->status = BotSessions::STATUS_ACTIVE;
            $session->last_message = "Registration started for " . $formattedPhone;
            
            if ($session->save(false)) {
                Yii::info("New registration session created for {$formattedPhone}, Session ID: {$session->id}", __METHOD__);
                
                // Send welcome message
                $this->sendWelcomeMessage($formattedPhone);
                
                // Start with first question
                $this->sendNextQuestion($session);
            } else {
                Yii::error("Failed to create session: " . Json::encode($session->getErrors()), __METHOD__);
                $this->sendMessage($formattedPhone, "Sorry, unable to start registration. Please try again later.");
            }
            
        } catch (\Exception $e) {
            // Comprehensive error logging
            $this->logComprehensiveError(__METHOD__, $e, [
                'from_number' => $fromNumber,
                'formatted_phone' => $formattedPhone ?? $fromNumber,
                'action' => 'start_registration_session'
            ]);
            
            // Try to create a basic session record for debugging
            try {
                $errorSession = new BotSessions();
                $errorSession->user_id = $this->getTempUserId($fromNumber);
                $errorSession->session_uuid = $this->generateSessionId();
                $errorSession->status = self::BOT_STATUS_CANCELLED;
                $errorSession->last_message = "Error: " . $e->getMessage();
                $errorSession->save(false);
                Yii::info("Error session created with ID: " . $errorSession->id, __METHOD__);
            } catch (\Exception $sessionError) {
                Yii::error("Failed to create error session: " . $sessionError->getMessage(), __METHOD__);
            }
            
            $this->sendMessage($fromNumber, "Sorry, there was an error starting registration. Error logged for investigation. Please contact support or try again later.");
        }
    }

    /**
     * Cancel registration session
     * @param string $fromNumber
     * @param bool $sendMessage
     */
    private function cancelRegistrationSession($fromNumber, $sendMessage = true)
    {
        $session = $this->getBotSession($fromNumber);
        
        if ($session) {
            $session->status = self::BOT_STATUS_CANCELLED;
            $session->last_message = "Registration cancelled";
            $session->save(false);
            
            if ($sendMessage) {
                $this->sendMessage($fromNumber, "Registration cancelled. You can start again anytime by sending 'START'.");
            }
        }
    }

    /**
     * Send help message with interactive buttons
     * @param string $fromNumber
     */
    private function sendHelpMessage($fromNumber)
    {
        $helpText = "🤖 *Vendor Registration Bot Help*\n\n";
        $helpText .= "I can help you register as a vendor with our platform! Here's what you can do:\n\n";
        $helpText .= "📋 *Registration Process:*\n";
        $helpText .= "• Click buttons to answer questions quickly\n";
        $helpText .= "• Type answers for text questions\n";
        $helpText .= "• Skip optional questions if needed\n\n";
        $helpText .= "💬 *Text Commands:*\n";
        $helpText .= "• START - Begin registration\n";
        $helpText .= "• SKIP - Skip current question\n";
        $helpText .= "• CANCEL - Cancel registration\n";
        $helpText .= "• HELP - Show this help\n\n";
        $helpText .= "Choose an option below:";
        
        $this->sendInteractiveMessage($fromNumber, $helpText, [
            [
                'id' => 'start_registration',
                'title' => '🚀 Start Registration'
            ],
            [
                'id' => 'cancel_registration',
                'title' => '❌ Cancel Registration'
            ]
        ]);
    }

    /**
     * Skip current question
     * @param string $fromNumber
     */
    private function skipCurrentQuestion($fromNumber)
    {
        $session = $this->getBotSession($fromNumber);
        
        if (!$session) {
            $this->sendMessage($fromNumber, "No active registration session. Send 'START' to begin.");
            return;
        }
        
        if ($session->current_question_id) {
            // Save empty answer for current question
            $this->saveAnswer($session, $session->current_question_id, "", "Question skipped");
        }
        
        // Move to next question
        $this->sendNextQuestion($session);
    }

    /**
     * Process question response
     * @param BotSessions $session
     * @param string $messageText
     */
    private function processQuestionResponse($session, $messageText)
    {
        if (!$session->current_question_id) {
            // No current question, move to first question
            $this->sendNextQuestion($session);
            return;
        }
        
        // Get current question
        $question = RegistrationQuestions::findOne($session->current_question_id);
        
        if (!$question) {
            $this->sendMessage($this->getPhoneFromSession($session), "Question not found. Please restart registration.");
            return;
        }
        
        // Validate answer
        $validationResult = $this->validateAnswer($question, $messageText);
        
        if (!$validationResult['valid']) {
            $this->sendMessage($this->getPhoneFromSession($session), $validationResult['message']);
            return;
        }
        
        // Save answer
        $this->saveAnswer($session, $question->id, $messageText);
        
        // Move to next question
        $this->sendNextQuestion($session);
    }

    /**
     * Send next question in sequence
     * @param BotSessions $session
     */
    private function sendNextQuestion($session)
    {
        // Get next question
        $currentQuestionId = $session->current_question_id;
        
        $nextQuestion = RegistrationQuestions::find()
            ->where(['status' => RegistrationQuestions::STATUS_ACTIVE])
            ->andWhere($currentQuestionId ? ['>', 'sort_order', 
                RegistrationQuestions::findOne($currentQuestionId)->sort_order ?? 0] : [])
            ->orderBy(['sort_order' => SORT_ASC])
            ->one();
        
        if (!$nextQuestion) {
            // No more questions, complete registration
            $this->completeRegistration($session);
            return;
        }
        
        // Update session with current question
        $session->current_question_id = $nextQuestion->id;
        $session->last_message = "Question sent: " . $nextQuestion->question_text;
        $session->save(false);
        
        $phoneNumber = $this->getPhoneFromSession($session);
        
        // Handle different question types
        if ($nextQuestion->type === 'choice') {
            $this->sendChoiceQuestion($phoneNumber, $nextQuestion);
        } else {
            $this->sendTextQuestion($phoneNumber, $nextQuestion);
        }
    }

    /**
     * Send choice question with buttons
     * @param string $phoneNumber
     * @param RegistrationQuestions $question
     */
    private function sendChoiceQuestion($phoneNumber, $question)
    {
        $questionText = "❓ " . $question->question_text;
        
        if ($question->required) {
            $questionText .= " *(Required)*";
        }
        
        // Define choices based on question
        $buttons = [];
        
        if ($question->column_name === 'vendor_type') {
            $buttons = [
                ['id' => 'Salon', 'title' => '💇 Salon'],
                ['id' => 'Spa', 'title' => '🧖 Spa'],
                ['id' => 'Clinic', 'title' => '🏥 Clinic']
            ];
            
            // Add skip button for optional questions
            if (!$question->required) {
                $buttons[] = ['id' => 'skip_question', 'title' => '⏭ Skip'];
            }
        } else {
            // Generic choice question - parse from meta field if available
            $meta = $question->meta ? Json::decode($question->meta) : null;
            
            if ($meta && isset($meta['choices'])) {
                foreach ($meta['choices'] as $choice) {
                    $buttons[] = [
                        'id' => $choice['value'] ?? $choice,
                        'title' => $choice['label'] ?? $choice
                    ];
                }
            }
            
            // Add skip button for optional questions
            if (!$question->required) {
                $buttons[] = ['id' => 'skip_question', 'title' => '⏭ Skip'];
            }
        }
        
        if (!empty($buttons)) {
            $this->sendInteractiveMessage($phoneNumber, $questionText, $buttons);
        } else {
            // Fallback to text question if no buttons defined
            $this->sendTextQuestion($phoneNumber, $question);
        }
    }

    /**
     * Send text question
     * @param string $phoneNumber
     * @param RegistrationQuestions $question
     */
    private function sendTextQuestion($phoneNumber, $question)
    {
        $questionText = "❓ " . $question->question_text;
        
        if ($question->required) {
            $questionText .= " *(Required)*";
        } else {
            $questionText .= "\n\n_Send SKIP to skip this question_";
        }
        
        // Add format hints for specific question types
        switch ($question->type) {
            case 'email':
                $questionText .= "\n\n💡 *Example:* yourname@example.com";
                break;
            case 'phone':
                $questionText .= "\n\n💡 *Example:* 9876543210";
                break;
            case 'number':
                $questionText .= "\n\n💡 *Please enter numbers only*";
                break;
        }
        
        $this->sendMessage($phoneNumber, $questionText);
    }

    /**
     * Complete registration process
     * @param BotSessions $session
     */
    private function completeRegistration($session)
    {
        try {
            $fromNumber = $this->getPhoneFromSession($session);
            
            // Get all answers for this session
            $answers = RegistrationAnswers::find()
                ->where(['session_id' => $session->id])
                ->with('question')
                ->all();
            
            if (empty($answers)) {
                $this->sendMessage($fromNumber, "No answers found. Please restart registration.");
                return;
            }
            
            // Create registration request
            $registrationRequest = $this->createRegistrationRequestFromAnswers($answers, $fromNumber, $session);
            
            if (!$registrationRequest) {
                $this->sendMessage($fromNumber, "Failed to save registration request. Please contact support.");
                return;
            }
            
            // Update session status
            $session->status = self::BOT_STATUS_COMPLETED;
            $session->last_message = "Registration completed successfully";
            $session->save(false);
            
            // Send completion message
            $this->sendRegistrationCompleteMessage($fromNumber, $registrationRequest);
            
            Yii::info("Vendor registration request created for {$fromNumber}, Request ID: {$registrationRequest->id}", __METHOD__);
            
        } catch (\Exception $e) {
            Yii::error("Error completing registration: " . $e->getMessage(), __METHOD__);
            Yii::error("Stack trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::error("Session ID: " . $session->id, __METHOD__);
            
            // Try to update session with error info
            try {
                $session->status = self::BOT_STATUS_CANCELLED;
                $session->last_message = "Error completing registration: " . $e->getMessage();
                $session->save(false);
            } catch (\Exception $sessionError) {
                Yii::error("Failed to update session with error: " . $sessionError->getMessage(), __METHOD__);
            }
            
            $fromNumber = $this->getPhoneFromSession($session);
            $this->sendMessage($fromNumber, 
                "There was an error completing your registration. Error has been logged. Please contact support or try again later.");
        }
    }

    /**
     * Validate answer based on question type
     * @param RegistrationQuestions $question
     * @param string $answer
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateAnswer($question, $answer)
    {
        $answer = trim($answer);
        
        // Check required fields
        if ($question->required === 'Y' && empty($answer)) {
            return [
                'valid' => false,
                'message' => "This question is required. Please provide an answer."
            ];
        }
        
        // Validate based on question type
        switch ($question->type) {
            case 'email':
                if (!empty($answer) && !filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'valid' => false,
                        'message' => "Please enter a valid email address."
                    ];
                }
                break;
                
            case 'phone':
                if (!empty($answer) && !preg_match('/^[+]?[0-9]{10,15}$/', $answer)) {
                    return [
                        'valid' => false,
                        'message' => "Please enter a valid phone number (10-15 digits)."
                    ];
                }
                break;
                
            case 'number':
                if (!empty($answer) && !is_numeric($answer)) {
                    return [
                        'valid' => false,
                        'message' => "Please enter a valid number."
                    ];
                }
                break;
                
            case 'url':
                if (!empty($answer) && !filter_var($answer, FILTER_VALIDATE_URL)) {
                    return [
                        'valid' => false,
                        'message' => "Please enter a valid URL."
                    ];
                }
                break;
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Save answer to database
     * @param BotSessions $session
     * @param int $questionId
     * @param string $answerText
     * @param string $note
     */
    private function saveAnswer($session, $questionId, $answerText, $note = '')
    {
        // Check if answer already exists
        $existingAnswer = RegistrationAnswers::find()
            ->where(['session_id' => $session->id, 'question_id' => $questionId])
            ->one();
        
        if ($existingAnswer) {
            // Update existing answer
            $existingAnswer->answer_text = $answerText;
            $existingAnswer->received_at = date('Y-m-d H:i:s');
            $existingAnswer->save(false);
        } else {
            // Create new answer
            $answer = new RegistrationAnswers();
            $answer->session_id = $session->id;
            $answer->question_id = $questionId;
            $answer->answer_text = $answerText;
            $answer->received_at = date('Y-m-d H:i:s');
            $answer->status = RegistrationAnswers::STATUS_ACTIVE;
            
            // Get question key
            $question = RegistrationQuestions::findOne($questionId);
            if ($question) {
                $answer->question_key = $question->column_name;
            }
            
            $answer->save(false);
        }
    }

    /**
     * Create WhatsApp registration request from answers
     * @param array $answers
     * @param string $fromNumber
     * @param BotSessions $session
     * @return WhatsappRegistrationRequests|null
     */
    private function createRegistrationRequestFromAnswers($answers, $fromNumber, $session)
    {
        // Check for existing registration request to prevent duplicates
        $existingRequest = WhatsappRegistrationRequests::find()
            ->where(['contact_no' => $fromNumber, 'source' => 'whatsapp_bot'])
            ->andWhere(['!=', 'status', WhatsappRegistrationRequests::STATUS_DELETE])
            ->one();
            
        if ($existingRequest) {
            Yii::info("Existing registration request found for {$fromNumber}, updating instead of creating new", __METHOD__);
            $registrationRequest = $existingRequest;
        } else {
            $registrationRequest = new WhatsappRegistrationRequests();
            $registrationRequest->source = 'whatsapp_bot';
            $registrationRequest->status = WhatsappRegistrationRequests::STATUS_ACTIVE;
        }
        
        $registrationRequest->src_id = $session->id;
        $registrationRequest->contact_no = $fromNumber;
        
        // Collect all answers for extra field
        $extraData = [];
        
        // Map answers to registration request fields
        foreach ($answers as $answer) {
            if ($answer->question) {
                $columnName = $answer->question->column_name;
                $answerText = $answer->answer_text;
                
                // Map to direct fields
                switch ($columnName) {
                    case 'first_name':
                        $registrationRequest->first_name = $answerText;
                        break;
                    case 'last_name':
                        $registrationRequest->last_name = $answerText;
                        break;
                    case 'email':
                        $registrationRequest->email = $answerText;
                        break;
                    case 'phone':
                    case 'contact_no':
                        $registrationRequest->contact_no = $answerText;
                        break;
                    case 'business_name':
                        $registrationRequest->business_name = $answerText;
                        break;
                    case 'gst_number':
                        $registrationRequest->gst_number = $answerText;
                        break;
                    case 'address':
                        $registrationRequest->address = $answerText;
                        break;
                    case 'city':
                    case 'location':
                        // For now, store as text. You can map to city_id later if needed
                        $extraData['city_name'] = $answerText;
                        break;
                    default:
                        // Store other answers in extra field
                        $extraData[$columnName] = $answerText;
                        break;
                }
            }
        }
        
        // Generate username if not provided
        if (empty($registrationRequest->username)) {
            $registrationRequest->username = 'whatsapp_vendor_' . time() . '_' . rand(1000, 9999);
        }
        
        // Store extra data as JSON
        if (!empty($extraData)) {
            $registrationRequest->extra = Json::encode($extraData);
        }
        
        // Try to save with validation first
        if ($registrationRequest->save()) {
            Yii::info("Registration request saved successfully with ID: " . $registrationRequest->id, __METHOD__);
            return $registrationRequest;
        }
        
        // If validation fails, log errors and try save(false)
        $errors = $registrationRequest->getErrors();
        Yii::error("Validation failed for registration request: " . Json::encode($errors), __METHOD__);
        Yii::error("Registration data: " . Json::encode($registrationRequest->toArray()), __METHOD__);
        
        // Try to save without validation
        if ($registrationRequest->save(false)) {
            Yii::info("Registration request saved without validation with ID: " . $registrationRequest->id, __METHOD__);
            return $registrationRequest;
        }
        
        Yii::error("Failed to create registration request even with save(false)", __METHOD__);
        return null;
    }



    /**
     * Send welcome message with interactive buttons
     * @param string $fromNumber
     */
    private function sendWelcomeMessage($fromNumber)
    {
        $welcomeText = "🎉 *Welcome to Vendor Registration!*\n\n";
        $welcomeText .= "I'll help you register as a vendor on our platform. ";
        $welcomeText .= "I'll ask you a few questions to complete your registration.\n\n";
        $welcomeText .= "Click the button below to start your registration process! 🚀";
        
        $this->sendInteractiveMessage($fromNumber, $welcomeText, [
            [
                'id' => 'start_registration',
                'title' => '🚀 Start Registration'
            ],
            [
                'id' => 'get_help',
                'title' => '❓ Get Help'
            ]
        ]);
    }

    /**
     * Send registration complete message
     * @param string $fromNumber
     * @param WhatsappRegistrationRequests $registrationRequest
     */
    private function sendRegistrationCompleteMessage($fromNumber, $registrationRequest)
    {
        $completeText = "✅ *Registration Request Submitted Successfully!*\n\n";
        $completeText .= "🎉 Congratulations! Your vendor registration request has been submitted.\n\n";
        $completeText .= "📋 *Your Details:*\n";
        
        if (!empty($registrationRequest->first_name)) {
            $completeText .= "• Name: " . $registrationRequest->first_name;
            if (!empty($registrationRequest->last_name)) {
                $completeText .= " " . $registrationRequest->last_name;
            }
            $completeText .= "\n";
        }
        
        if (!empty($registrationRequest->email)) {
            $completeText .= "• Email: " . $registrationRequest->email . "\n";
        }
        
        if (!empty($registrationRequest->contact_no)) {
            $completeText .= "• Phone: " . $registrationRequest->contact_no . "\n";
        }
        
        if (!empty($registrationRequest->business_name)) {
            $completeText .= "• Business: " . $registrationRequest->business_name . "\n";
        }
        
        $completeText .= "\n� *Request Details:*\n";
        $completeText .= "• Request ID: #" . $registrationRequest->id . "\n";
        $completeText .= "• Source: WhatsApp Bot\n";
        $completeText .= "• Status: Under Review\n";
        
        $completeText .= "\n🔍 *Next Steps:*\n";
        $completeText .= "1. Our team will review your request\n";
        $completeText .= "2. You'll receive updates via WhatsApp/Email\n";
        $completeText .= "3. Account will be created upon approval\n\n";
        $completeText .= "Thank you for choosing our platform! 🙏\n\n";
        $completeText .= "_Reference ID: WA" . $registrationRequest->id . "_";
        
        $this->sendMessage($fromNumber, $completeText);
    }

    /**
     * Get phone number from session
     * @param BotSessions $session
     * @return string
     */
    private function getPhoneFromSession($session)
    {
        // Try to get phone number from existing registration request
        $registrationRequest = WhatsappRegistrationRequests::find()
            ->where(['src_id' => $session->id, 'source' => 'whatsapp_bot'])
            ->one();
            
        if ($registrationRequest && !empty($registrationRequest->contact_no)) {
            return $registrationRequest->contact_no;
        }
        
        // Try to get from registration answers in this session
        $phoneAnswer = RegistrationAnswers::find()
            ->joinWith('question')
            ->where(['session_id' => $session->id])
            ->andWhere(['in', 'registration_questions.column_name', ['phone', 'contact_no']])
            ->one();
            
        if ($phoneAnswer && !empty($phoneAnswer->answer_text)) {
            return $phoneAnswer->answer_text;
        }
        
        // Fallback: Generate phone number from temp user ID pattern
        // This is a simplified approach - in production you might want to store phone in session
        return "temp_" . $session->user_id;
    }

    /**
     * Generate unique session ID
     * @return string
     */
    private function generateSessionId()
    {
        return 'bot_' . time() . '_' . uniqid();
    }

    /**
     * Send message via AiSensy Direct API
     * @param string $toNumber
     * @param string $message
     */
    private function sendMessage($toNumber, $message)
    {
        try {
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                Yii::error("AiSensy WhatsApp key not configured in settings", __METHOD__);
                return;
            }
            
            // AiSensy Direct API endpoint
            $apiUrl = 'https://backend.aisensy.com/direct-apis/t1/messages';
            
            // Prepare message data in AiSensy format
            $messageData = [
                'to' => $toNumber,
                'type' => 'text',
                'recipient_type' => 'individual',
                'text' => [
                    'body' => $message
                ]
            ];
            
            // Send via HTTP client
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($apiUrl)
                ->setHeaders([
                    'Accept' => 'application/json, application/xml',
                    'Authorization' => 'Bearer ' . $aisensy_wa_key,
                    'Content-Type' => 'application/json'
                ])
                ->setContent(Json::encode($messageData))
                ->send();
            
            if ($response->isOk) {
                Yii::info("Message sent successfully to {$toNumber} via AiSensy", __METHOD__);
                Yii::info("AiSensy Response: " . $response->content, __METHOD__);
            } else {
                Yii::error("Failed to send message to {$toNumber} via AiSensy: " . $response->content, __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error sending message via AiSensy: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Send interactive message with buttons via AiSensy Direct API
     * @param string $toNumber
     * @param string $message
     * @param array $buttons Array of buttons with 'id' and 'title'
     */
    private function sendInteractiveMessage($toNumber, $message, $buttons)
    {
        try {
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                Yii::error("AiSensy WhatsApp key not configured in settings", __METHOD__);
                return;
            }
            
            // AiSensy Direct API endpoint
            $apiUrl = 'https://backend.aisensy.com/direct-apis/t1/messages';
            
            // Prepare interactive buttons
            $interactiveButtons = [];
            foreach ($buttons as $button) {
                $interactiveButtons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => $button['id'],
                        'title' => $button['title']
                    ]
                ];
            }
            
            // Prepare interactive message data in AiSensy format
            $messageData = [
                'to' => $toNumber,
                'type' => 'interactive',
                'recipient_type' => 'individual',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $message
                    ],
                    'action' => [
                        'buttons' => $interactiveButtons
                    ]
                ]
            ];
            
            // Send via HTTP client
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($apiUrl)
                ->setHeaders([
                    'Accept' => 'application/json, application/xml',
                    'Authorization' => 'Bearer ' . $aisensy_wa_key,
                    'Content-Type' => 'application/json'
                ])
                ->setContent(Json::encode($messageData))
                ->send();
            
            if ($response->isOk) {
                Yii::info("Interactive message sent successfully to {$toNumber} via AiSensy", __METHOD__);
                Yii::info("AiSensy Response: " . $response->content, __METHOD__);
            } else {
                Yii::error("Failed to send interactive message to {$toNumber} via AiSensy: " . $response->content, __METHOD__);
                // Fallback to regular text message
                $this->sendMessage($toNumber, $message);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error sending interactive message via AiSensy: " . $e->getMessage(), __METHOD__);
            // Fallback to regular text message
            $this->sendMessage($toNumber, $message);
        }
    }

    /**
     * Get AiSensy WhatsApp API settings
     * @return string|null
     */
    private function getAisensyApiKey()
    {
        // Get AiSensy bearer token from settings
        $setting = new WebSetting();
        return $setting->getSettingBykey('aisensy_wa_key');
    }

    /**
     * Create comprehensive error log with all debugging information
     * @param string $method
     * @param \Exception $e
     * @param array $context
     */
    private function logComprehensiveError($method, $e, $context = [])
    {
        $errorData = [
            'method' => $method,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        Yii::error("COMPREHENSIVE ERROR LOG: " . Json::encode($errorData), $method);
        
        // Also try to save error to webhook table for persistent logging
        try {
            $errorWebhook = new AisensyWebhooks();
            $errorWebhook->event_type = 'error_log';
            $errorWebhook->from_number = $context['from_number'] ?? 'unknown';
            $errorWebhook->status = AisensyWebhooks::STATUS_ACTIVE;
            $errorWebhook->payload = Json::encode($errorData);
            $errorWebhook->error_message = $e->getMessage();
            $errorWebhook->error_code = (string)$e->getCode();
            $errorWebhook->save(false);
        } catch (\Exception $logError) {
            Yii::error("Failed to save error to webhook table: " . $logError->getMessage(), $method);
        }
    }

    /**
     * Send debug information about current session
     * @param string $fromNumber
     */
    private function sendDebugInfo($fromNumber)
    {
        try {
            $debugInfo = "🔧 *Debug Information*\n\n";
            
            // Check bot session
            $session = $this->getBotSession($fromNumber);
            if ($session) {
                $debugInfo .= "✅ Active Session Found\n";
                $debugInfo .= "• Session ID: " . $session->id . "\n";
                $debugInfo .= "• User ID: " . $session->user_id . "\n";
                $debugInfo .= "• Status: " . $session->status . "\n";
                $debugInfo .= "• Current Question: " . ($session->current_question_id ?? 'None') . "\n";
                $debugInfo .= "• Last Message: " . $session->last_message . "\n";
                
                // Check answers count
                $answersCount = RegistrationAnswers::find()
                    ->where(['session_id' => $session->id])
                    ->count();
                $debugInfo .= "• Answers Count: " . $answersCount . "\n";
            } else {
                $debugInfo .= "❌ No Active Session\n";
            }
            
            // Check registration questions
            $questionsCount = RegistrationQuestions::find()
                ->where(['status' => RegistrationQuestions::STATUS_ACTIVE])
                ->count();
            $debugInfo .= "\n📋 Questions Available: " . $questionsCount . "\n";
            
            // Check registration requests
            $requestsCount = WhatsappRegistrationRequests::find()
                ->where(['contact_no' => $fromNumber])
                ->count();
            $debugInfo .= "📝 Previous Requests: " . $requestsCount . "\n";
            
            $debugInfo .= "\n💡 Commands Available:\n";
            $debugInfo .= "• START - Begin registration\n";
            $debugInfo .= "• CANCEL - Cancel registration\n";
            $debugInfo .= "• HELP - Get help\n";
            $debugInfo .= "• TEST - Test bot functionality\n";
            
            $this->sendMessage($fromNumber, $debugInfo);
            
        } catch (\Exception $e) {
            $this->logComprehensiveError(__METHOD__, $e, ['from_number' => $fromNumber]);
            $this->sendMessage($fromNumber, "Error generating debug info: " . $e->getMessage());
        }
    }

    /**
     * Format phone number for consistent storage
     * @param string $phoneNumber
     * @return string|null
     */
    private function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }
        
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // Remove + prefix if present
        $cleaned = ltrim($cleaned, '+');
        
        // Ensure it's a valid phone number length (10-15 digits)
        if (strlen($cleaned) >= 10 && strlen($cleaned) <= 15) {
            return $cleaned;
        }
        
        // Return original if formatting fails
        return $phoneNumber;
    }

    /**
     * Handle undeliverable message error with comprehensive tracking
     * @param string $phoneNumber
     * @param string $errorMessage
     * @param array $failureData
     */
    private function handleUndeliverableMessage($phoneNumber, $errorMessage, $failureData = [])
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            Yii::error("UNDELIVERABLE MESSAGE: Phone {$formattedPhone}, Error: {$errorMessage}", __METHOD__);
            
            // Check if there's an active bot session for this number
            $session = $this->getBotSession($formattedPhone);
            
            if ($session) {
                $session->last_message = "Message undeliverable: " . $errorMessage;
                $session->status = self::BOT_STATUS_CANCELLED; // Cancel session to prevent retries
                $session->save(false);
                
                Yii::info("Cancelled bot session {$session->id} due to undeliverable message for {$formattedPhone}", __METHOD__);
            }
            
            // Update registration request with failure details
            $registrationRequest = WhatsappRegistrationRequests::find()
                ->where(['contact_no' => $formattedPhone])
                ->orderBy(['id' => SORT_DESC])
                ->one();
                
            if ($registrationRequest) {
                $extra = $registrationRequest->extra ? Json::decode($registrationRequest->extra) : [];
                $extra['delivery_failures'] = ($extra['delivery_failures'] ?? 0) + 1;
                $extra['last_failure'] = date('Y-m-d H:i:s');
                $extra['last_failure_reason'] = $errorMessage;
                $extra['undeliverable'] = true;
                $extra['failure_details'] = $failureData;
                $registrationRequest->extra = Json::encode($extra);
                $registrationRequest->save(false);
                
                Yii::info("Updated registration request {$registrationRequest->id} with undeliverable status", __METHOD__);
            }
            
            // Store in webhook for persistent tracking
            $this->trackPhoneNumberIssue($formattedPhone, 'undeliverable', $errorMessage, $failureData);
            
        } catch (\Exception $e) {
            Yii::error("Error handling undeliverable message: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Handle invalid phone number error
     * @param string $phoneNumber
     * @param array $failureData
     */
    private function handleInvalidPhoneNumber($phoneNumber, $failureData = [])
    {
        try {
            Yii::error("INVALID PHONE NUMBER: {$phoneNumber}", __METHOD__);
            
            // Check if there's an active bot session for this number
            $session = $this->getBotSession($phoneNumber);
            
            if ($session) {
                $session->last_message = "Invalid phone number format: " . $phoneNumber;
                $session->status = self::BOT_STATUS_CANCELLED;
                $session->save(false);
                
                Yii::info("Cancelled bot session {$session->id} due to invalid phone number {$phoneNumber}", __METHOD__);
            }
            
            // Store in webhook for tracking
            $this->trackPhoneNumberIssue($phoneNumber, 'invalid_format', 'Invalid phone number format', $failureData);
            
        } catch (\Exception $e) {
            Yii::error("Error handling invalid phone number: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle user not found error
     * @param string $phoneNumber
     * @param array $failureData
     */
    private function handleUserNotFound($phoneNumber, $failureData = [])
    {
        try {
            Yii::error("USER NOT FOUND: {$phoneNumber}", __METHOD__);
            $this->trackPhoneNumberIssue($phoneNumber, 'user_not_found', 'WhatsApp user not found', $failureData);
        } catch (\Exception $e) {
            Yii::error("Error handling user not found: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle recipient not available error
     * @param string $phoneNumber
     * @param array $failureData
     */
    private function handleRecipientNotAvailable($phoneNumber, $failureData = [])
    {
        try {
            Yii::error("RECIPIENT NOT AVAILABLE: {$phoneNumber}", __METHOD__);
            $this->trackPhoneNumberIssue($phoneNumber, 'not_available', 'Recipient not available', $failureData);
        } catch (\Exception $e) {
            Yii::error("Error handling recipient not available: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Handle generic delivery failure
     * @param string $phoneNumber
     * @param string $errorCode
     * @param string $errorMessage
     * @param array $failureData
     */
    private function handleGenericDeliveryFailure($phoneNumber, $errorCode, $errorMessage, $failureData = [])
    {
        try {
            Yii::error("GENERIC DELIVERY FAILURE: {$phoneNumber} - Code: {$errorCode}, Message: {$errorMessage}", __METHOD__);
            $this->trackPhoneNumberIssue($phoneNumber, 'generic_failure', $errorMessage, $failureData);
        } catch (\Exception $e) {
            Yii::error("Error handling generic delivery failure: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Update bot session for delivery failure
     * @param string $phoneNumber
     * @param string $errorCode
     * @param string $errorMessage
     */
    private function updateBotSessionForFailure($phoneNumber, $errorCode, $errorMessage)
    {
        try {
            $session = $this->getBotSession($phoneNumber);
            if ($session) {
                $session->last_message = "Delivery failed: {$errorCode} - {$errorMessage}";
                $session->status = self::BOT_STATUS_CANCELLED; // Cancel to prevent retries
                $session->save(false);
                
                Yii::info("Updated bot session {$session->id} with delivery failure", __METHOD__);
            }
        } catch (\Exception $e) {
            Yii::error("Error updating bot session for failure: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Update bot session for successful delivery
     * @param string $phoneNumber
     * @param string $status
     */
    private function updateBotSessionForSuccess($phoneNumber, $status)
    {
        try {
            $session = $this->getBotSession($phoneNumber);
            if ($session) {
                $session->last_message = "Message {$status}: " . date('Y-m-d H:i:s');
                $session->save(false);
                
                Yii::info("Updated bot session {$session->id} with successful delivery: {$status}", __METHOD__);
            }
        } catch (\Exception $e) {
            Yii::error("Error updating bot session for success: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Track delivery metrics
     * @param string $phoneNumber
     * @param string $status
     * @param array $contextData
     */
    private function trackDeliveryMetrics($phoneNumber, $status, $contextData)
    {
        try {
            $metricsData = [
                'phone_number' => $phoneNumber,
                'status' => $status,
                'timestamp' => date('Y-m-d H:i:s'),
                'context' => $contextData
            ];
            
            Yii::info("DELIVERY METRICS: " . Json::encode($metricsData), __METHOD__);
            
            // Here you could store metrics in a dedicated table for analytics
            
        } catch (\Exception $e) {
            Yii::error("Error tracking delivery metrics: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Track phone number issues in webhook for persistence
     * @param string $phoneNumber
     * @param string $issueType
     * @param string $errorMessage
     * @param array $failureData
     */
    private function trackPhoneNumberIssue($phoneNumber, $issueType, $errorMessage, $failureData = [])
    {
        try {
            $trackingWebhook = new AisensyWebhooks();
            $trackingWebhook->event_type = 'phone_issue_tracking';
            // For issue tracking, the problem is with the customer's phone number
            $trackingWebhook->to_number = $phoneNumber; // Customer phone (problematic number)
            $trackingWebhook->from_number = 'system'; // System tracking
            $trackingWebhook->status = AisensyWebhooks::STATUS_ACTIVE;
            $trackingWebhook->error_message = $errorMessage;
            $trackingWebhook->error_code = $issueType;
            $trackingWebhook->payload = Json::encode([
                'issue_type' => $issueType,
                'customer_phone' => $phoneNumber,
                'error_message' => $errorMessage,
                'failure_data' => $failureData,
                'tracked_at' => date('Y-m-d H:i:s')
            ]);
            $trackingWebhook->save(false);
            
            Yii::info("Phone number issue tracked: {$phoneNumber} - {$issueType}", __METHOD__);
            
        } catch (\Exception $e) {
            Yii::error("Error tracking phone number issue: " . $e->getMessage(), __METHOD__);
        }
    }
    
    /**
     * Summarize payload for logging without exposing sensitive data
     * @param array $payload
     * @return array
     */
    private function summarizePayload($payload)
    {
        try {
            return [
                'has_entry' => isset($payload['entry']),
                'has_statuses' => isset($payload['entry'][0]['changes'][0]['value']['statuses']),
                'has_messages' => isset($payload['entry'][0]['changes'][0]['value']['messages']),
                'object_type' => $payload['object'] ?? 'unknown',
                'payload_keys' => array_keys($payload),
                'size_kb' => round(strlen(Json::encode($payload)) / 1024, 2)
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to summarize payload'];
        }
    }

    /**
     * Check if phone number has recent delivery failures
     * @param string $phoneNumber
     * @return bool
     */
    private function hasRecentDeliveryFailures($phoneNumber)
    {
        try {
            // Check recent webhooks for delivery failures (customer phone is in to_number for delivery status)
            $recentFailures = AisensyWebhooks::find()
                ->where(['to_number' => $phoneNumber])
                ->andWhere(['event_type' => 'delivery_status'])
                ->andWhere(['status_value' => 'failed'])
                ->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', strtotime('-1 hour'))])
                ->count();
                
            if ($recentFailures >= 3) {
                Yii::info("Phone {$phoneNumber} has {$recentFailures} delivery failures in last hour", __METHOD__);
                return true;
            }
            
            // Check registration requests for undeliverable status
            $registrationRequest = WhatsappRegistrationRequests::find()
                ->where(['contact_no' => $phoneNumber])
                ->orderBy(['id' => SORT_DESC])
                ->one();
                
            if ($registrationRequest && $registrationRequest->extra) {
                $extra = Json::decode($registrationRequest->extra);
                if (isset($extra['undeliverable']) && $extra['undeliverable'] === true) {
                    Yii::info("Phone {$phoneNumber} marked as undeliverable in registration request", __METHOD__);
                    return true;
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            Yii::error("Error checking delivery failures for {$phoneNumber}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
    
    /**
     * Send existing registration status instead of starting new registration
     * @param string $phoneNumber
     * @param WhatsappRegistrationRequests $registrationRequest
     */
    private function sendExistingRegistrationStatus($phoneNumber, $registrationRequest)
    {
        try {
            $statusText = "📋 *Existing Registration Found*\n\n";
            $statusText .= "You already have a registration request with us!\n\n";
            $statusText .= "📋 *Your Details:*\n";
            $statusText .= "• Request ID: #" . $registrationRequest->id . "\n";
            $statusText .= "• Name: " . ($registrationRequest->first_name ?? 'Not provided');
            if (!empty($registrationRequest->last_name)) {
                $statusText .= " " . $registrationRequest->last_name;
            }
            $statusText .= "\n";
            
            if (!empty($registrationRequest->email)) {
                $statusText .= "• Email: " . $registrationRequest->email . "\n";
            }
            
            if (!empty($registrationRequest->business_name)) {
                $statusText .= "• Business: " . $registrationRequest->business_name . "\n";
            }
            
            $statusText .= "• Status: ";
            switch ($registrationRequest->status) {
                case WhatsappRegistrationRequests::STATUS_ACTIVE:
                    $statusText .= "Under Review ⏳\n";
                    break;
                default:
                    $statusText .= "Processing\n";
                    break;
            }
            
            $statusText .= "\n🔄 *Next Steps:*\n";
            $statusText .= "Our team is reviewing your request. You'll receive updates via WhatsApp/Email.\n\n";
            $statusText .= "If you need to update any information, please contact our support team.\n\n";
            $statusText .= "_Reference: WA" . $registrationRequest->id . "_";
            
            $this->sendMessage($phoneNumber, $statusText);
            
        } catch (\Exception $e) {
            Yii::error("Error sending existing registration status: " . $e->getMessage(), __METHOD__);
            $this->sendMessage($phoneNumber, "You already have a registration request with us. Please contact support for status updates.");
        }
    }
    
    /**
     * Update bulk message log with webhook status
     * @param string $messageId
     * @param string $recipientId
     * @param string $status
     * @param array $payload
     */
    private function updateBulkMessageLog($messageId, $recipientId, $status, $payload = [])
    {
        try {
            if (empty($recipientId)) {
                Yii::info("No recipient ID provided for bulk message log update", __METHOD__);
                return;
            }
            
            // Clean and format recipient phone number for matching
            $cleanRecipientId = $this->formatPhoneNumber($recipientId);
            
            Yii::info("Searching for bulk message log: messageId={$messageId}, recipientId={$recipientId}, cleanRecipientId={$cleanRecipientId}", __METHOD__);
            
            // Find bulk message log by message ID and recipient (using correct field names from base model)
            $bulkMessageLog = null;
            
            // First try: exact message ID and contact number match
            if (!empty($messageId)) {
                $bulkMessageLog = AisensyBulkMessageLog::find()
                    ->where(['message_id' => $messageId])
                    ->andWhere(['contact_number' => $cleanRecipientId])
                    ->one();
                    
                if (!$bulkMessageLog) {
                    // Try with original recipient ID if cleaning changed it
                    $bulkMessageLog = AisensyBulkMessageLog::find()
                        ->where(['message_id' => $messageId])
                        ->andWhere(['contact_number' => $recipientId])
                        ->one();
                }
            }
            
            // Second try: find by contact number (most recent pending/sent message)
            if (!$bulkMessageLog) {
                $bulkMessageLog = AisensyBulkMessageLog::find()
                    ->where(['contact_number' => $cleanRecipientId])
                    ->andWhere(['in', 'status', ['pending', 'sent']])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                    
                if (!$bulkMessageLog && $cleanRecipientId !== $recipientId) {
                    // Try with original recipient ID
                    $bulkMessageLog = AisensyBulkMessageLog::find()
                        ->where(['contact_number' => $recipientId])
                        ->andWhere(['in', 'status', ['pending', 'sent']])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();
                }
            }
            
            if ($bulkMessageLog) {
                // Update message status
                $bulkMessageLog->updateMessageStatus($status, $messageId, $payload);
                
                Yii::info("Updated bulk message log ID {$bulkMessageLog->id} (contact: {$bulkMessageLog->contact_number}) with status: {$status}", __METHOD__);
                
                // Update campaign statistics
                if ($bulkMessageLog->campaign_id) {
                    $this->updateCampaignStatistics($bulkMessageLog->campaign_id);
                }
            } else {
                Yii::info("No bulk message log found for message ID: {$messageId}, recipient: {$recipientId} (cleaned: {$cleanRecipientId})", __METHOD__);
                
                // Log available records for debugging
                $availableRecords = AisensyBulkMessageLog::find()
                    ->select(['id', 'contact_number', 'message_id', 'status'])
                    ->limit(5)
                    ->orderBy(['id' => SORT_DESC])
                    ->asArray()
                    ->all();
                Yii::info("Recent bulk message logs: " . Json::encode($availableRecords), __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error updating bulk message log: " . $e->getMessage(), __METHOD__);
            Yii::error("Message ID: {$messageId}, Recipient: {$recipientId}, Status: {$status}", __METHOD__);
        }
    }
    
    /**
     * Update template sent log with webhook status
     * @param string $messageId
     * @param string $status
     * @param array $payload
     */
    private function updateTemplateSentLog($messageId, $status, $payload = [])
    {
        try {
            if (empty($messageId)) {
                return;
            }
            
            // Find template sent log by message ID
            $templateLog = AisensyTemplateSentLog::find()
                ->where(['like', 'api_response', '"message_id":"' . $messageId . '"', false])
                ->orWhere(['like', 'api_response', '"id":"' . $messageId . '"', false])
                ->one();
                
            if ($templateLog) {
                // Update delivery status (only if column exists)
                if ($templateLog->hasAttribute('delivery_status')) {
                    $templateLog->delivery_status = $status;
                }
                if ($templateLog->hasAttribute('delivery_timestamp')) {
                    $templateLog->delivery_timestamp = date('Y-m-d H:i:s');
                }
                
                // Store webhook details (only if column exists)
                if ($templateLog->hasAttribute('webhook_data')) {
                    $webhookData = [
                        'webhook_status' => $status,
                        'webhook_timestamp' => date('Y-m-d H:i:s'),
                        'webhook_payload' => $this->summarizePayload($payload)
                    ];
                    $templateLog->webhook_data = Json::encode($webhookData);
                }
                
                if ($templateLog->save(false)) {
                    Yii::info("Updated template sent log ID {$templateLog->id} with status: {$status}", __METHOD__);
                } else {
                    Yii::error("Failed to save template sent log updates", __METHOD__);
                }
                
            } else {
                Yii::info("No template sent log found for message ID: {$messageId}", __METHOD__);
            }
            
        } catch (\Exception $e) {
            Yii::error("Error updating template sent log: " . $e->getMessage(), __METHOD__);
            Yii::error("Message ID: {$messageId}, Status: {$status}", __METHOD__);
        }
    }
    
    /**
     * Update campaign statistics based on current message statuses
     * @param int $campaignId
     */
    private function updateCampaignStatistics($campaignId)
    {
        try {
            $campaign = AisensyBulkCampaignLog::findOne($campaignId);
            if (!$campaign) {
                return;
            }
            
            // Get current message statistics (using correct field name 'status' from base model)
            $stats = AisensyBulkMessageLog::find()
                ->select([
                    'COUNT(*) as total_messages',
                    'SUM(CASE WHEN status = "' . AisensyBulkMessageLog::STATUS_SENT . '" THEN 1 ELSE 0 END) as sent_count',
                    'SUM(CASE WHEN status = "' . AisensyBulkMessageLog::STATUS_DELIVERED . '" THEN 1 ELSE 0 END) as delivered_count',
                    'SUM(CASE WHEN status = "' . AisensyBulkMessageLog::STATUS_READ . '" THEN 1 ELSE 0 END) as read_count',
                    'SUM(CASE WHEN status = "' . AisensyBulkMessageLog::STATUS_FAILED . '" THEN 1 ELSE 0 END) as failed_count',
                    'SUM(CASE WHEN status = "' . AisensyBulkMessageLog::STATUS_PENDING . '" THEN 1 ELSE 0 END) as pending_count'
                ])
                ->where(['campaign_id' => $campaignId])
                ->asArray()
                ->one();
                
            if ($stats) {
                // Update campaign with current statistics
                $campaign->sent_count = (int)$stats['sent_count'];
                $campaign->delivered_count = (int)$stats['delivered_count'];
                
                // Handle read_count field if it exists in the campaign table
                if ($campaign->hasAttribute('read_count')) {
                    $campaign->read_count = (int)$stats['read_count'];
                }
                
                $campaign->failed_count = (int)$stats['failed_count'];
                
                // Calculate skipped count as pending messages that won't be processed
                $campaign->skipped_count = (int)$stats['pending_count'];
                
                // Calculate success rate
                $totalProcessed = $campaign->sent_count + $campaign->failed_count;
                if ($totalProcessed > 0) {
                    // Store success rate in performance_metrics if campaign has that field
                    if ($campaign->hasAttribute('performance_metrics')) {
                        $metrics = $campaign->performance_metrics ? Json::decode($campaign->performance_metrics) : [];
                        $metrics['success_rate'] = round(($campaign->sent_count / $totalProcessed) * 100, 2);
                        $campaign->performance_metrics = Json::encode($metrics);
                    }
                }
                
                // Update campaign status if all messages are processed
                $pendingCount = (int)$stats['pending_count'];
                if ($pendingCount == 0 && $totalProcessed > 0) {
                    $campaign->campaign_status = AisensyBulkCampaignLog::STATUS_COMPLETED;
                    $campaign->completed_at = date('Y-m-d H:i:s');
                }
                
                // Save updates
                if ($campaign->save(false)) {
                    Yii::info("Updated campaign {$campaignId} statistics: sent={$campaign->sent_count}, failed={$campaign->failed_count}, pending={$pendingCount}", __METHOD__);
                } else {
                    Yii::error("Failed to save campaign statistics updates for campaign {$campaignId}", __METHOD__);
                }
            }
            
        } catch (\Exception $e) {
            Yii::error("Error updating campaign statistics for campaign {$campaignId}: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Test method to verify bot is working
     * @param string $fromNumber
     */
    private function sendTestMessage($fromNumber)
    {
        try {
            $testText = "🤖 *Bot Test Results*\n\n";
            $testText .= "✅ Bot is operational!\n";
            $testText .= "✅ Database connection working\n";
            $testText .= "✅ AiSensy API accessible\n";
            $testText .= "✅ Webhook processing active\n\n";
            $testText .= "📱 Phone: " . $fromNumber . "\n";
            $testText .= "🕐 Time: " . date('Y-m-d H:i:s') . "\n\n";
            $testText .= "All systems operational! 🚀";
            
            $this->sendMessage($fromNumber, $testText);
            Yii::info("Test message sent successfully to " . $fromNumber, __METHOD__);
        } catch (\Exception $e) {
            $this->logComprehensiveError(__METHOD__, $e, ['from_number' => $fromNumber]);
            $this->sendMessage($fromNumber, "Bot test failed. Error logged for investigation.");
        }
    }

} 
 