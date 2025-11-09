<?php

namespace app\modules\api\controllers;

use app\modules\admin\models\WebSetting;
use app\modules\admin\models\WhatsappApiLogs;
use app\modules\admin\models\WhatsappConversationFlows;
use app\modules\admin\models\WhatsappUserState;
use app\modules\admin\models\WhatsappWebhookLogs;
use Yii;

use yii\helpers\Json;
use yii\httpclient\Client;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;

/**
 * Advanced WhatsApp Webhook Controller
 * Handles incoming WhatsApp messages with state management and conversation flows
 */
class WhatsAppWebhookController extends BKController
{
    public $enableCsrfValidation = false;

    private $verifyToken = 'estetica_verify_2025';
    private $whatsappApiUrl = 'https://graph.facebook.com/v20.0/';
    private $accessToken;
    private $phoneNumberId = '734023276451663'; // Provided phone number ID from Meta Business Suite

    /**
     * Define behaviors for CORS and access control
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => ['*'], // Allow all origins for WhatsApp API (Meta servers)
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
                        'roles' => ['@'], // Allow guest access for webhook
                    ],
                ],
            ],
        ]);
    }

    /**
     * Initialize controller and fetch WhatsApp token
     */
    public function init()
    {
        parent::init();
        // Fetch WhatsApp token from WebSetting
        $settings = new WebSetting();
        $whatsapp_token = $settings->getSettingBykey('whatsapp_token');

        if (!$whatsapp_token) {
            Yii::error('Failed to retrieve WhatsApp token from WebSetting', __METHOD__);
            throw new \yii\base\Exception('WhatsApp token not configured');
        }

        $this->accessToken = $whatsapp_token;
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
            return $this->asJson(['status' => 'error', 'message' => 'Invalid request method'], 405);
        }

        // Process incoming POST payload
        $rawPayload = $request->getRawBody();
        if (empty($rawPayload)) {
            Yii::error("Empty payload received", __METHOD__);
            return $this->asJson(['status' => 'error', 'message' => 'Empty payload'], 400);
        }

