<?php

namespace app\modules\api\controllers;

use app\modules\admin\models\WebSetting;
use Yii;

use yii\helpers\Json;
use yii\httpclient\Client;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;

/**
 * Instagram Webhook Controller for Estetica
 * 
 * This controller handles Instagram Business API webhooks and provides automated responses
 * for customer interactions on Instagram.
 * 
 * Features:
 * - Instagram Direct Message handling
 * - Comment auto-replies with trigger words
 * - Instagram mention processing
 * - Story interaction handling
 * - Conversation state management
 * - Webhook verification for Meta
 * 
 * Webhook Events Supported:
 * - messages (Direct Messages)
 * - messaging_postbacks (Quick Replies, Persistent Menu)
 * - comments (Post Comments)
 * - mentions (Story/Post Mentions)
 * - story_insights (Story Analytics)
 * 
 * Setup Requirements:
 * 1. Instagram Business Account
 * 2. Facebook App with Instagram Basic Display API
 * 3. Valid access token stored in WebSetting as 'instagram_token'
 * 4. Instagram Business Account ID stored as 'instagram_page_id'
 * 5. Webhook URL configured in Facebook App settings
 * 
 * TODO Items:
 * - Create InstagramWebhookLogs model for logging
 * - Create InstagramApiLogs model for API call logging  
 * - Create InstagramUserState model for conversation state
 * - Create InstagramConversationFlows model for automated responses
 * 
 * @author Estetica Development Team
 * @version 1.0
 */
class InstagramWebHookController extends BKController
{
    public $enableCsrfValidation = false;

    private $verifyToken = 'estetica_instagram_verify_2025';
    private $instagramApiUrl = 'https://graph.facebook.com/v20.0/';
    private $accessToken;
    private $pageId; // Instagram Business Account ID

    /**
     * Define behaviors for CORS and access control
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => ['*'], // Allow all origins for Instagram API (Meta servers)
                    'Access-Control-Request-Method' => ['GET', 'POST'], // Allow GET for verification, POST for messages
                    'Access-Control-Request-Headers' => ['*'], // Allow all headers
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 3600,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className()
                ],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?'], // Allow guest access for webhook
                    ],
                ],
            ],
        ]);
    }

    /**
     * Initialize controller and fetch Instagram token
     */
    public function init()
    {
        parent::init();
        // Fetch Instagram token from WebSetting
        $settings = new WebSetting();
        $instagram_token = $settings->getSettingBykey('instagram_token');
        $instagram_page_id = $settings->getSettingBykey('instagram_page_id');

        if (!$instagram_token) {
            Yii::error('Failed to retrieve Instagram token from WebSetting', __METHOD__);
            throw new \yii\base\Exception('Instagram token not configured');
        }

        if (!$instagram_page_id) {
            Yii::error('Failed to retrieve Instagram Page ID from WebSetting', __METHOD__);
            throw new \yii\base\Exception('Instagram Page ID not configured');
        }

        $this->accessToken = $instagram_token;
        $this->pageId = $instagram_page_id;
    }

