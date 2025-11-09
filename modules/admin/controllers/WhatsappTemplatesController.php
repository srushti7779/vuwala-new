<?php
namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\WhatsappTemplates;
use app\modules\admin\models\search\WhatsappTemplatesSearch;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\WhatsappTemplateComponents;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile; 

/**
 * WhatsappTemplatesController implements the CRUD actions for WhatsappTemplates model.
 */
class WhatsappTemplatesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'update-status' => ['POST'],
                    'add-whatsapp-template-components' => ['POST'],
                    'import' => ['POST'],
                    'send-test-message' => ['POST'],
                    'upload-excel' => ['POST'],
                    'send-bulk-messages' => ['POST'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-whatsapp-template-components', 'sync-status', 'import', 'send-test-message', 'upload-excel', 'download-example-excel', 'send-bulk-messages', 'download-contact-example'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin();
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'update-status'],
                        'matchCallback' => function () {
                            return User::isManager();
                        }
                    ],
                    [
                        'allow' => false
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all WhatsappTemplates models.
     */
    public function actionIndex()
    {
        $searchModel = new WhatsappTemplatesSearch();
        $dataProvider = (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || 
                         Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN)
            ? $searchModel->search(Yii::$app->request->queryParams)
            : $searchModel->managersearch(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WhatsappTemplates model.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerWhatsappTemplateComponents = new \yii\data\ArrayDataProvider([
            'allModels' => $model->whatsappTemplateComponents,
        ]);
        return $this->render('view', [
            'model' => $model,
            'providerWhatsappTemplateComponents' => $providerWhatsappTemplateComponents,
        ]);
    }

    /**
     * Creates a new WhatsappTemplates model.
     */
    public function actionCreate()
    {
        $model = new WhatsappTemplates();

        if ($model->load(Yii::$app->request->post())) {
            $components = Yii::$app->request->post('WhatsappTemplateComponents', []);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Sanitize and validate model
                $model->name = strtolower(preg_replace('/[^a-z0-9_]/', '', $model->name)); // WhatsApp name format
                if (!$model->validate()) {
                    throw new \Exception('Validation failed: ' . implode(', ', array_keys($model->getErrors())));
                }

                // Validate components
                if (empty($components)) {
                    throw new \Exception('At least one template component is required.');
                }
                foreach ($components as $index => $comp) {
                    if (empty($comp['type']) || empty($comp['default_value'])) {
                        throw new \Exception("Component #{$index} is missing required fields: type and default value.");
                    }
                    if ($comp['type'] === 'header' && empty($comp['subtype'])) {
                        throw new \Exception("Component #{$index} (header) requires a subtype.");
                    }
                }

                // Create template via Graph API
                $fbResponse = $this->createWhatsappTemplateViaGraphApi($model, $components);

                // Save template
                $model->template_status = 'PENDING';
                $model->created_on = date('Y-m-d H:i:s');
                $model->create_user_id = Yii::$app->user->id ?? null;
                if (!$model->save(false)) {
                    throw new \Exception('Failed to save template to database.');
                }

                // Save components
                foreach ($components as $i => $comp) {
                    $component = new WhatsappTemplateComponents([
                        'template_id' => $model->id,
                        'type' => $comp['type'] ?? '',
                        'subtype' => $comp['subtype'] ?? '',
                        'param_order' => $i + 1,
                        'variable_name' => $comp['variable_name'] ?? 'param_' . ($i + 1),
                        'default_value' => $comp['default_value'] ?? '',
                        'is_required' => 1,
                        'status' => 1,
                        'created_on' => date('Y-m-d H:i:s'),
                        'create_user_id' => Yii::$app->user->id ?? null,
                    ]);
                    if (!$component->validate() || !$component->save(false)) {
                        throw new \Exception("Failed to save component #{$i}.");
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Template created and submitted to WhatsApp.');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("WhatsApp Template Create Error: " . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing WhatsappTemplates model.
     * Note: WhatsApp API does not support direct updates, so we delete and recreate.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $components = Yii::$app->request->post('WhatsappTemplateComponents', []);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Sanitize name
                $model->name = strtolower(preg_replace('/[^a-z0-9_]/', '', $model->name));
                if (!$model->validate()) {
                    throw new \Exception('Validation failed: ' . implode(', ', array_keys($model->getErrors())));
                }

                // Validate components
                if (empty($components)) {
                    throw new \Exception('At least one template component is required.');
                }
                foreach ($components as $index => $comp) {
                    if (empty($comp['type']) || empty($comp['default_value'])) {
                        throw new \Exception("Component #{$index} is missing required fields: type and default value.");
                    }
                    if ($comp['type'] === 'header' && empty($comp['subtype'])) {
                        throw new \Exception("Component #{$index} (header) requires a subtype.");
                    }
                }

                // Delete existing template via Graph API
                $this->deleteWhatsappTemplateViaGraphApi($model->name);

                // Create new template
                $fbResponse = $this->createWhatsappTemplateViaGraphApi($model, $components);

                // Update template
                $model->template_status = 'PENDING';
                $model->updated_on = date('Y-m-d H:i:s');
                $model->update_user_id = Yii::$app->user->id ?? null;
                if (!$model->save(false)) {
                    throw new \Exception('Failed to update template in database.');
                }

                // Delete old components and save new ones
                WhatsappTemplateComponents::deleteAll(['template_id' => $model->id]);
                foreach ($components as $i => $comp) {
                    $component = new WhatsappTemplateComponents([
                        'template_id' => $model->id,
                        'type' => $comp['type'] ?? '',
                        'subtype' => $comp['subtype'] ?? '',
                        'param_order' => $i + 1,
                        'variable_name' => $comp['variable_name'] ?? 'param_' . ($i + 1),
                        'default_value' => $comp['default_value'] ?? '',
                        'is_required' => 1,
                        'status' => 1,
                        'created_on' => date('Y-m-d H:i:s'),
                        'create_user_id' => Yii::$app->user->id ?? null,
                    ]);
                    if (!$component->validate() || !$component->save(false)) {
                        throw new \Exception("Failed to save component #{$i}.");
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Template updated and resubmitted to WhatsApp.');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error("WhatsApp Template Update Error: " . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'components' => $model->whatsappTemplateComponents,
        ]);
    }

    /**
     * Deletes an existing WhatsappTemplates model.
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->deleteWhatsappTemplateViaGraphApi($model->name);
            $model->status = WhatsappTemplates::STATUS_DELETE;
            $model->template_status = 'DELETED';
            $model->save(false);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Template deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("WhatsApp Template Delete Error: " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Updates template status (active/inactive).
     */
    public function actionUpdateStatus()
    {
        Yii::$app->response->format = 'json';
        $post = Yii::$app->request->post();
        $data = [];

        if (empty($post['id']) || !isset($post['val'])) {
            $data['message'] = 'Missing required fields: id or status value.';
            return $data;
        }

        $model = WhatsappTemplates::findOne($post['id']);
        if (!$model) {
            $data['message'] = 'Template not found.';
            return $data;
        }

        $model->status = $post['val'];
        if ($model->validate() && $model->save(false)) {
            $data['message'] = 'Status updated successfully.';
            $data['id'] = $model->status;
        } else {
            $data['message'] = 'Failed to update status.';
        }

        return $data;
    }

    /**
     * Syncs template statuses with WhatsApp API.
     */
    public function actionSyncStatus()
    {
        $settings = new WebSetting();
        $accessToken = $settings->getSettingBykey('whatsapp_token');
        $businessId = $settings->getSettingBykey('whatsapp_business_id');

        if (empty($accessToken) || empty($businessId)) {
            Yii::$app->session->setFlash('error', 'Missing WhatsApp API credentials.');
            return $this->redirect(['index']);
        }

        $url = "https://graph.facebook.com/v19.0/{$businessId}/message_templates";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode !== 200 || isset($data['error'])) {
            Yii::error("Sync Status Error: " . json_encode($data), __METHOD__);
            Yii::$app->session->setFlash('error', 'Failed to sync statuses: ' . ($data['error']['message'] ?? 'Unknown error'));
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($data['data'] as $template) {
                $model = WhatsappTemplates::findOne(['name' => $template['name']]);
                if ($model) {
                    $model->template_status = $template['status'];
                    $model->save(false);
                }
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Template statuses synced successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Sync Status DB Error: " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error syncing statuses: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Imports templates from WhatsApp API.
     */
public function actionImport()
{
    Yii::$app->response->format = 'json';

    $settings = new WebSetting();
    $accessToken = $settings->getSettingBykey('whatsapp_token');
    $businessId = $settings->getSettingBykey('whatsapp_business_id');

    if (empty($accessToken) || empty($businessId)) {
        return ['success' => false, 'message' => 'Missing WhatsApp API credentials.'];
    }

    $url = "https://graph.facebook.com/v17.0/{$businessId}/message_templates?fields=name,language,status,category,components";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($httpCode !== 200 || isset($data['error'])) {
        Yii::error("Import Templates Error: " . json_encode($data), __METHOD__);
        return ['success' => false, 'message' => 'Failed to fetch templates: ' . ($data['error']['message'] ?? 'Unknown error')];
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
        foreach ($data['data'] as $template) {
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
                    $component = new WhatsappTemplateComponents();
                    $component->template_id = $model->id;
                    $component->type = $type;
                    $component->subtype = $subtype;
                    $component->param_order = 1; 
                    $component->is_required = 1; 
                    $component->status = 1;
                    $component->created_on = date('Y-m-d H:i:s');
                    $component->create_user_id = Yii::$app->user->id ?? null;

                    // Determine default value based on subtype
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
                        $component = new WhatsappTemplateComponents();
                        $component->template_id = $model->id;
                        $component->type = $type;
                        $component->subtype = '';
                        $component->param_order = $i + 1;
                        $component->variable_name = 'param_' . ($i + 1);
                        $component->default_value = $val;
                        $component->is_required = 1;
                        $component->status = 1;
                        $component->created_on = date('Y-m-d H:i:s');
                        $component->create_user_id = Yii::$app->user->id ?? null;

                        if (!$component->save(false)) {
                            throw new \Exception("Failed to save BODY parameter #{$i} for template: {$template['name']}");
                        }
                    }
                } elseif ($type === 'FOOTER') {
                    $component = new WhatsappTemplateComponents();
                    $component->template_id = $model->id;
                    $component->type = $type;
                    $component->subtype = '';
                    $component->param_order = 1;
                    $component->default_value = $comp['text'] ?? '';
                    $component->is_required = 0;
                    $component->status = 1;
                    $component->created_on = date('Y-m-d H:i:s');
                    $component->create_user_id = Yii::$app->user->id ?? null;

                    if (!$component->save(false)) {
                        throw new \Exception("Failed to save FOOTER component for template: {$template['name']}");
                    }
                } elseif ($type === 'BUTTONS') {
                    foreach ($comp['buttons'] as $buttonIndex => $button) {
                        $component = new WhatsappTemplateComponents();
                        $component->template_id = $model->id;
                        $component->type = $type;
                        $component->subtype = strtoupper($button['type']);
                        $component->param_order = $buttonIndex + 1;
                        $component->variable_name = 'button_' . ($buttonIndex + 1);
                        $component->default_value = $button['text'] ?? '';
                        $component->is_required = 0;
                        $component->status = 1;
                        $component->created_on = date('Y-m-d H:i:s');
                        $component->create_user_id = Yii::$app->user->id ?? null;

                        if (!$component->save(false)) {
                            throw new \Exception("Failed to save BUTTON #{$buttonIndex} for template: {$template['name']}");
                        }
                    }
                }
            }
        }

        $transaction->commit();
        return ['success' => true, 'message' => 'Templates imported successfully.'];
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Import Templates DB Error: " . $e->getMessage(), __METHOD__);
        return ['success' => false, 'message' => 'Error importing templates: ' . $e->getMessage()];
    }
}












    public function actionSendTestMessage($id)
    {
        Yii::$app->response->format = 'json';
        $model = $this->findModel($id);
        $phoneNumber = Yii::$app->request->post('phone_number');
        $parameters = Yii::$app->request->post('parameters', []);

        if (empty($phoneNumber) || !preg_match('/^[0-9]{10,15}$/', $phoneNumber)) {
            return ['success' => false, 'message' => 'Invalid phone number. Please enter a 10-15 digit number without special characters.'];
        }

        if ($model->template_status !== 'APPROVED') {
            return ['success' => false, 'message' => 'Template is not approved for sending messages.'];
        }

        $settings = new WebSetting();
        $accessToken = $settings->getSettingBykey('whatsapp_token');
        $phoneNumberId = $settings->getSettingBykey('whatsapp_phone_number_id');

        if (empty($accessToken) || empty($phoneNumberId)) {
            return ['success' => false, 'message' => 'Missing WhatsApp API credentials.'];
        }

        // Fetch required components
    $components = WhatsappTemplateComponents::find()
    ->where(['template_id' => $model->id, 'status' => 1])
    ->andWhere(['in', 'type', ['BODY', 'HEADER']])
    ->orderBy(['param_order' => SORT_ASC])
    ->all();


        // Prepare payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => $model->name,
                'language' => ['code' => $model->language_code],
                'components' => [],
            ],
        ];

        // Group BODY parameters
        $bodyParams = [];
        foreach ($components as $component) {
            if ($component->type === 'BODY') {
                if (!isset($parameters[$component->id])) {
                    return ['success' => false, 'message' => "Missing required parameter for BODY component '{$component->variable_name}' (order {$component->param_order})."];
                }
                // Validate URL parameters
                if (stripos($component->variable_name, 'url') !== false || preg_match('/^https?:\/\//', $component->default_value)) {
                    if (!filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                        return ['success' => false, 'message' => "Invalid URL for parameter '{$component->variable_name}' (order {$component->param_order})."];
                    }
                }
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => $parameters[$component->id]
                ];
            } elseif ($component->type === 'HEADER') {
                $compData = ['type' => 'header'];
                if ($component->subtype === 'IMAGE') {
                    if (!isset($parameters[$component->id]) || !filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
                        return ['success' => false, 'message' => "Invalid image URL for HEADER parameter (order {$component->param_order})."];
                    }
                    $compData['parameters'] = [
                        [
                            'type' => 'image',
                            'image' => ['link' => $parameters[$component->id]]
                        ]
                    ];
                } 
                if ($component->subtype === 'TEXT') {
                    if (!isset($parameters[$component->id])) {
                        return ['success' => false, 'message' => "Missing required parameter for HEADER component (order {$component->param_order})."];
                    }
                    $compData['parameters'] = [
                        [
                            'type' => 'text',
                            'text' => $parameters[$component->id]
                        ]
                    ];
                }




if ($component->subtype === 'VIDEO') {
    if (!isset($parameters[$component->id]) || !filter_var($parameters[$component->id], FILTER_VALIDATE_URL)) {
        return ['success' => false, 'message' => "Invalid video URL for HEADER parameter (order {$component->param_order})."];
    }
    $compData['parameters'] = [
        [
            'type' => 'video',
            'video' => ['link' => $parameters[$component->id]]
        ]
    ];
}

                $payload['template']['components'][] = $compData;
            }
        }

        // Add BODY component if parameters exist
        if (!empty($bodyParams)) {
            $payload['template']['components'][] = [
                'type' => 'body',
                'parameters' => $bodyParams
            ];
        }

        // Log payload for debugging
        Yii::info("Send Test Message Payload: " . json_encode($payload), __METHOD__);

        // Send request
        $url = "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "cURL Error: {$error_msg}"];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);

        if ($httpCode !== 200 || isset($data['error'])) {
            Yii::error("Send Test Message Error: " . json_encode($data), __METHOD__);
            return ['success' => false, 'message' => 'Failed to send test message: ' . ($data['error']['message'] ?? 'Unknown error')];
        }

        return ['success' => true, 'message' => 'Test message sent successfully to ' . $phoneNumber];
    }

















    /**
     * Creates WhatsApp template via Graph API.
     */
    protected function createWhatsappTemplateViaGraphApi($model, $components)
    {
        $settings = new WebSetting();
        $accessToken = $settings->getSettingBykey('whatsapp_token');
        $businessId = $settings->getSettingBykey('whatsapp_business_id');

        if (empty($accessToken)) {
            throw new \Exception('WhatsApp access token is missing.');
        }
        if (empty($businessId) || !is_numeric($businessId)) {
            throw new \Exception('Invalid or missing WhatsApp Business ID.');
        }

        $payload = [
            'name' => $model->name,
            'language' => $model->language_code,
            'category' => $model->category,
            'components' => [],
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

        $url = "https://graph.facebook.com/v19.0/{$businessId}/message_templates";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: {$error_msg}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            $errorMessages = [
                131009 => 'Template name already exists.',
                190 => 'Invalid access token.',
            ];
            $error = $data['error'];
            $message = $errorMessages[$error['code'] ?? 0] ?? ($error['message'] ?? 'Unknown error');
            throw new \Exception("Template creation failed: {$message}");
        }

        if ($httpCode === 200 && isset($data['id'])) {
            return $data;
        }

        throw new \Exception('Template creation failed: ' . json_encode($data));
    }

    /**
     * Deletes WhatsApp template via Graph API.
     */
    protected function deleteWhatsappTemplateViaGraphApi($templateName)
    {
        if (empty($templateName)) {
            throw new \Exception('Template name is missing.');
        }

        $settings = new WebSetting();
        $accessToken = $settings->getSettingBykey('whatsapp_token');
        $businessId = $settings->getSettingBykey('whatsapp_business_id');

        if (empty($accessToken)) {
            throw new \Exception('WhatsApp access token is missing.');
        }
        if (empty($businessId) || !is_numeric($businessId)) {
            throw new \Exception('Invalid or missing WhatsApp Business ID.');
        }

        $url = "https://graph.facebook.com/v19.0/{$businessId}/message_templates?name={$templateName}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
            ],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: {$error_msg}");
        }

        curl_close($ch);
        $data = json_decode($response, true);

        if (!isset($data['success']) || !$data['success']) {
            throw new \Exception('Failed to delete template: ' . json_encode($data));
        }
    }

    /**
     * Finds the WhatsappTemplates model by ID.
     */
    protected function findModel($id)
    {
        if (($model = WhatsappTemplates::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Renders AJAX form for adding template components.
     */
    public function actionAddWhatsappTemplateComponents()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('WhatsappTemplateComponents', []);
            if (!empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || 
                Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }
            return $this->renderAjax('_formWhatsappTemplateComponents', ['row' => $row]);
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Upload Excel file for WhatsApp template data
     */
    public function actionUploadExcel()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $templateId = Yii::$app->request->post('template_id');
            $file = UploadedFile::getInstanceByName('excel_file');

            if (!$file) {
                throw new \Exception('No file uploaded.');
            }

            if (!in_array($file->extension, ['xlsx', 'xls', 'csv'])) {
                throw new \Exception('Invalid file format. Only .xlsx, .xls and .csv files are allowed.');
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = Yii::getAlias('@webroot/uploads/temp');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Save uploaded file temporarily
            $tempFile = $uploadDir . '/' . uniqid() . '.' . $file->extension;
            if (!$file->saveAs($tempFile)) {
                throw new \Exception('Failed to save uploaded file.');
            }

            $rows = [];
            
            // Read file based on extension
            if ($file->extension === 'csv') {
                // Handle CSV files
                if (($handle = fopen($tempFile, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                // For Excel files, try to use a simple reader or convert
                // For now, suggest users to use CSV format
                throw new \Exception('Excel format not yet supported. Please convert your file to CSV format and try again.');
            }

            if (empty($rows)) {
                throw new \Exception('File is empty or could not be read.');
            }

            // Expected headers: Component Type, Subtype, Order, Default Value, Variable Name, Required
            $expectedHeaders = ['component_type', 'subtype', 'param_order', 'default_value', 'variable_name', 'is_required'];
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Validate headers
            foreach ($expectedHeaders as $header) {
                if (!in_array($header, $headers)) {
                    throw new \Exception("Missing required column: " . $header);
                }
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Process data rows (skip header row)
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    if (empty(array_filter($row))) {
                        continue; // Skip empty rows
                    }

                    try {
                        $component = new WhatsappTemplateComponents();
                        $component->template_id = $templateId;
                        $component->type = isset($row[array_search('component_type', $headers)]) ? trim($row[array_search('component_type', $headers)]) : '';
                        $component->subtype = isset($row[array_search('subtype', $headers)]) ? trim($row[array_search('subtype', $headers)]) : null;
                        $component->param_order = isset($row[array_search('param_order', $headers)]) ? (int)$row[array_search('param_order', $headers)] : 0;
                        $component->default_value = isset($row[array_search('default_value', $headers)]) ? trim($row[array_search('default_value', $headers)]) : null;
                        $component->variable_name = isset($row[array_search('variable_name', $headers)]) ? trim($row[array_search('variable_name', $headers)]) : null;
                        $component->is_required = isset($row[array_search('is_required', $headers)]) ? (strtolower(trim($row[array_search('is_required', $headers)])) === 'yes' || trim($row[array_search('is_required', $headers)]) === '1') : 0;
                        $component->status = 1;
                        $component->created_at = date('Y-m-d H:i:s');
                        $component->updated_at = date('Y-m-d H:i:s');

                        if ($component->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Row " . ($i + 1) . ": " . implode(', ', $component->getFirstErrors());
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
                    }
                }

                $transaction->commit();

                // Clean up temp file
                unlink($tempFile);

                $message = "Upload completed. Success: {$successCount}, Errors: {$errorCount}";
                if (!empty($errors)) {
                    $message .= "\nErrors: " . implode("\n", array_slice($errors, 0, 10)); // Show first 10 errors
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'successCount' => $successCount,
                    'errorCount' => $errorCount
                ];

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Download example Excel format
     */
    public function actionDownloadExampleExcel()
    {
        try {
            // Create CSV content
            $headers = [
                'component_type',
                'subtype', 
                'param_order',
                'default_value',
                'variable_name',
                'is_required'
            ];

            // Add example data
            $exampleData = [
                ['HEADER', 'TEXT', '1', 'Welcome {{1}}!', 'customer_name', 'Yes'],
                ['BODY', '', '1', 'Thank you for choosing {{1}}. Your order {{2}} is confirmed.', 'business_name', 'Yes'],
                ['BODY', '', '2', '', 'order_number', 'Yes'],
                ['FOOTER', '', '1', 'Visit our website for more information', '', 'No'],
                ['BUTTON', 'URL', '1', 'https://example.com', 'website_url', 'No']
            ];

            // Create CSV content
            $csvContent = '';
            $csvContent .= implode(',', $headers) . "\n";
            
            foreach ($exampleData as $row) {
                $csvContent .= '"' . implode('","', $row) . '"' . "\n";
            }

            // Set headers for download
            $filename = 'whatsapp_template_components_example.csv';
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', 'text/csv');
            Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');
            Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

            return $csvContent;

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Failed to generate example file: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Send bulk WhatsApp messages from uploaded contact file with humanized timing
     */
    public function actionSendBulkMessages()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $templateId = Yii::$app->request->post('template_id');
            $file = UploadedFile::getInstanceByName('contact_file');
            $bulkParameters = Yii::$app->request->post('bulk_parameters', []);

            if (!$file) {
                throw new \Exception('No contact file uploaded.');
            }

            if (!in_array($file->extension, ['xlsx', 'xls', 'csv'])) {
                throw new \Exception('Invalid file format. Only .xlsx, .xls and .csv files are allowed.');
            }

            // Get template model
            $template = $this->findModel($templateId);
            
            // Check if template is approved
            if ($template->template_status !== 'APPROVED') {
                throw new \Exception('Template must be approved before sending bulk messages.');
            }
            
            // Get ALL components to validate bulk parameters
            $bodyComponents = WhatsappTemplateComponents::find()
                ->where(['template_id' => $templateId, 'status' => 1, 'type' => 'BODY'])
                ->orderBy(['param_order' => SORT_ASC])
                ->all();
                
            $headerComponents = WhatsappTemplateComponents::find()
                ->where(['template_id' => $templateId, 'status' => 1, 'type' => 'HEADER'])
                ->orderBy(['param_order' => SORT_ASC])
                ->all();

            // Validate that all required parameters are provided in UI
            $missingParams = [];
            
            // Check BODY parameters
            foreach ($bodyComponents as $component) {
                if (!isset($bulkParameters[$component->id]) || empty(trim($bulkParameters[$component->id]))) {
                    $missingParams[] = $component->variable_name ?: 'BODY param_' . $component->param_order;
                }
            }
            
            // Check required HEADER parameters (especially for media components)
            foreach ($headerComponents as $component) {
                if ($component->is_required && (!isset($bulkParameters[$component->id]) || empty(trim($bulkParameters[$component->id])))) {
                    $paramName = $component->variable_name ?: $component->subtype . ' param_' . $component->param_order;
                    $missingParams[] = 'HEADER ' . $paramName;
                }
            }
            
            if (!empty($missingParams)) {
                throw new \Exception('Missing required parameters: ' . implode(', ', $missingParams));
            }

            // Log bulk parameters for debugging
            Yii::info("Bulk messaging started with parameters: " . json_encode($bulkParameters), __METHOD__);

            // Create uploads directory if it doesn't exist
            $uploadDir = Yii::getAlias('@webroot/uploads/temp');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Save uploaded file temporarily
            $tempFile = $uploadDir . '/' . uniqid() . '.' . $file->extension;
            if (!$file->saveAs($tempFile)) {
                throw new \Exception('Failed to save uploaded file.');
            }

            $rows = [];
            
            // Read file based on extension
            if ($file->extension === 'csv') {
                // Handle CSV files
                if (($handle = fopen($tempFile, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                // For Excel files, suggest using CSV format
                throw new \Exception('Excel format not yet supported. Please convert your file to CSV format and try again.');
            }

            if (empty($rows)) {
                throw new \Exception('Contact file is empty or could not be read.');
            }

            // Expected headers: mobile_number only
            $expectedHeaders = ['mobile_number'];
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Validate that mobile_number column exists
            if (!in_array('mobile_number', $headers)) {
                throw new \Exception("Missing required column: mobile_number");
            }

            $successCount = 0;
            $errorCount = 0;
            $skippedCount = 0;
            $errors = [];
            $processedNumbers = [];
            $totalContacts = count($rows) - 1; // Exclude header row

            // Initialize bulk sending session
            $sessionId = uniqid('bulk_', true);
            $startTime = microtime(true);
            
            Yii::info("Starting bulk WhatsApp message sending. Session: {$sessionId}, Template: {$template->name}, Total contacts: {$totalContacts}", __METHOD__);

            // Process data rows (skip header row)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                if (empty(array_filter($row))) {
                    $skippedCount++;
                    continue; // Skip empty rows
                }

                try {
                    // Get mobile number
                    $mobileIndex = array_search('mobile_number', $headers);
                    $mobileNumber = isset($row[$mobileIndex]) ? trim($row[$mobileIndex]) : '';
                    
                    if (empty($mobileNumber)) {
                        $errorCount++;
                        $errors[] = "Row " . ($i + 1) . ": Missing mobile number";
                        continue;
                    }

                        // Convert scientific notation (like 9.16282E+11) to full number
                        if (is_numeric($mobileNumber) && strpos(strtolower($mobileNumber), 'e') !== false) {
                            $mobileNumber = number_format($mobileNumber, 0, '', '');
                        }

                        // Remove all non-digit characters (e.g., spaces, dashes, plus signs)
                        $mobileNumber = preg_replace('/\D/', '', $mobileNumber);
                        
                        // Validate mobile number format
                        if (!preg_match('/^[0-9]{10,15}$/', $mobileNumber)) {
                            $errorCount++;
                            $errors[] = "Row " . ($i + 1) . ": Invalid mobile number format: " . $mobileNumber;
                            continue;
                        }
                  

                    // Check for duplicates in current batch
                    if (in_array($mobileNumber, $processedNumbers)) {
                        $skippedCount++;
                        $errors[] = "Row " . ($i + 1) . ": Duplicate mobile number: " . $mobileNumber;
                        continue;
                    }
                    $processedNumbers[] = $mobileNumber;

                    // Build parameters array using bulk parameters from UI only
                    $parameters = [];
                    
                    // Add BODY parameters from UI form
                    foreach ($bodyComponents as $component) {
                        if (isset($bulkParameters[$component->id])) {
                            $parameters[$component->id] = $bulkParameters[$component->id];
                        }
                    }
                    
                    // Add ALL HEADER parameters from UI form (not from Excel)
                    $allHeaderComponents = WhatsappTemplateComponents::find()
                        ->where(['template_id' => $templateId, 'status' => 1, 'type' => 'HEADER'])
                        ->orderBy(['param_order' => SORT_ASC])
                        ->all();
                    
                    foreach ($allHeaderComponents as $component) {
                        if (isset($bulkParameters[$component->id])) {
                            // Use UI parameter value
                            $parameters[$component->id] = $bulkParameters[$component->id];
                        } else {
                            // Use default value if no UI parameter provided
                            $parameters[$component->id] = $component->default_value ?: '';
                        }
                    }

                    // Log message attempt
                    $messageNumber = $successCount + $errorCount + 1;
                    Yii::info("Sending message {$messageNumber}/{$totalContacts} to {$mobileNumber}", __METHOD__);

                    // Send WhatsApp message with enhanced tracking
                    $result = $this->sendWhatsAppMessageWithTracking($template, $mobileNumber, $parameters, $sessionId, $i);
                    
                    if ($result['success']) {
                        $successCount++;
                        Yii::info("Message sent successfully to {$mobileNumber}. Message ID: " . ($result['messageId'] ?? 'unknown'), __METHOD__);
                    } else {
                        $errorCount++;
                        $errors[] = "Row " . ($i + 1) . " (Mobile: " . $mobileNumber . "): " . $result['message'];
                        Yii::warning("Failed to send message to {$mobileNumber}: " . $result['message'], __METHOD__);
                    }

                    // Implement humanized delays to avoid spam detection
                    $this->addHumanizedDelay($successCount + $errorCount, $totalContacts);

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
                    Yii::error("Exception while processing row " . ($i + 1) . ": " . $e->getMessage(), __METHOD__);
                }
            }

            // Clean up temp file
            unlink($tempFile);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            // Log completion
            Yii::info("Bulk messaging completed. Session: {$sessionId}, Duration: {$duration}s, Success: {$successCount}, Errors: {$errorCount}, Skipped: {$skippedCount}", __METHOD__);

            $message = "Bulk messaging completed in {$duration} seconds.\n";
            $message .= "✅ Successfully sent: {$successCount} messages\n";
            $message .= "❌ Failed: {$errorCount} messages\n";
            $message .= "⏭️ Skipped: {$skippedCount} contacts\n";
            $message .= "📊 Total processed: " . ($successCount + $errorCount + $skippedCount) . " out of {$totalContacts} contacts\n";
            
            // Add bulk parameters info
            if (!empty($bulkParameters)) {
                $message .= "\n📝 Parameters used from UI:\n";
                foreach ($bodyComponents as $component) {
                    if (isset($bulkParameters[$component->id])) {
                        $paramName = $component->variable_name ?: 'BODY param_' . $component->param_order;
                        $message .= "   • {$paramName}: " . $bulkParameters[$component->id] . "\n";
                    }
                }
                foreach ($headerComponents as $component) {
                    if (isset($bulkParameters[$component->id])) {
                        $paramName = $component->variable_name ?: $component->subtype . ' param_' . $component->param_order;
                        $message .= "   • HEADER {$paramName}: " . $bulkParameters[$component->id] . "\n";
                    }
                }
            }
            
            if (!empty($errors)) {
                $message .= "\n\n🔍 First 10 errors:\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= "\n... and " . (count($errors) - 10) . " more errors";
                }
            }

            return [
                'success' => true,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => $errorCount,
                'skippedCount' => $skippedCount,
                'totalContacts' => $totalContacts,
                'duration' => $duration,
                'sessionId' => $sessionId
            ];

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            Yii::error("Bulk messaging error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Download contact example CSV format
     */
    public function actionDownloadContactExample($id)
    {
        try {
            $template = $this->findModel($id);

            // Simple CSV format - only mobile numbers
            $headers = ['mobile_number'];

            // Simple example data - only mobile numbers
            $exampleData = [
                ['`1234567890'],
                ['`1234567891']
            ];

            // Create CSV content
            $csvContent = '';
            $csvContent .= implode(',', $headers) . "\n";
            
            foreach ($exampleData as $row) {
                $csvContent .= '"' . implode('","', $row) . '"' . "\n";
            }

            // Set headers for download
            $filename = 'whatsapp_bulk_contacts_' . $template->name . '.csv';
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename); // Clean filename
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', 'text/csv');
            Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');
            Yii::$app->response->headers->add('Cache-Control', 'max-age=0');

            return $csvContent;

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Failed to generate contact example file: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    /**
     * Send WhatsApp message using the configured API with enhanced tracking
     */
    private function sendWhatsAppMessageWithTracking($template, $phoneNumber, $parameters = [], $sessionId = null, $rowIndex = 0)
    {
        try {
            // Get WhatsApp API settings
            $settings = new WebSetting();
            $accessToken = $settings->getSettingBykey('whatsapp_token');
            $phoneNumberId = $settings->getSettingBykey('whatsapp_phone_number_id');

            if (empty($accessToken) || empty($phoneNumberId)) {
                return ['success' => false, 'message' => 'WhatsApp API credentials not configured. Please check access token and phone number ID.'];
            }

            // Prepare template parameters for the Graph API format
            $templateComponents = [];
            
            if (!empty($parameters)) {
                $bodyParams = [];
                $headerParams = [];
                
                // Get components to determine parameter structure
                $components = WhatsappTemplateComponents::find()
                    ->where(['template_id' => $template->id, 'status' => 1])
                    ->orderBy(['param_order' => SORT_ASC])
                    ->all();
                
                foreach ($components as $component) {
                    if (isset($parameters[$component->id])) {
                        if ($component->type === 'BODY') {
                            $bodyParams[] = [
                                'type' => 'text',
                                'text' => $parameters[$component->id]
                            ];
                        } elseif ($component->type === 'HEADER') {
                            if ($component->subtype === 'IMAGE') {
                                $headerParams[] = [
                                    'type' => 'image',
                                    'image' => ['link' => $parameters[$component->id]]
                                ];
                            } elseif ($component->subtype === 'VIDEO') {
                                $headerParams[] = [
                                    'type' => 'video',
                                    'video' => ['link' => $parameters[$component->id]]
                                ];
                            } elseif ($component->subtype === 'DOCUMENT') {
                                $headerParams[] = [
                                    'type' => 'document',
                                    'document' => ['link' => $parameters[$component->id]]
                                ];
                            } else {
                                $headerParams[] = [
                                    'type' => 'text',
                                    'text' => $parameters[$component->id]
                                ];
                            }
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
                    ]
                ]
            ];

            if (!empty($templateComponents)) {
                $requestData['template']['components'] = $templateComponents;
            }

            // Add tracking metadata
            if ($sessionId) {
                $requestData['biz_opaque_callback_data'] = json_encode([
                    'session_id' => $sessionId,
                    'row_index' => $rowIndex,
                    'timestamp' => time()
                ]);
            }

            // Log request for debugging (remove sensitive data)
            $logData = $requestData;
            Yii::info("WhatsApp API Request for {$phoneNumber}: " . json_encode($logData), __METHOD__);

            // Send API request
            $url = "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!empty($curlError)) {
                return ['success' => false, 'message' => "Network error: {$curlError}"];
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['messages']) && !empty($responseData['messages'])) {
                $messageId = $responseData['messages'][0]['id'] ?? 'unknown';
                return [
                    'success' => true, 
                    'message' => 'Message sent successfully',
                    'messageId' => $messageId,
                    'response' => $responseData
                ];
            } else {
                $errorMessage = 'Unknown error';
                if (isset($responseData['error'])) {
                    $errorMessage = $responseData['error']['message'] ?? 'API Error';
                    if (isset($responseData['error']['error_subcode'])) {
                        $errorMessage .= " (Code: {$responseData['error']['error_subcode']})";
                    }
                } elseif (!empty($response)) {
                    $errorMessage = "API returned: " . substr($response, 0, 200);
                }
                
                return [
                    'success' => false, 
                    'message' => $errorMessage,
                    'httpCode' => $httpCode,
                    'response' => $responseData
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Add humanized delays between messages to avoid spam detection
     */
    private function addHumanizedDelay($messageCount, $totalMessages)
    {
        // Base delay between messages (microseconds)
        $baseDelay = 2000000; // 2 seconds
        
        // Add random variation to make it more human-like (0.5 - 1.5 seconds)
        $randomDelay = rand(500000, 1500000);
        
        // Progressive delay - longer delays as we send more messages
        $progressiveDelay = 0;
        if ($messageCount > 50) {
            $progressiveDelay = 1000000; // Extra 1 second after 50 messages
        }
        if ($messageCount > 100) {
            $progressiveDelay = 2000000; // Extra 2 seconds after 100 messages
        }
        
        // Batch delays - longer pauses every 25 messages
        if ($messageCount % 25 === 0 && $messageCount > 0) {
            $batchDelay = 5000000; // 5 second pause every 25 messages
            Yii::info("Batch pause: Processed {$messageCount} messages, taking 5-second break", __METHOD__);
            usleep($batchDelay);
        }
        
        // Calculate total delay
        $totalDelay = $baseDelay + $randomDelay + $progressiveDelay;
        
        // Apply delay
        usleep($totalDelay);
        
        // Log timing for monitoring
        if ($messageCount % 10 === 0) {
            $delaySeconds = round($totalDelay / 1000000, 2);
            Yii::info("Message timing: #{$messageCount}/{$totalMessages}, delay: {$delaySeconds}s", __METHOD__);
        }
    }

    /**
     * Send WhatsApp message using the configured API (legacy method for backward compatibility)
     */
    private function sendWhatsAppMessage($template, $phoneNumber, $parameters = [])
    {
        return $this->sendWhatsAppMessageWithTracking($template, $phoneNumber, $parameters);
    }
}