        $data = Json::decode($rawPayload, true);
        if (!$data) {
            Yii::error("Invalid JSON payload: {$rawPayload}", __METHOD__);
            return $this->asJson(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
        }

        // Log raw payload
        $this->logWebhook($rawPayload);

        // Process message
        try {
            $this->processIncomingMessage($data);
            return $this->asJson(['status' => 'success', 'message' => 'Message processed']);
        } catch (\Throwable $e) {
            Yii::error("Webhook processing error: {$e->getMessage()}", __METHOD__);
            return $this->asJson(['status' => 'error', 'message' => 'Internal server error'], 500);
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
     * Log webhook payload to database
     */
    private function logWebhook($payload)
    {
        $log = new WhatsappWebhookLogs();
        $log->payload = $payload;
        $log->status = 1;
        $log->save(false);
    }

    /**
     * Process incoming WhatsApp message
     */
    private function processIncomingMessage($data)
    {
        if (!isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            Yii::info("No messages found in payload", __METHOD__);
            return;
        }

        $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $from = $message['from'];
        $messageType = $message['type'];

        // Get or create user conversation state
        $userState = $this->getOrCreateUserState($from);

        // Handle different message types
        switch ($messageType) {
            case 'text':
                $this->handleTextMessage($message, $userState);
                break;
            case 'interactive':
                $this->handleInteractiveMessage($message, $userState);
                break;
            case 'button':
                $this->handleButtonMessage($message, $userState);
                break;
            default:
                Yii::info("Unsupported message type: $messageType", __METHOD__);
        }

        // Update user state
        $userState->last_interaction = date('Y-m-d H:i:s');
        $userState->save();
    }

    /**
     * Handle text messages
     */
    private function handleTextMessage($message, $userState)
    {
        $text = strtolower(trim($message['text']['body']));
        $language = $userState->language ?? 'en';

        // Load conversation flows from database
        $flow = $this->getConversationFlow($language, $userState->current_state, $text);

        // Send response
        $this->sendWhatsappMessage($userState->phone_number, $flow['response_text'], $flow['response_interactive'] ? Json::decode($flow['response_interactive'], true) : null);

        // Update conversation state
        $userState->current_state = $flow['next_state'];
        $userState->save();
    }

    /**
     * Handle interactive messages (list replies, button replies)
     */
    private function handleInteractiveMessage($message, $userState)
    {
        $interactive = $message['interactive'];
        $id = $interactive['list_reply']['id'] ?? $interactive['button_reply']['id'] ?? '';
        $language = $userState->language ?? 'en';

        // Find flow for interactive response
        $flow = WhatsappConversationFlows::find()
            ->where(['language' => $language, 'state' => $userState->current_state])
            ->andWhere(['like', 'pattern', $id])
            ->one();

        if (!$flow) {
            $flow = WhatsappConversationFlows::find()
                ->where(['language' => $language, 'state' => $userState->current_state])
                ->one();
        }

        $responseText = $flow ? $flow->response_text : 'Please select a valid option.';
        $responseInteractive = $flow && $flow->response_interactive ? Json::decode($flow['response_interactive'], true) : null;
        $nextState = $flow ? $flow->next_state : $userState->current_state;

        $this->sendWhatsappMessage($userState->phone_number, $responseText, $responseInteractive);

        $userState->current_state = $nextState;
        $userState->save();
    }

    /**
     * Handle button messages
     */
    private function handleButtonMessage($message, $userState)
    {
        $button = $message['button'];
        $payload = $button['payload'] ?? '';
        $language = $userState->language ?? 'en';

        // Find flow for button response
        $flow = WhatsappConversationFlows::find()
            ->where(['language' => $language, 'state' => $userState->current_state])
            ->andWhere(['like', 'pattern', $payload])
            ->one();

        if (!$flow) {
            $flow = WhatsappConversationFlows::find()
                ->where(['language' => $language, 'state' => $userState->current_state])
                ->one();
        }

        $responseText = $flow ? $flow->response_text : 'Please select a valid option.';
        $responseInteractive = $flow && $flow->response_interactive ? Json::decode($flow['response_interactive'], true) : null;
        $nextState = $flow ? $flow->next_state : $userState->current_state;

        $this->sendWhatsappMessage($userState->phone_number, $responseText, $responseInteractive);

        $userState->current_state = $nextState;
        $userState->save();
    }

    /**
     * Get or create user conversation state
     */
    private function getOrCreateUserState($phoneNumber)
    {
        $state = WhatsappUserState::findOne(['phone_number' => $phoneNumber]);

        if (!$state) {
            $state = new WhatsappUserState();
            $state->phone_number = $phoneNumber;
            $state->current_state = 'initial';
            $state->language = 'en';
            $state->save();
        }

        return $state;
    }

    /**
     * Load conversation flow from database
     */
    private function getConversationFlow($language, $state, $text)
    {
        $flow = WhatsappConversationFlows::find()
            ->where(['language' => $language, 'state' => $state])
            ->andWhere(['REGEXP', 'pattern', $text])
            ->one();

        if (!$flow) {
            // Fallback to default response for the state
            $flow = WhatsappConversationFlows::find()
                ->where(['language' => $language, 'state' => $state])
                ->one();
        }

        if (!$flow) {
            // Fallback to initial state
            $flow = WhatsappConversationFlows::find()
                ->where(['language' => $language, 'state' => 'initial'])
                ->one();
        }

        return [
            'response_text' => $flow ? $flow->response_text : 'Sorry, I didn\'t understand that. Please try again.',
            'response_interactive' => $flow ? $flow->response_interactive : null,
            'next_state' => $flow ? $flow->next_state : 'initial'
        ];
    }

    /**
     * Send WhatsApp message
     */
    private function sendWhatsappMessage($to, $text, $interactive = null)
    {
        $client = new Client();
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $interactive ? 'interactive' : 'text'
        ];

        if ($interactive) {
            $payload['interactive'] = $interactive;
        } else {
            $payload['text'] = ['body' => $text];
        }

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->whatsappApiUrl . $this->phoneNumberId . '/messages')
            ->setHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])
            ->setContent(Json::encode($payload))
            ->send();

        // Log API response
        $log = new WhatsappApiLogs();
        $log->phone_number = $to;
        $log->payload = Json::encode($payload);
        $log->response = $response->content;
        $log->status = $response->isOk ? 1 : 0;
        $log->save();
    }







    



}