    /**
     * Webhook entry point
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;

        // Log request details for debugging
        Yii::info("Request received: method={$request->method}, params=" . Json::encode($request->get()), __METHOD__);

        // Handle webhook verification for GET requests
        if ($request->isGet) {
            return $this->verifyWebhook();
        }

        // Ensure request is POST for message processing
        if (!$request->isPost) {
            Yii::error("Invalid request method: {$request->method}", __METHOD__);
            Yii::$app->response->statusCode = 405;
            return $this->asJson(['status' => 'error', 'message' => 'Invalid request method']);
        }

        // Process incoming POST payload
        $rawPayload = $request->getRawBody();
        if (empty($rawPayload)) {
            Yii::error("Empty payload received", __METHOD__);
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['status' => 'error', 'message' => 'Empty payload']);
        }

        $data = Json::decode($rawPayload, true);
        if (!$data) {
            Yii::error("Invalid JSON payload: {$rawPayload}", __METHOD__);
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['status' => 'error', 'message' => 'Invalid JSON payload']);
        }

        // Log raw payload
        $this->logWebhook($rawPayload);

        // Process message
        try {
            $this->processIncomingMessage($data);
            return $this->asJson(['status' => 'success', 'message' => 'Message processed']);
        } catch (\Throwable $e) {
            Yii::error("Webhook processing error: {$e->getMessage()}", __METHOD__);
            Yii::$app->response->statusCode = 500;
            return $this->asJson(['status' => 'error', 'message' => 'Internal server error']);
        }
    }

    /**
     * Verify webhook subscription
     */
    private function verifyWebhook()
    {
        $mode = Yii::$app->request->get('hub_mode');
        $token = Yii::$app->request->get('hub_verify_token');
        $challenge = Yii::$app->request->get('hub_challenge');

        Yii::info("Webhook verification: mode=$mode, token=$token, challenge=$challenge", __METHOD__);

        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::info("Webhook verification successful, returning challenge: $challenge", __METHOD__);
            return $challenge;
        }

