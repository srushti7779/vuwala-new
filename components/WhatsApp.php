<?php

namespace app\components; 

use yii\base\Component;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\WhatsappTemplateComponents;
use app\modules\admin\models\WhatsappTemplates;
use Yii;

/**
 * WhatsApp Component for handling WhatsApp API interactions
 */
class WhatsApp extends Component
{
    /**
     * @var string WhatsApp API base URL
     */
    private $apiBaseUrl = 'https://graph.facebook.com/v19.0';

    /**
     * @var string|null WhatsApp access token
     */
    private $accessToken;

    /**
     * @var string|null WhatsApp business ID
     */
    private $businessId;

    /**
     * @var string|null WhatsApp phone number ID
     */
    private $phoneNumberId;

    /**
     * Initialize component with API credentials
     */
    public function init()
    {
        parent::init();
        $settings = new WebSetting();
        $this->accessToken = $settings->getSettingBykey('whatsapp_token');
        $this->businessId = $settings->getSettingBykey('whatsapp_business_id');
        $this->phoneNumberId = $settings->getSettingBykey('whatsapp_phone_number_id');
    }

    /**
     * Send WhatsApp message using template
     * @param WhatsappTemplates $template
     * @param string $phoneNumber
     * @param array $parameters
     * @param string|null $sessionId
     * @param int $rowIndex
     * @return array
     */
    public function sendMessage($template, $phoneNumber, $parameters = [], $sessionId = null, $rowIndex = 0)
    {
        try {
            // Validate credentials
            if (empty($this->accessToken) || empty($this->phoneNumberId)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp API credentials not configured. Please check access token and phone number ID.'
                ];
            }

            // Validate phone number
            if (!preg_match('/^[0-9]{10,15}$/', $phoneNumber)) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format. Must be 10-15 digits.'
                ];
            }

            // Validate template status
            if ($template->template_status !== 'APPROVED') {
                return [
                    'success' => false,
                    'message' => 'Template is not approved for sending messages.'
                ];
            }

            // Prepare template components
            $templateComponents = $this->prepareTemplateComponents($template->id, $parameters);
            if (!$templateComponents['success']) {
                return $templateComponents;
            }

            // Prepare request data
            $requestData = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'template',
                'template' => [
                    'name' => $template->name,
                    'language' => [
                        'code' => $template->language_code ?: 'en_US'
                    ],
                    'components' => $templateComponents['components']
                ]
            ];

            // Add tracking metadata if provided
            if ($sessionId) {
                $requestData['biz_opaque_callback_data'] = json_encode([
                    'session_id' => $sessionId,
                    'row_index' => $rowIndex,
                    'timestamp' => time()
                ]);
            }

            // Log request
            Yii::info("WhatsApp API Request for {$phoneNumber}: " . json_encode($requestData), __METHOD__);

            // Send API request
            $url = "{$this->apiBaseUrl}/{$this->phoneNumberId}/messages";
            $response = $this->makeApiRequest($url, 'POST', $requestData);

            if (!$response['success']) {
                return $response;
            }

            $responseData = $response['data'];
            if (isset($responseData['messages']) && !empty($responseData['messages'])) {
                $messageId = $responseData['messages'][0]['id'] ?? 'unknown';
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'messageId' => $messageId,
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => 'Unexpected API response: ' . json_encode($responseData),
                'response' => $responseData
            ];

        } catch (\Exception $e) {
            Yii::error("Send Message Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create WhatsApp template via Graph API
     * @param WhatsappTemplates $model
     * @param array $components
     * @return array
     */
    public function createTemplate($model, $components)
    {
        try {
            // Validate credentials
            if (empty($this->accessToken) || empty($this->businessId)) {
                throw new \Exception('Missing WhatsApp API credentials.');
            }

            // Validate components
            if (empty($components)) {
                throw new \Exception('At least one template component is required.');
            }

            // Prepare payload
            $payload = [
                'name' => $model->name,
                'language' => $model->language_code,
                'category' => $model->category,
                'components' => []
            ];

            foreach ($components as $index => $comp) {
                if (empty($comp['type']) || empty($comp['default_value'])) {
                    throw new \Exception("Component #{$index} is missing required fields: type or default value.");
                }

                $item = ['type' => strtoupper($comp['type'])];
                if ($comp['type'] === 'header') {
                    if (empty($comp['subtype'])) {
                        throw new \Exception("Header component #{$index} requires a subtype.");
                    }
                    $item['format'] = strtoupper($comp['subtype']);
                    $item['text'] = $comp['default_value'];
                } elseif ($comp['type'] === 'body') {
                    $item['text'] = $comp['default_value'];
                } elseif ($comp['type'] === 'button') {
                    $item['buttons'] = [['type' => 'QUICK_REPLY', 'text' => $comp['default_value']]];
                } else {
                    throw new \Exception("Invalid component type for component #{$index}.");
                }

                $payload['components'][] = $item;
            }

            // Send API request
            $url = "{$this->apiBaseUrl}/{$this->businessId}/message_templates";
            $response = $this->makeApiRequest($url, 'POST', $payload);

            if (!$response['success']) {
                $errorMessages = [
                    131009 => 'Template name already exists.',
                    190 => 'Invalid access token.'
                ];
                $error = $response['data']['error'] ?? ['code' => 0, 'message' => 'Unknown error'];
                $message = $errorMessages[$error['code'] ?? 0] ?? ($error['message'] ?? 'Unknown error');
                throw new \Exception("Template creation failed: {$message}");
            }

            return $response['data'];

        } catch (\Exception $e) {
            Yii::error("Create Template Error: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Delete WhatsApp template via Graph API
     * @param string $templateName
     * @return bool
     * @throws \Exception
     */
    public function deleteTemplate($templateName)
    {
        try {
            if (empty($templateName)) {
                throw new \Exception('Template name is missing.');
            }

            if (empty($this->accessToken) || empty($this->businessId)) {
                throw new \Exception('Missing WhatsApp API credentials.');
            }

            $url = "{$this->apiBaseUrl}/{$this->businessId}/message_templates?name=" . urlencode($templateName);
            $response = $this->makeApiRequest($url, 'DELETE');

            if (!$response['success'] || !isset($response['data']['success']) || !$response['data']['success']) {
                throw new \Exception('Failed to delete template: ' . json_encode($response['data']));
            }

            return true;

        } catch (\Exception $e) {
            Yii::error("Delete Template Error: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Sync template statuses with WhatsApp API
     * @return array
     */
    public function syncTemplateStatuses()
    {
        try {
            if (empty($this->accessToken) || empty($this->businessId)) {
                return [
                    'success' => false,
                    'message' => 'Missing WhatsApp API credentials.'
                ];
            }

            $url = "{$this->apiBaseUrl}/{$this->businessId}/message_templates";
            $response = $this->makeApiRequest($url, 'GET');

            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to sync statuses: ' . ($response['data']['error']['message'] ?? 'Unknown error')
                ];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($response['data']['data'] as $template) {
                    $model = WhatsappTemplates::findOne(['name' => $template['name']]);
                    if ($model) {
                        $model->template_status = $template['status'];
                        $model->save(false);
                    }
                }
                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Template statuses synced successfully.'
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("Sync Status DB Error: " . $e->getMessage(), __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Error syncing statuses: ' . $e->getMessage()
                ];
            }

        } catch (\Exception $e) {
            Yii::error("Sync Status Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error syncing statuses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import templates from WhatsApp API
     * @return array
     */
    public function importTemplates()
    {
        try {
            if (empty($this->accessToken) || empty($this->businessId)) {
                return [
                    'success' => false,
                    'message' => 'Missing WhatsApp API credentials.'
                ];
            }

            $url = "{$this->apiBaseUrl}/{$this->businessId}/message_templates?fields=name,language,status,category,components";
            $response = $this->makeApiRequest($url, 'GET');

            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch templates: ' . ($response['data']['error']['message'] ?? 'Unknown error')
                ];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($response['data']['data'] as $template) {
                    $model = WhatsappTemplates::findOne(['fb_template_id' => $template['id']])
                        ?? WhatsappTemplates::findOne(['name' => $template['name']])
                        ?? new WhatsappTemplates();

                    $model->name = $template['name'];
                    $model->language_code = $template['language'];
                    $model->description = $template['components'][0]['text'] ?? 'Imported template';
                    $model->category = $template['category'];
                    $model->template_status = $template['status'];
                    $model->fb_template_id = $template['id'];
                    $model->status = $template['status'] === 'APPROVED' ? 1 : 0;
                    $model->created_on = $model->created_on ?? date('Y-m-d H:i:s');
                    $model->updated_on = date('Y-m-d H:i:s');
                    $model->create_user_id = $model->create_user_id ?? Yii::$app->user->id ?? null;
                    $model->update_user_id = Yii::$app->user->id ?? null;

                    if (!$model->save(false)) {
                        throw new \Exception("Failed to save template: {$template['name']}");
                    }

                    WhatsappTemplateComponents::deleteAll(['template_id' => $model->id]);

                    foreach ($template['components'] as $componentIndex => $comp) {
                        $type = strtoupper($comp['type']);
                        $subtype = isset($comp['format']) ? strtoupper($comp['format']) : '';
                        $examples = $comp['example'] ?? [];

                        if ($type === 'HEADER') {
                            $component = new WhatsappTemplateComponents([
                                'template_id' => $model->id,
                                'type' => $type,
                                'subtype' => $subtype,
                                'param_order' => 1,
                                'is_required' => 1,
                                'status' => 1,
                                'created_on' => date('Y-m-d H:i:s'),
                                'create_user_id' => Yii::$app->user->id ?? null
                            ]);

                            if ($subtype === 'IMAGE' && isset($examples['header_handle'][0])) {
                                $component->default_value = $examples['header_handle'][0];
                            } elseif ($subtype === 'VIDEO' && isset($examples['header_handle'][0])) {
                                $component->default_value = $examples['header_handle'][0];
                            } elseif ($subtype === 'DOCUMENT' && isset($examples['header_handle'][0])) {
                                $component->default_value = $examples['header_handle'][0];
                            } elseif ($subtype === 'TEXT' && isset($comp['text'])) {
                                $component->default_value = $comp['text'];
                            }

                            if (!$component->save(false)) {
                                throw new \Exception("Failed to save HEADER component for template: {$template['name']}");
                            }
                        } elseif ($type === 'BODY') {
                            $bodyVariables = $examples['body_text'][0] ?? [];
                            foreach ($bodyVariables as $i => $val) {
                                $component = new WhatsappTemplateComponents([
                                    'template_id' => $model->id,
                                    'type' => $type,
                                    'subtype' => '',
                                    'param_order' => $i + 1,
                                    'variable_name' => 'param_' . ($i + 1),
                                    'default_value' => $val,
                                    'is_required' => 1,
                                    'status' => 1,
                                    'created_on' => date('Y-m-d H:i:s'),
                                    'create_user_id' => Yii::$app->user->id ?? null
                                ]);

                                if (!$component->save(false)) {
                                    throw new \Exception("Failed to save BODY parameter #{$i} for template: {$template['name']}");
                                }
                            }
                        } elseif ($type === 'FOOTER') {
                            $component = new WhatsappTemplateComponents([
                                'template_id' => $model->id,
                                'type' => $type,
                                'subtype' => '',
                                'param_order' => 1,
                                'default_value' => $comp['text'] ?? '',
                                'is_required' => 0,
                                'status' => 1,
                                'created_on' => date('Y-m-d H:i:s'),
                                'create_user_id' => Yii::$app->user->id ?? null
                            ]);

                            if (!$component->save(false)) {
                                throw new \Exception("Failed to save FOOTER component for template: {$template['name']}");
                            }
                        } elseif ($type === 'BUTTONS') {
                            foreach ($comp['buttons'] as $buttonIndex => $button) {
                                $component = new WhatsappTemplateComponents([
                                    'template_id' => $model->id,
                                    'type' => $type,
                                    'subtype' => strtoupper($button['type']),
                                    'param_order' => $buttonIndex + 1,
                                    'variable_name' => 'button_' . ($buttonIndex + 1),
                                    'default_value' => $button['text'] ?? '',
                                    'is_required' => 0,
                                    'status' => 1,
                                    'created_on' => date('Y-m-d H:i:s'),
                                    'create_user_id' => Yii::$app->user->id ?? null
                                ]);

                                if (!$component->save(false)) {
                                    throw new \Exception("Failed to save BUTTON #{$buttonIndex} for template: {$template['name']}");
                                }
                            }
                        }
                    }
                }

                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Templates imported successfully.'
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("Import Templates DB Error: " . $e->getMessage(), __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Error importing templates: ' . $e->getMessage()
                ];
            }

        } catch (\Exception $e) {
            Yii::error("Import Templates Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error importing templates: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepare template components for API request
     * @param int $templateId
     * @param array $parameters
     * @return array
     */
    private function prepareTemplateComponents($templateId, $parameters)
    {
        try {
            $components = WhatsappTemplateComponents::find()
                ->where(['template_id' => $templateId, 'status' => 1])
                ->andWhere(['in', 'type', ['BODY', 'HEADER']])
                ->orderBy(['param_order' => SORT_ASC])
                ->all();

            $templateComponents = [];
            $bodyParams = [];
            $headerParams = [];

            foreach ($components as $component) {
                if (!isset($parameters[$component->id])) {
                    return [
                        'success' => false,
                        'message' => "Missing required parameter for {$component->type} component '{$component->variable_name}' (order {$component->param_order})."
                    ];
                }

                if ($component->type === 'BODY') {
                    if (stripos($component->variable_name, 'url') !== false || preg_match('/^https?:\/\//', $component->default_value)) {
                        if (!filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                            return [
                                'success' => false,
                                'message' => "Invalid URL for parameter '{$component->variable_name}' (order {$component->param_order})."
                            ];
                        }
                    }
                    $bodyParams[] = [
                        'type' => 'text',
                        'text' => $parameters[$component->id]
                    ];
                } elseif ($component->type === 'HEADER') {
                    $compData = ['type' => 'header'];
                    if ($component->subtype === 'IMAGE') {
                        if (!filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                            return [
                                'success' => false,
                                'message' => "Invalid image URL for HEADER parameter (order {$component->param_order})."
                            ];
                        }
                        $headerParams[] = [
                            'type' => 'image',
                            'image' => ['link' => $parameters[$component->id]]
                        ];
                    } elseif ($component->subtype === 'VIDEO') {
                        if (!filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                            return [
                                'success' => false,
                                'message' => "Invalid video URL for HEADER parameter (order {$component->param_order})."
                            ];
                        }
                        $headerParams[] = [
                            'type' => 'video',
                            'video' => ['link' => $parameters[$component->id]]
                        ];
                    } elseif ($component->subtype === 'DOCUMENT') {
                        if (!filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                            return [
                                'success' => false,
                                'message' => "Invalid document URL for HEADER parameter (order {$component->param_order})."
                            ];
                        }
                        $headerParams[] = [
                            'type' => 'document',
                            'document' => ['link' => $parameters[$component->id]]
                        ];
                    } elseif ($component->subtype === 'TEXT') {
                        $headerParams[] = [
                            'type' => 'text',
                            'text' => $parameters[$component->id]
                        ];
                    }
                }
            }

            if (!empty($headerParams)) {
                $templateComponents[] = [
                    'type' => 'header',
                    'parameters' => $headerParams
                ];
            }

            if (!empty($bodyParams)) {
                $templateComponents[] = [
                    'type' => 'body',
                    'parameters' => $bodyParams
                ];
            }

            return [
                'success' => true,
                'components' => $templateComponents
            ];

        } catch (\Exception $e) {
            Yii::error("Prepare Components Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error preparing components: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Make API request to WhatsApp Graph API
     * @param string $url
     * @param string $method
     * @param array $data
     * @return array
     */
    private function makeApiRequest($url, $method = 'GET', $data = [])
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ]);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!empty($curlError)) {
                Yii::error("cURL Error: {$curlError}", __METHOD__);
                return [
                    'success' => false,
                    'message' => "Network error: {$curlError}"
                ];
            }

            $responseData = json_decode($response, true);

            if ($httpCode !== 200 || isset($responseData['error'])) {
                $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
                if (isset($responseData['error']['error_subcode'])) {
                    $errorMessage .= " (Code: {$responseData['error']['error_subcode']})";
                }
                Yii::error("API Error: {$errorMessage}", __METHOD__);
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $responseData
                ];
            }

            return [
                'success' => true,
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Yii::error("API Request Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }



    public static function sendTemplate($phoneNumber, $templateName, $userParams = [], $sessionId = null, $rowIndex = 0)
{
    $template = WhatsappTemplates::findOne(['name' => $templateName, 'status' => 1]);
    if (!$template) {
        return ['success' => false, 'message' => 'Template not found or inactive.'];
    }

    // Map input param names (e.g., 'video_url') to actual component IDs
    $components = WhatsappTemplateComponents::find()
        ->where(['template_id' => $template->id, 'status' => 1])
        ->all();

    $paramMap = [];
    foreach ($components as $comp) {
        if ($comp->type === 'HEADER') {
            $key = strtolower($comp->subtype) . '_url'; // e.g., video_url, image_url
        } elseif ($comp->type === 'BODY') {
            $key = 'param_' . $comp->param_order;
        } elseif ($comp->type === 'BUTTONS') {
            $key = 'button_' . $comp->param_order;
        } elseif ($comp->type === 'FOOTER') {
            $key = 'footer';
        } else {
            continue;
        }

        if (isset($userParams[$key])) {
            $paramMap[$comp->id] = $userParams[$key];
        }
    }

    $whatsapp = new self();
    return $whatsapp->sendMessage($template, $phoneNumber, $paramMap, $sessionId, $rowIndex);
}



public static function getTemplateParameterKeys($templateName)
{
    $template = WhatsappTemplates::findOne(['name' => $templateName]);
    if (!$template) {
        return [
            'success' => false,
            'message' => 'Template not found.'
        ];
    }

    $components = WhatsappTemplateComponents::find()
        ->where(['template_id' => $template->id, 'status' => 1])
        ->orderBy(['param_order' => SORT_ASC])
        ->all();

    $keys = [];

    foreach ($components as $comp) {
        if ($comp->type === 'BODY') {
            $keys[] = 'param_' . $comp->param_order;
        } elseif ($comp->type === 'HEADER') {
            $keys[] = strtolower($comp->subtype) . '_url';
        } elseif ($comp->type === 'FOOTER') {
            $keys[] = 'footer';
        } elseif ($comp->type === 'BUTTONS') {
            $keys[] = 'button_' . $comp->param_order;
        }
    }

    return [
        'success' => true,
        'template_name' => $templateName,
        'keys' => $keys
    ];
}

}