        Yii::error("Webhook verification failed: mode=$mode, token=$token", __METHOD__);
        return 'Invalid verification token';
    }

    /**
     * Log webhook payload to database (fallback logging)
     */
    private function logWebhook($payload)
    {
        // Use Yii logging until Instagram-specific models are created
        Yii::info("Instagram Webhook Payload: " . $payload, __METHOD__);
        
        // TODO: Create InstagramWebhookLogs model and use:
        // $log = new InstagramWebhookLogs();
        // $log->payload = $payload;
        // $log->status = 1;
        // $log->save(false);
    }

    /**
     * Process incoming Instagram message/event
     */
    private function processIncomingMessage($data)
    {
        if (!isset($data['entry'][0]['messaging'][0]) && !isset($data['entry'][0]['changes'][0])) {
            Yii::info("No messages or changes found in payload", __METHOD__);
            return;
        }

        // Handle Instagram messaging (direct messages)
        if (isset($data['entry'][0]['messaging'][0])) {
            $this->handleInstagramMessaging($data['entry'][0]['messaging'][0]);
        }

        // Handle Instagram changes (comments, mentions, story replies)
        if (isset($data['entry'][0]['changes'][0])) {
            $this->handleInstagramChanges($data['entry'][0]['changes'][0]);
        }
    }

    /**
     * Handle Instagram Direct Messages
     */
    private function handleInstagramMessaging($messaging)
    {
        $senderId = $messaging['sender']['id'];
        $recipientId = $messaging['recipient']['id'];

        // Get or create user conversation state
        $userState = $this->getOrCreateUserState($senderId);

        if (isset($messaging['message'])) {
            $message = $messaging['message'];
            
            if (isset($message['text'])) {
                $this->handleTextMessage($message, $userState, $senderId);
            } elseif (isset($message['attachments'])) {
                $this->handleAttachmentMessage($message, $userState, $senderId);
            }
        }

        // Handle postback (quick replies, persistent menu)
        if (isset($messaging['postback'])) {
            $this->handlePostback($messaging['postback'], $userState, $senderId);
        }

        // Update user state
        $userState->last_interaction = date('Y-m-d H:i:s');
        // Mock save since we're using stdClass temporarily
        Yii::info("Instagram user state updated for user: {$userState->user_id}", __METHOD__);
    }

    /**
     * Handle Instagram changes (comments, mentions, story replies)
     */
    private function handleInstagramChanges($changes)
    {
        $field = $changes['field'];
        $value = $changes['value'];

        switch ($field) {
            case 'comments':
                $this->handleComments($value);
                break;
            case 'mentions':
                $this->handleMentions($value);
                break;
            case 'story_insights':
                $this->handleStoryInsights($value);
                break;
            default:
                Yii::info("Unsupported change field: $field", __METHOD__);
        }
    }

    /**
     * Handle Instagram comments
     */
    private function handleComments($value)
    {
        if (isset($value['text']) && isset($value['from']['id'])) {
            $commentText = $value['text'];
            $userId = $value['from']['id'];
            $commentId = $value['id'];

            // Get or create user state
            $userState = $this->getOrCreateUserState($userId);

            // Process comment and potentially reply
            $this->processCommentResponse($commentText, $userState, $commentId);
        }
    }

    /**
     * Handle Instagram mentions
     */
    private function handleMentions($value)
    {
        if (isset($value['text']) && isset($value['from']['id'])) {
            $mentionText = $value['text'];
            $userId = $value['from']['id'];
            $mediaId = $value['media_id'] ?? null;

            // Get or create user state
            $userState = $this->getOrCreateUserState($userId);

            // Process mention and potentially respond
            $this->processMentionResponse($mentionText, $userState, $mediaId);
        }
    }

    /**
     * Handle story insights
     */
    private function handleStoryInsights($value)
    {
        // Log story insights for analytics
        Yii::info("Story insights received: " . Json::encode($value), __METHOD__);
    }

    /**
     * Handle text messages
     */
    private function handleTextMessage($message, $userState, $senderId)
    {
        $text = strtolower(trim($message['text']));
        $language = $userState->language ?? 'en';

        // Load conversation flows from database
        $flow = $this->getConversationFlow($language, $userState->current_state, $text);

        // Send response via Instagram API
        $this->sendInstagramMessage($senderId, $flow['response_text'], $flow['response_interactive'] ? Json::decode($flow['response_interactive'], true) : null);

        // Update conversation state
        $userState->current_state = $flow['next_state'];
        Yii::info("Instagram user state updated for user: {$userState->user_id}, new state: {$flow['next_state']}", __METHOD__);
    }

    /**
     * Handle attachment messages (images, videos, etc.)
     */
    private function handleAttachmentMessage($message, $userState, $senderId)
    {
        $attachments = $message['attachments'];
        $language = $userState->language ?? 'en';

        // Process first attachment
        $attachment = $attachments[0];
        $attachmentType = $attachment['type'];

        // Get appropriate response based on attachment type
        $responseText = "Thank you for sharing the $attachmentType. How can I help you today?";
        
        $this->sendInstagramMessage($senderId, $responseText);
    }

    /**
     * Handle postback messages (quick replies, persistent menu)
     */
    private function handlePostback($postback, $userState, $senderId)
    {
        $payload = $postback['payload'];
        $language = $userState->language ?? 'en';

        // TODO: Replace with InstagramConversationFlows when model is created
        // For now, use basic response logic
        $responseText = $this->getBasicResponse($payload);
        
        $this->sendInstagramMessage($senderId, $responseText);

        // Update state based on payload
        $userState->current_state = $this->getNextState($payload);
        Yii::info("Instagram user state updated for user: {$userState->user_id}, new state: {$userState->current_state}", __METHOD__);
    }

    /**
     * Basic response logic (fallback until models are created)
     */
    private function getBasicResponse($payload)
    {
        switch ($payload) {
            case 'get_started':
                return "Welcome to Estetica! How can I help you today?";
            case 'services':
                return "We offer various beauty and wellness services. Would you like to know more about our treatments?";
            case 'booking':
                return "I'd be happy to help you book an appointment. Please visit our website or call us directly.";
            case 'contact':
                return "You can reach us at our salon or through this Instagram page. We're here to help!";
            default:
                return "Thank you for your message! Our team will get back to you soon.";
        }
    }

    /**
     * Get next state based on payload (fallback logic)
     */
    private function getNextState($payload)
    {
        switch ($payload) {
            case 'get_started':
                return 'main_menu';
            case 'services':
                return 'services_info';
            case 'booking':
                return 'booking_process';
            case 'contact':
                return 'contact_info';
            default:
                return 'initial';
        }
    }

    /**
     * Process comment responses
     */
    private function processCommentResponse($commentText, $userState, $commentId)
    {
        $text = strtolower(trim($commentText));
        $language = $userState->language ?? 'en';

        // Check if comment contains trigger words for auto-reply
        $triggerWords = ['help', 'info', 'price', 'booking', 'appointment'];
        $shouldReply = false;

        foreach ($triggerWords as $trigger) {
            if (strpos($text, $trigger) !== false) {
                $shouldReply = true;
                break;
            }
        }

        if ($shouldReply) {
            // Reply to comment
            $this->replyToComment($commentId, "Thanks for your comment! Please send us a direct message for more information.");
        }
    }

    /**
     * Process mention responses
     */
    private function processMentionResponse($mentionText, $userState, $mediaId)
    {
        $text = strtolower(trim($mentionText));
        
        // Log mention for analytics
        Yii::info("Mention received: $mentionText from user: {$userState->user_id}", __METHOD__);
        
        // Could implement auto-response to mentions if needed
        // For now, just log the mention
    }

    /**
     * Get or create user conversation state (fallback implementation)
     */
    private function getOrCreateUserState($userId)
    {
        // TODO: Replace with InstagramUserState when model is created
        // For now, create a simple object structure
        $state = new \stdClass();
        $state->user_id = $userId;
        $state->current_state = 'initial';
        $state->language = 'en';
        $state->last_interaction = date('Y-m-d H:i:s');

        return $state;
    }

    /**
     * Load conversation flow from database (fallback implementation)
     */
    private function getConversationFlow($language, $state, $text)
    {
        // TODO: Replace with InstagramConversationFlows when model is created
        // For now, use basic response logic based on text patterns
        $text = strtolower($text);
        
        if (strpos($text, 'hello') !== false || strpos($text, 'hi') !== false) {
            return [
                'response_text' => 'Hello! Welcome to Estetica. How can I help you today?',
                'response_interactive' => null,
                'next_state' => 'greeting'
            ];
        } elseif (strpos($text, 'service') !== false || strpos($text, 'treatment') !== false) {
            return [
                'response_text' => 'We offer a wide range of beauty treatments including facials, hair styling, and wellness services. Would you like to know more?',
                'response_interactive' => null,
                'next_state' => 'services'
            ];
        } elseif (strpos($text, 'book') !== false || strpos($text, 'appointment') !== false) {
            return [
                'response_text' => 'I\'d be happy to help you book an appointment. Please let me know what service you\'re interested in.',
                'response_interactive' => null,
                'next_state' => 'booking'
            ];
        } else {
            return [
                'response_text' => 'Thank you for your message! Our team will get back to you soon.',
                'response_interactive' => null,
                'next_state' => 'initial'
            ];
        }
    }

    /**
     * Send Instagram Direct Message
     */
    private function sendInstagramMessage($userId, $text, $interactive = null)
    {
        $client = new Client();
        $payload = [
            'recipient' => ['id' => $userId],
            'message' => []
        ];

        if ($interactive) {
            $payload['message'] = $interactive;
        } else {
            $payload['message']['text'] = $text;
        }

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->instagramApiUrl . $this->pageId . '/messages')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])
            ->setContent(Json::encode($payload))
            ->send();

        // Log API response (fallback logging)
        Yii::info("Instagram Message Sent - User: $userId, Response: " . ($response->isOk ? 'Success' : 'Failed'), __METHOD__);
        
        // TODO: Create InstagramApiLogs model and use:
        // $log = new InstagramApiLogs();
        // $log->user_id = $userId;
        // $log->payload = Json::encode($payload);
        // $log->response = $response->content;
        // $log->status = $response->isOk ? 1 : 0;
        // $log->save();

        return $response->isOk;
    }

    /**
     * Reply to Instagram comment
     */
    private function replyToComment($commentId, $message)
    {
        $client = new Client();
        $payload = [
            'message' => $message
        ];

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->instagramApiUrl . $commentId . '/replies')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])
            ->setContent(Json::encode($payload))
            ->send();

        // Log API response (fallback logging)
        Yii::info("Instagram Comment Reply - Comment ID: $commentId, Response: " . ($response->isOk ? 'Success' : 'Failed'), __METHOD__);
        
        // TODO: Create InstagramApiLogs model and use:
        // $log = new InstagramApiLogs();
        // $log->comment_id = $commentId;
        // $log->payload = Json::encode($payload);
        // $log->response = $response->content;
        // $log->status = $response->isOk ? 1 : 0;
        // $log->save();

        return $response->isOk;
    }

    /**
     * Get Instagram user profile information
     */
    private function getUserProfile($userId)
    {
        $client = new Client();
        
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($this->instagramApiUrl . $userId . '?fields=name,profile_pic')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])
            ->send();

        if ($response->isOk) {
            return Json::decode($response->content, true);
        }

        return null;
    }

    /**
     * Send Instagram story reply
     */
    private function sendStoryReply($storyId, $userId, $message)
    {
        $client = new Client();
        $payload = [
            'recipient' => ['id' => $userId],
            'message' => [
                'text' => $message,
                'attachment' => [
                    'type' => 'story_mention',
                    'payload' => [
                        'story_id' => $storyId
                    ]
                ]
            ]
        ];

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->instagramApiUrl . $this->pageId . '/messages')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])
            ->setContent(Json::encode($payload))
            ->send();

        // Log API response (fallback logging)
        Yii::info("Instagram Story Reply - Story ID: $storyId, User: $userId, Response: " . ($response->isOk ? 'Success' : 'Failed'), __METHOD__);
        
        // TODO: Create InstagramApiLogs model and use:
        // $log = new InstagramApiLogs();
        // $log->user_id = $userId;
        // $log->story_id = $storyId;
        // $log->payload = Json::encode($payload);
        // $log->response = $response->content;
        // $log->status = $response->isOk ? 1 : 0;
        // $log->save();

        return $response->isOk;
    }

    /**
     * Set up Instagram webhook subscriptions
     * This method can be called to configure webhook subscriptions for Instagram
     */
    public function actionSetupWebhook()
    {
        $client = new Client();
        
        // Subscribe to messaging, comments, and mentions
        $subscriptions = ['messages', 'messaging_postbacks', 'comments', 'mentions'];
        
        foreach ($subscriptions as $subscription) {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($this->instagramApiUrl . $this->pageId . '/subscribed_apps')
                ->setHeaders([
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json'
                ])
                ->setContent(Json::encode([
                    'subscribed_fields' => $subscription
                ]))
                ->send();

            Yii::info("Instagram webhook subscription for $subscription: " . ($response->isOk ? 'Success' : 'Failed'), __METHOD__);
        }

        return $this->asJson(['status' => 'webhook_setup_completed']);
    }

    /**
     * Get Instagram page information
     */
    public function actionGetPageInfo()
    {
        $client = new Client();
        
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($this->instagramApiUrl . $this->pageId . '?fields=name,username,followers_count,media_count')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])
            ->send();

        if ($response->isOk) {
            return $this->asJson(Json::decode($response->content, true));
        }

        return $this->asJson(['error' => 'Failed to fetch page information']);
    }

    /**
     * Test Instagram API connection
     */
    public function actionTestConnection()
    {
        $client = new Client();
        
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($this->instagramApiUrl . 'me?fields=name,id')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])
            ->send();

        if ($response->isOk) {
            $data = Json::decode($response->content, true);
            return $this->asJson([
                'status' => 'connected',
                'account_name' => $data['name'] ?? 'Unknown',
                'account_id' => $data['id'] ?? 'Unknown'
            ]);
        }

        return $this->asJson([
            'status' => 'failed',
            'error' => 'Unable to connect to Instagram API'
        ]);
    }

    /**
     * Send a test message to verify webhook functionality
     */
    public function actionSendTestMessage()
    {
        $request = Yii::$app->request;
        $userId = $request->post('user_id');
        $message = $request->post('message', 'This is a test message from Estetica Instagram Bot.');

        if (!$userId) {
            return $this->asJson(['error' => 'user_id is required']);
        }

        $success = $this->sendInstagramMessage($userId, $message);

        return $this->asJson([
            'status' => $success ? 'sent' : 'failed',
            'message' => $message,
            'user_id' => $userId
        ]);
    }
}
