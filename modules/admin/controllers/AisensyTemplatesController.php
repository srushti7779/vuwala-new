<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\AisensyTemplates;
use app\modules\admin\models\AisensyTemplateComponents;
use app\modules\admin\models\AisensyTemplateLinks;
use app\modules\admin\models\AisensyBulkCampaignLog;
use app\modules\admin\models\AisensyBulkMessageLog;
use app\modules\admin\models\search\AisensyTemplatesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\modules\admin\models\WebSetting;
use yii\httpclient\Client;


/**
 * AisensyTemplatesController implements the CRUD actions for AisensyTemplates model.
 */
class AisensyTemplatesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'delete-template' => ['post'],
                    'upload-media' => ['post'],
                    'send-test-message' => ['post'],
                    'process-bulk-upload' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'delete-template', 'update-status', 'add-aisensy-template-components', 'add-aisensy-template-links', 'import-templates', 'upload-media', 'send-test-message', 'bulk-message', 'download-sample-excel', 'process-bulk-upload'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status', 'upload-media', 'send-test-message', 'bulk-message', 'download-sample-excel', 'process-bulk-upload'],
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
     * Lists all AisensyTemplates models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AisensyTemplatesSearch();
        if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN){
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if(\Yii::$app->user->identity->user_role==User::ROLE_MANAGER){
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
 
    /**
     * Displays a single AisensyTemplates model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerAisensyTemplateComponents = new \yii\data\ArrayDataProvider([
            'allModels' => $model->aisensyTemplateComponents,
        ]);
        $providerAisensyTemplateLinks = new \yii\data\ArrayDataProvider([
            'allModels' => $model->aisensyTemplateLinks,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerAisensyTemplateComponents' => $providerAisensyTemplateComponents,
            'providerAisensyTemplateLinks' => $providerAisensyTemplateLinks,
        ]);
    }

    /**
     * Creates a new AisensyTemplates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AisensyTemplates();

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AisensyTemplates model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AisensyTemplates model.
     * Also attempts to delete from AiSensy API if template name is available.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $errors = [];
        $apiDeleted = false;
        
        if (!empty($model)) {
            // Try to delete from AiSensy API first
            if (!empty($model->name)) {
                try {
                    // Get AiSensy bearer token from settings
                    $setting = new WebSetting();
                    $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
                    
                    if (!empty($aisensy_wa_key)) {
                        // Try using HTTP client first, fallback to cURL if not available
                        if (class_exists('yii\httpclient\Client')) {
                            // Create HTTP client
                            $client = new Client();
                            
                            // Make API request to delete template
                            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setUrl('https://backend.aisensy.com/direct-apis/t1/wa_template/' . urlencode($model->name))
                                ->addHeaders([
                                    'Accept' => 'application/json',
                                    'Authorization' => 'Bearer ' . $aisensy_wa_key
                                ])
                                ->send();

                            if ($response->isOk) {
                                $apiDeleted = true;
                            } else {
                                $errors[] = 'Failed to delete from AiSensy API. Status: ' . $response->statusCode;
                            }
                        } else {
                            // Fallback to cURL
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/wa_template/' . urlencode($model->name),
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_CUSTOMREQUEST => 'DELETE',
                                CURLOPT_HTTPHEADER => [
                                    'Accept: application/json',
                                    'Authorization: Bearer ' . $aisensy_wa_key
                                ],
                            ]);
                            
                            $response = curl_exec($curl);
                            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                            curl_close($curl);
                            
                            if ($httpCode >= 200 && $httpCode < 300) {
                                $apiDeleted = true;
                            } else {
                                $errors[] = 'Failed to delete from AiSensy API. Status: ' . $httpCode;
                            }
                        }
                    } else {
                        $errors[] = 'AiSensy API key not configured';
                    }
                } catch (\Exception $e) {
                    $errors[] = 'API deletion error: ' . $e->getMessage();
                    Yii::error('AiSensy template deletion failed: ' . $e->getMessage(), __METHOD__);
                }
            }
            
            // Always delete/mark as deleted in local database
            $model->status = AisensyTemplates::STATUS_DELETE;
            $model->save(false);
            
            // Set flash message based on results
            if ($apiDeleted) {
                Yii::$app->session->setFlash('success', 'Template deleted successfully from both local database and AiSensy API.');
            } elseif (!empty($errors)) {
                Yii::$app->session->setFlash('warning', 'Template deleted from local database, but API deletion failed: ' . implode('; ', $errors));
            } else {
                Yii::$app->session->setFlash('success', 'Template deleted successfully from local database.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * AJAX action to delete template from both local DB and AiSensy API
     * @return array JSON response
     */
    public function actionDeleteTemplate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        if (empty($id)) {
            return [
                'success' => false,
                'message' => 'Template ID is required'
            ];
        }
        
        try {
            $model = $this->findModel($id);
            $errors = [];
            $apiDeleted = false;
            
            // Try to delete from AiSensy API first
            if (!empty($model->name)) {
                try {
                    // Get AiSensy bearer token from settings
                    $setting = new WebSetting();
                    $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
                    
                    if (!empty($aisensy_wa_key)) {
                        // Try using HTTP client first, fallback to cURL if not available
                        if (class_exists('yii\httpclient\Client')) {
                            // Create HTTP client
                            $client = new Client();
                            
                            // Make API request to delete template
                            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setUrl('https://backend.aisensy.com/direct-apis/t1/wa_template/' . urlencode($model->name))
                                ->addHeaders([
                                    'Accept' => 'application/json',
                                    'Authorization' => 'Bearer ' . $aisensy_wa_key
                                ])
                                ->send();

                            if ($response->isOk) {
                                $apiDeleted = true;
                            } else {
                                $errors[] = 'Failed to delete from AiSensy API. Status: ' . $response->statusCode;
                            }
                        } else {
                            // Fallback to cURL
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/wa_template/' . urlencode($model->name),
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_CUSTOMREQUEST => 'DELETE',
                                CURLOPT_HTTPHEADER => [
                                    'Accept: application/json',
                                    'Authorization: Bearer ' . $aisensy_wa_key
                                ],
                            ]);
                            
                            $response = curl_exec($curl);
                            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                            curl_close($curl);
                            
                            if ($httpCode >= 200 && $httpCode < 300) {
                                $apiDeleted = true;
                            } else {
                                $errors[] = 'Failed to delete from AiSensy API. Status: ' . $httpCode;
                            }
                        }
                    } else {
                        $errors[] = 'AiSensy API key not configured';
                    }
                } catch (\Exception $e) {
                    $errors[] = 'API deletion error: ' . $e->getMessage();
                    Yii::error('AiSensy template deletion failed: ' . $e->getMessage(), __METHOD__);
                }
            }
            
            // Always delete/mark as deleted in local database
            $model->status = AisensyTemplates::STATUS_DELETE;
            if ($model->save(false)) {
                if ($apiDeleted) {
                    return [
                        'success' => true,
                        'message' => 'Template deleted successfully from both local database and AiSensy API.',
                        'apiDeleted' => true
                    ];
                } elseif (!empty($errors)) {
                    return [
                        'success' => true,
                        'message' => 'Template deleted from local database, but API deletion failed: ' . implode('; ', $errors),
                        'apiDeleted' => false,
                        'apiErrors' => $errors
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => 'Template deleted successfully from local database.',
                        'apiDeleted' => false
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete template from local database: ' . implode(', ', $model->getFirstErrors())
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error('Template deletion failed: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ];
        }
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = AisensyTemplates::find()->where([
				'id' => $post['id'],
			])->one();
			if(!empty($model)){

                $model->status = $post['val'];
              
               
			}
			if($model->save(false)){
				$data['message'] = "Updated";
                $data['id'] = $model->status ;
			}else{
				$data['message'] = "Not Updated";
                
			}

	}
	return $data;
}

    
    /**
     * Finds the AisensyTemplates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AisensyTemplates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AisensyTemplates::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for AisensyTemplateComponents
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddAisensyTemplateComponents()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('AisensyTemplateComponents');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formAisensyTemplateComponents', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for AisensyTemplateLinks
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddAisensyTemplateLinks()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('AisensyTemplateLinks');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formAisensyTemplateLinks', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Import templates from AiSensy API
     * Requires yii2-httpclient extension: composer require yiisoft/yii2-httpclient
     * @return mixed
     */
    public function actionImportTemplates()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                throw new \Exception('AiSensy API key not configured. Please set the aisensy_wa_key in web settings.');
            }
            
            // Try using HTTP client first, fallback to cURL if not available
            if (class_exists('yii\httpclient\Client')) {
                // Create HTTP client
                $client = new Client();
                
                // Make API request
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl('https://backend.aisensy.com/direct-apis/t1/get-templates')
                    ->addHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $aisensy_wa_key
                    ])
                    ->send();

                if (!$response->isOk) {
                    throw new \Exception('Failed to fetch templates from AiSensy API. Status: ' . $response->statusCode);
                }

                $data = $response->data;
            } else {
                // Fallback to cURL
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/get-templates',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Authorization: Bearer ' . $aisensy_wa_key
                    ],
                ]);
                
                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                
                if ($httpCode !== 200) {
                    throw new \Exception('Failed to fetch templates from AiSensy API. Status: ' . $httpCode);
                }
                
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from AiSensy API');
                }
            }
            $importedCount = 0;
            $updatedCount = 0;
            $errors = [];

            if (isset($data['data']) && is_array($data['data'])) {
                $transaction = Yii::$app->db->beginTransaction();
                
                try {
                    foreach ($data['data'] as $templateData) {
                        // Check if template already exists
                        $existingTemplate = AisensyTemplates::find()
                            ->where(['external_id' => $templateData['id']])
                            ->one();

                        $isUpdate = false;
                        if ($existingTemplate) {
                            $template = $existingTemplate;
                            $isUpdate = true;
                        } else {
                            $template = new AisensyTemplates();
                        }

                        // Set template attributes
                        $template->external_id = $templateData['id'];
                        $template->name = $templateData['name'];
                        $template->category = $templateData['category'] ?? 'MARKETING';
                        $template->language = $templateData['language'] ?? 'en';
                        $template->status = ($templateData['status'] === 'APPROVED') ? AisensyTemplates::STATUS_ACTIVE : AisensyTemplates::STATUS_INACTIVE;
                        $template->rejected_reason = $templateData['rejected_reason'] ?? null;
                        
                        // Handle quality score
                        if (isset($templateData['quality_score'])) {
                            $template->quality_score = json_encode($templateData['quality_score']);
                        }

                        // Extract body text and footer text from components
                        $bodyText = '';
                        $footerText = '';
                        
                        if (isset($templateData['components']) && is_array($templateData['components'])) {
                            foreach ($templateData['components'] as $component) {
                                if ($component['type'] === 'BODY' && isset($component['text'])) {
                                    $bodyText = $component['text'];
                                } elseif ($component['type'] === 'FOOTER' && isset($component['text'])) {
                                    $footerText = $component['text'];
                                }
                            }
                        }
                        
                        $template->body_text = $bodyText;
                        $template->footer_text = $footerText;
                        
                        // Store full template data in meta
                        $template->meta = json_encode($templateData);

                        if (!$template->save()) {
                            $errors[] = "Failed to save template '{$templateData['name']}': " . implode(', ', $template->getFirstErrors());
                            continue;
                        }

                        // Delete existing components and links if updating
                        if ($isUpdate) {
                            AisensyTemplateComponents::deleteAll(['template_id' => $template->id]);
                            AisensyTemplateLinks::deleteAll(['template_id' => $template->id]);
                        }

                        // Save components
                        if (isset($templateData['components']) && is_array($templateData['components'])) {
                            foreach ($templateData['components'] as $index => $componentData) {
                                $component = new AisensyTemplateComponents();
                                $component->template_id = $template->id;
                                $component->component_index = $index;
                                $component->type = $componentData['type'];
                                $component->format = $componentData['format'] ?? null;
                                $component->text = $componentData['text'] ?? null;
                                
                                if (isset($componentData['example'])) {
                                    $component->example = json_encode($componentData['example']);
                                }
                                
                                if (isset($componentData['buttons'])) {
                                    $component->buttons = json_encode($componentData['buttons']);
                                    
                                    // Extract links for the links table
                                    foreach ($componentData['buttons'] as $button) {
                                        if (isset($button['type']) && in_array($button['type'], ['PHONE_NUMBER', 'URL'])) {
                                            $link = new AisensyTemplateLinks();
                                            $link->template_id = $template->id;
                                            $link->type = $button['type'];
                                            $link->label = $button['text'] ?? '';
                                            
                                            if ($button['type'] === 'PHONE_NUMBER') {
                                                $link->value = $button['phone_number'] ?? '';
                                            } elseif ($button['type'] === 'URL') {
                                                $link->value = $button['url'] ?? '';
                                            }
                                            
                                            if (!$link->save()) {
                                                $errors[] = "Failed to save link for template '{$templateData['name']}': " . implode(', ', $link->getFirstErrors());
                                            }
                                        }
                                    }
                                }
                                
                                $component->raw = json_encode($componentData);
                                
                                if (!$component->save()) {
                                    $errors[] = "Failed to save component for template '{$templateData['name']}': " . implode(', ', $component->getFirstErrors());
                                }
                            }
                        }

                        if ($isUpdate) {
                            $updatedCount++;
                        } else {
                            $importedCount++;
                        }
                    }
                    
                    $transaction->commit();
                    
                    return [
                        'success' => true,
                        'message' => "Import completed successfully. Imported: {$importedCount}, Updated: {$updatedCount}",
                        'imported' => $importedCount,
                        'updated' => $updatedCount,
                        'errors' => $errors
                    ];
                    
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            } else {
                throw new \Exception('Invalid response format from AiSensy API');
            }
            
        } catch (\Exception $e) {
            Yii::error('AiSensy template import failed: ' . $e->getMessage(), __METHOD__);
            
            return [
                'success' => false,
                'message' => 'Failed to import templates: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload media file to AiSensy API
     * @return array JSON response
     */
    public function actionUploadMedia()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                throw new \Exception('AiSensy API key not configured. Please set the aisensy_wa_key in web settings.');
            }

            // Check if file was uploaded
            if (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('No file uploaded or upload error occurred');
            }

            $uploadedFile = $_FILES['media_file'];
            $filePath = $uploadedFile['tmp_name'];
            $fileName = $uploadedFile['name'];
            $mimeType = $uploadedFile['type'];
            
            // Fix MIME type for documents - WhatsApp is very specific about document MIME types
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($fileExtension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'])) {
                switch ($fileExtension) {
                    case 'pdf':
                        $mimeType = 'application/pdf';
                        break;
                    case 'doc':
                        $mimeType = 'application/msword';
                        break;
                    case 'docx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                        break;
                    case 'xls':
                        $mimeType = 'application/vnd.ms-excel';
                        break;
                    case 'xlsx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                        break;
                    case 'ppt':
                        $mimeType = 'application/vnd.ms-powerpoint';
                        break;
                    case 'pptx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                        break;
                    case 'txt':
                        $mimeType = 'text/plain';
                        break;
                }
            }

            // Try using HTTP client first, fallback to cURL if not available
            if (class_exists('yii\httpclient\Client')) {
                // Create HTTP client
                $client = new Client();
                
                // Create multipart form data
                $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setUrl('https://backend.aisensy.com/direct-apis/t1/media')
                    ->addHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $aisensy_wa_key
                    ])
                    ->addFile('file', $filePath, [
                        'fileName' => $fileName,
                        'mimeType' => $mimeType
                    ])
                    ->setData(['messaging_product' => 'whatsapp'])
                    ->send();

                if (!$response->isOk) {
                    $errorMessage = 'Failed to upload media to AiSensy API. Status: ' . $response->statusCode;
                    if (!empty($response->content)) {
                        $errorMessage .= '. Response: ' . $response->content;
                    }
                    throw new \Exception($errorMessage);
                }

                $data = $response->data;
            } else {
                // Fallback to cURL
                $cFile = new \CURLFile($filePath, $mimeType, $fileName);
                
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/media',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => [
                        'file' => $cFile,
                        'messaging_product' => 'whatsapp'
                    ],
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Authorization: Bearer ' . $aisensy_wa_key
                    ],
                ]);
                
                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                
                if ($httpCode !== 200) {
                    $errorMessage = 'Failed to upload media to AiSensy API. Status: ' . $httpCode;
                    if (!empty($response)) {
                        $errorMessage .= '. Response: ' . $response;
                    }
                    throw new \Exception($errorMessage);
                }
                
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response from AiSensy API');
                }
            }

            if (isset($data['id'])) {
                return [
                    'success' => true,
                    'media_id' => $data['id'],
                    'filename' => $fileName, // Add filename for display
                    'message' => 'Media uploaded successfully',
                    'file_info' => [
                        'name' => $fileName,
                        'mime_type' => $mimeType,
                        'extension' => $fileExtension ?? 'unknown'
                    ]
                ];
            } else {
                throw new \Exception('No media ID returned from API. Response: ' . json_encode($data));
            }
            
        } catch (\Exception $e) {
            Yii::error('Media upload failed: ' . $e->getMessage(), __METHOD__);
            
            return [
                'success' => false,
                'message' => 'Failed to upload media: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test message using template
     * @return array JSON response
     */
    public function actionSendTestMessage()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $post = Yii::$app->request->post();
        
        try {
            // Validate required parameters
            if (empty($post['template_id'])) {
                throw new \Exception('Template ID is required');
            }
            
            if (empty($post['phone_number'])) {
                throw new \Exception('Phone number is required');
            }

            // Get template
            $template = $this->findModel($post['template_id']);
            
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                throw new \Exception('AiSensy API key not configured. Please set the aisensy_wa_key in web settings.');
            }

            // Build message payload
            $messageData = [
                'to' => $post['phone_number'],
                'type' => 'template',
                'template' => [
                    'language' => [
                        'policy' => 'deterministic',
                        'code' => $template->language ?: 'en_us'
                    ],
                    'name' => $template->name,
                    'components' => []
                ]
            ];

            // Get template components to build message structure
            $components = AisensyTemplateComponents::find()
                ->where(['template_id' => $template->id])
                ->orderBy('component_index')
                ->all();

            foreach ($components as $component) {
                $componentData = [
                    'type' => strtolower($component->type)
                ];

                // Handle different component types
                switch (strtoupper($component->type)) {
                    case 'HEADER':
                        if ($component->format === 'IMAGE' && !empty($post['header_media_id'])) {
                            $imageParam = [
                                'type' => 'image',
                                'image' => [
                                    'id' => $post['header_media_id']
                                ]
                            ];
                            // Add caption if provided
                            if (!empty($post['header_media_caption'])) {
                                $imageParam['image']['caption'] = $post['header_media_caption'];
                            }
                            $componentData['parameters'] = [$imageParam];
                        } elseif ($component->format === 'VIDEO' && !empty($post['header_media_id'])) {
                            $videoParam = [
                                'type' => 'video',
                                'video' => [
                                    'id' => $post['header_media_id']
                                ]
                            ];
                            // Add caption if provided
                            if (!empty($post['header_media_caption'])) {
                                $videoParam['video']['caption'] = $post['header_media_caption'];
                            }
                            $componentData['parameters'] = [$videoParam];
                        } elseif ($component->format === 'DOCUMENT' && !empty($post['header_media_id'])) {
                            $documentParam = [
                                'type' => 'document',
                                'document' => [
                                    'id' => $post['header_media_id']
                                ]
                            ];
                            // Add filename for documents (required for proper display)
                            if (!empty($post['header_media_filename'])) {
                                $documentParam['document']['filename'] = $post['header_media_filename'];
                            }
                            // Add caption if provided
                            if (!empty($post['header_media_caption'])) {
                                $documentParam['document']['caption'] = $post['header_media_caption'];
                            }
                            $componentData['parameters'] = [$documentParam];
                        } elseif (!empty($post['header_text'])) {
                            $componentData['parameters'] = [
                                [
                                    'type' => 'text',
                                    'text' => $post['header_text']
                                ]
                            ];
                        }
                        break;

                    case 'BODY':
                        $parameters = [];
                        
                        // Extract parameters from body text and user input
                        if (!empty($component->text)) {
                            preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                            if (!empty($matches[1])) {
                                foreach ($matches[1] as $index) {
                                    $paramKey = 'body_param_' . ($index);
                                    if (!empty($post[$paramKey])) {
                                        $parameters[] = [
                                            'type' => 'text',
                                            'text' => $post[$paramKey]
                                        ];
                                    }
                                }
                            }
                        }
                        
                        if (!empty($parameters)) {
                            $componentData['parameters'] = $parameters;
                        }
                        break;

                    case 'BUTTONS':
                        // Handle button parameters if needed
                        $parameters = [];
                        $buttonsData = json_decode($component->buttons, true);
                        
                        if (is_array($buttonsData)) {
                            foreach ($buttonsData as $buttonIndex => $button) {
                                if ($button['type'] === 'QUICK_REPLY') {
                                    // Quick reply buttons don't need parameters
                                    continue;
                                } elseif ($button['type'] === 'URL' && strpos($button['url'], '{{') !== false) {
                                    $paramKey = 'button_param_' . $buttonIndex;
                                    if (!empty($post[$paramKey])) {
                                        $parameters[] = [
                                            'type' => 'text',
                                            'text' => $post[$paramKey]
                                        ];
                                    }
                                }
                            }
                        }
                        
                        if (!empty($parameters)) {
                            $componentData['parameters'] = $parameters;
                        }
                        break;
                }

                if (!empty($componentData['parameters']) || strtoupper($component->type) === 'FOOTER') {
                    $messageData['template']['components'][] = $componentData;
                }
            }

            // Send message via API
            if (class_exists('yii\httpclient\Client')) {
                // Create HTTP client
                $client = new Client();
                
                $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setUrl('https://backend.aisensy.com/direct-apis/t1/marketing_messages')
                    ->addHeaders([
                        'Accept' => 'application/json, application/xml',
                        'Authorization' => 'Bearer ' . $aisensy_wa_key,
                        'Content-Type' => 'application/json'
                    ])
                    ->setContent(json_encode($messageData))
                    ->send();

                if (!$response->isOk) {
                    throw new \Exception('Failed to send message via AiSensy API. Status: ' . $response->statusCode . '. Response: ' . $response->content);
                }

                $data = $response->data;
            } else {
                // Fallback to cURL
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/marketing_messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($messageData),
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json, application/xml',
                        'Authorization: Bearer ' . $aisensy_wa_key,
                        'Content-Type: application/json'
                    ],
                ]);
                
                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                
                if ($httpCode < 200 || $httpCode >= 300) {
                    throw new \Exception('Failed to send message via AiSensy API. Status: ' . $httpCode . '. Response: ' . $response);
                }
                
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $data = ['response' => $response]; // Store raw response if JSON decode fails
                }
            }

            return [
                'success' => true,
                'message' => 'Test message sent successfully',
                'response' => $data,
                'payload' => $messageData // For debugging
            ];
            
        } catch (\Exception $e) {
            Yii::error('Test message sending failed: ' . $e->getMessage(), __METHOD__);
            
            return [
                'success' => false,
                'message' => 'Failed to send test message: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk message sending page
     * @param string $id Template ID
     * @return mixed
     */
    public function actionBulkMessage($id)
    {
        $model = $this->findModel($id);
        
        // Get template components to build dynamic form
        $components = AisensyTemplateComponents::find()
            ->where(['template_id' => $model->id])
            ->orderBy('component_index')
            ->all();

        return $this->render('bulk-message', [
            'model' => $model,
            'components' => $components,
        ]);
    }

    /**
     * Download sample Excel file for bulk messaging
     * @param string $id Template ID
     * @return mixed
     */
    public function actionDownloadSampleExcel($id)
    {
        $model = $this->findModel($id);
        
        // Get template components to determine required columns
        $components = AisensyTemplateComponents::find()
            ->where(['template_id' => $model->id])
            ->orderBy('component_index')
            ->all();

        $headers = ['Mobile Number'];
        $sampleData = ['916281684605'];
        
        // Add parameter columns based on template components
        foreach ($components as $component) {
            if (strtoupper($component->type) === 'BODY' && !empty($component->text)) {
                // Extract parameters from body text
                preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $index) {
                        $paramHeader = 'Body_Param_' . $index;
                        if (!in_array($paramHeader, $headers)) {
                            $headers[] = $paramHeader;
                            $sampleData[] = 'Sample Value ' . $index;
                        }
                    }
                }
            } elseif (strtoupper($component->type) === 'BUTTONS' && !empty($component->buttons)) {
                $buttons = json_decode($component->buttons, true);
                if (is_array($buttons)) {
                    foreach ($buttons as $buttonIndex => $button) {
                        if ($button['type'] === 'URL' && isset($button['url']) && strpos($button['url'], '{{') !== false) {
                            $paramHeader = 'Button_Param_' . $buttonIndex;
                            if (!in_array($paramHeader, $headers)) {
                                $headers[] = $paramHeader;
                                $sampleData[] = 'https://example.com/sample-url';
                            }
                        }
                    }
                }
            }
        }

        // Create Excel content with proper phone number formatting
        $filename = 'BulkMessage_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $model->name) . '_Template.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Write BOM for proper Excel UTF-8 encoding
        fwrite($output, "\xEF\xBB\xBF");
        
        // Create comprehensive instructions sheet structure
        
        // === HEADER SECTION ===
        fputcsv($output, ['=== AISENSY WHATSAPP BULK MESSAGE TEMPLATE ===']);
        fputcsv($output, ['Template Name: ' . $model->name]);
        fputcsv($output, ['Language: ' . ($model->language ?? 'en')]);
        fputcsv($output, ['Category: ' . ($model->category ?? 'MARKETING')]);
        fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
        fputcsv($output, []); // Empty row
        
        // === TEMPLATE BODY PREVIEW ===
        fputcsv($output, ['=== TEMPLATE BODY PREVIEW ===']);
        if (!empty($model->body_text)) {
            $bodyLines = explode("\n", $model->body_text);
            foreach ($bodyLines as $line) {
                fputcsv($output, ['Body: ' . trim($line)]);
            }
        }
        if (!empty($model->footer_text)) {
            fputcsv($output, ['Footer: ' . $model->footer_text]);
        }
        fputcsv($output, []); // Empty row
        
        // === INSTRUCTIONS SECTION ===  
        fputcsv($output, ['=== CRITICAL PHONE NUMBER FORMATTING INSTRUCTIONS ===']);
        fputcsv($output, ['1. MUST FORMAT MOBILE NUMBER COLUMN AS TEXT in Excel:']);
        fputcsv($output, ['   - Select mobile number column -> Right click -> Format Cells -> Text']);
        fputcsv($output, ['2. ALTERNATIVE: Prefix each number with apostrophe: \'916281684605']);
        fputcsv($output, ['3. AVOID: Scientific notation (9.16E+11) - This causes message failures!']);
        fputcsv($output, ['4. FORMAT: Country code + mobile number (e.g., 916281684605 for India)']);
        fputcsv($output, ['5. NO SPACES or special characters in phone numbers']);
        fputcsv($output, []); // Empty row
        
        // === PARAMETER INSTRUCTIONS ===
        fputcsv($output, ['=== PARAMETER FILLING INSTRUCTIONS ===']);
        foreach ($components as $component) {
            if (strtoupper($component->type) === 'BODY' && !empty($component->text)) {
                preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $index) {
                        $paramDescription = 'Body_Param_' . $index . ': Replace {{' . $index . '}} in template';
                        fputcsv($output, [$paramDescription]);
                    }
                }
            }
        }
        fputcsv($output, []); // Empty row
        
        // === DATA SECTION STARTS HERE ===
        fputcsv($output, ['=== DATA SECTION - FILL BELOW ROWS ===']);
        fputcsv($output, ['DELETE INSTRUCTION ROWS ABOVE BEFORE UPLOADING']);
        fputcsv($output, []); // Empty row
        
        // Write column headers
        fputcsv($output, $headers);
        
        // Write formatting reminder row
        $formatReminder = [];
        $formatReminder[0] = 'FORMAT AS TEXT ->>';
        for ($j = 1; $j < count($headers); $j++) {
            $formatReminder[$j] = 'Your data here';
        }
        fputcsv($output, $formatReminder);
        fputcsv($output, []); // Empty row
        
        // Generate sample data based on actual template content
        $sampleRows = [];
        
        // Sample row 1 with proper formatting
        $row1 = ["'916281684605"]; // Apostrophe prefix for phone
        foreach ($components as $component) {
            if (strtoupper($component->type) === 'BODY' && !empty($component->text)) {
                preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $index) {
                        // Create realistic sample data based on common use cases
                        $sampleValue = '';
                        if (strpos(strtolower($component->text), 'name') !== false) {
                            $sampleValue = 'John Doe';
                        } elseif (strpos(strtolower($component->text), 'amount') !== false || strpos(strtolower($component->text), 'price') !== false) {
                            $sampleValue = '₹1000';
                        } elseif (strpos(strtolower($component->text), 'date') !== false) {
                            $sampleValue = date('d-M-Y');
                        } elseif (strpos(strtolower($component->text), 'time') !== false) {
                            $sampleValue = date('h:i A');
                        } elseif (strpos(strtolower($component->text), 'code') !== false || strpos(strtolower($component->text), 'otp') !== false) {
                            $sampleValue = 'ABC123';
                        } else {
                            $sampleValue = 'Sample Value ' . $index;
                        }
                        $row1[] = $sampleValue;
                    }
                }
            }
        }
        // Handle button parameters
        foreach ($components as $component) {
            if (strtoupper($component->type) === 'BUTTONS' && !empty($component->buttons)) {
                $buttons = json_decode($component->buttons, true);
                if (is_array($buttons)) {
                    foreach ($buttons as $buttonIndex => $button) {
                        if ($button['type'] === 'URL' && isset($button['url']) && strpos($button['url'], '{{') !== false) {
                            $row1[] = 'https://example.com/user-page';
                        }
                    }
                }
            }
        }
        fputcsv($output, $row1);
        
        // Additional sample rows with different realistic data
        $samplePhones = ["'919876543210", "'918765432109", "'917654321098"];
        $sampleNames = ["Jane Smith", "Raj Kumar", "Priya Sharma"];
        
        for ($i = 0; $i < 3; $i++) {
            $row = [$samplePhones[$i]];
            $nameIndex = 0;
            
            foreach ($components as $component) {
                if (strtoupper($component->type) === 'BODY' && !empty($component->text)) {
                    preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $index) {
                            if (strpos(strtolower($component->text), 'name') !== false) {
                                $row[] = $sampleNames[$i];
                            } elseif (strpos(strtolower($component->text), 'amount') !== false) {
                                $row[] = '₹' . (1000 + ($i * 500));
                            } elseif (strpos(strtolower($component->text), 'code') !== false) {
                                $row[] = 'CODE' . (100 + $i);
                            } else {
                                $row[] = 'Data ' . ($i + 2) . '_' . $index;
                            }
                        }
                    }
                }
            }
            
            // Handle button parameters
            foreach ($components as $component) {
                if (strtoupper($component->type) === 'BUTTONS' && !empty($component->buttons)) {
                    $buttons = json_decode($component->buttons, true);
                    if (is_array($buttons)) {
                        foreach ($buttons as $buttonIndex => $button) {
                            if ($button['type'] === 'URL' && isset($button['url']) && strpos($button['url'], '{{') !== false) {
                                $row[] = 'https://example.com/user' . ($i + 2);
                            }
                        }
                    }
                }
            }
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    } 

    /**
     * Process bulk upload and send messages
     * @return array JSON response
     */
    public function actionProcessBulkUpload()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $post = Yii::$app->request->post();
        
        // Debug logging
        Yii::error('POST data received: ' . print_r($post, true), __METHOD__);
        Yii::error('FILES data received: ' . print_r($_FILES, true), __METHOD__);
        Yii::error('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'], __METHOD__);
        Yii::error('CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'), __METHOD__);
        
        try {
            // Validate required parameters
            if (empty($post['template_id'])) {
                throw new \Exception('Template ID is required. Received POST data: ' . print_r($post, true));
            }
 
            // Check if file was uploaded
            if (!isset($_FILES['excel_file'])) {
                throw new \Exception('No excel_file found in $_FILES. Available files: ' . print_r(array_keys($_FILES), true));
            }
            
            if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $errorCode = $_FILES['excel_file']['error'];
                $errorMessage = $uploadErrors[$errorCode] ?? 'Unknown upload error: ' . $errorCode;
                throw new \Exception('File upload error: ' . $errorMessage . '. File info: ' . print_r($_FILES['excel_file'], true));
            }

            $template = $this->findModel($post['template_id']);
            
            // Debug template info
            Yii::error('Template found: ID=' . $template->id . ', Name=' . $template->name, __METHOD__);
            
            // Get AiSensy bearer token from settings
            $setting = new WebSetting();
            $aisensy_wa_key = $setting->getSettingBykey('aisensy_wa_key');
            
            if (empty($aisensy_wa_key)) {
                throw new \Exception('AiSensy API key not configured. Please set the aisensy_wa_key in web settings.');
            }

            // Process Excel file
            $uploadedFile = $_FILES['excel_file'];
            $filePath = $uploadedFile['tmp_name'];
            
            // Read CSV/Excel file with improved parsing
            $rows = [];
            $scientificNotationWarning = false;
            $headers = null;
            $dataStarted = false;
            
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $rowNumber = 0;
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowNumber++;
                    
                    // Skip BOM if present
                    if ($rowNumber === 1 && !empty($data[0])) {
                        $data[0] = str_replace("\xEF\xBB\xBF", '', $data[0]);
                    }
                    
                    // Look for data section start
                    if (!$dataStarted) {
                        // Check if this row contains column headers (Mobile Number should be first)
                        if (!empty($data[0]) && (
                            strtolower(trim($data[0])) === 'mobile number' ||
                            trim($data[0]) === 'Mobile Number'
                        )) {
                            $headers = array_map('trim', $data);
                            $dataStarted = true;
                            continue;
                        }
                        // Skip all instruction/header rows
                        continue;
                    }
                    
                    // Skip formatting reminder rows and empty rows after headers
                    if (empty(trim($data[0])) || 
                        strpos(strtoupper($data[0]), 'FORMAT AS TEXT') !== false ||
                        strpos(strtoupper($data[0]), 'YOUR DATA HERE') !== false) {
                        continue;
                    }
                    
                    // Process data rows
                    if (!empty($data[0]) && $headers !== null) {
                        // Check for scientific notation in mobile number
                        if (strpos(strtoupper($data[0]), 'E') !== false) {
                            $scientificNotationWarning = true;
                        }
                        
                        // Ensure we have the right number of columns
                        $data = array_pad($data, count($headers), '');
                        $row = array_combine($headers, $data);
                        
                        // Basic validation - phone number should not be empty
                        if (!empty(trim($row['Mobile Number']))) {
                            $rows[] = $row;
                        }
                    }
                }
                fclose($handle);
            } else {
                throw new \Exception('Unable to read uploaded file');
            }
            
            // Validate that we found headers and data
            if ($headers === null) {
                throw new \Exception('Could not find data headers in the uploaded file. Please ensure the file contains "Mobile Number" column header.');
            }
            
            // Add warning if scientific notation detected
            if ($scientificNotationWarning) {
                $errors[] = "WARNING: Scientific notation detected in mobile numbers (e.g., 9.16E+11). This may cause issues. Please format mobile number column as TEXT in Excel.";
            }

            if (empty($rows)) {
                throw new \Exception('No valid data found in uploaded file');
            }

            // Create bulk campaign log
            $campaignName = 'Bulk Campaign - ' . $template->name . ' - ' . date('Y-m-d H:i:s');
            $campaign = new AisensyBulkCampaignLog();
            $campaign->campaign_name = $campaignName;
            $campaign->template_id = $template->id;
            $campaign->excel_filename = $uploadedFile['name'];
            $campaign->total_contacts = count($rows);
            $campaign->batch_size = 100;
            $campaign->delay_seconds = 1.0;
            $campaign->campaign_status = 'running';
            $campaign->started_at = date('Y-m-d H:i:s');
            $campaign->save();

            // Get template components for message building
            $components = AisensyTemplateComponents::find()
                ->where(['template_id' => $template->id])
                ->orderBy('component_index')
                ->all();

            $successCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            $errors = [];
            $processedNumbers = [];

            // Enhanced time management and execution controls
            $startTime = microtime(true);
            $maxExecutionTime = 300; // 5 minutes maximum execution time
            $timeBuffer = 30; // Reserve 30 seconds for response processing
            $maxMessagesPerBatch = 50; // Reduced batch size for better time management
            $delayBetweenMessages = 0.5; // Reduced delay for faster processing
            $chunkSize = 10; // Process in smaller chunks with time checks
            $maxMessagesProcessed = 0;
            
            // Set execution time limit and ignore user abort
            set_time_limit($maxExecutionTime + $timeBuffer);
            ignore_user_abort(true);
            
            // Enhanced cURL timeout settings
            $curlTimeout = 30; // 30 seconds timeout per request
            $curlConnectTimeout = 10; // 10 seconds connection timeout

            // Process rows in chunks with time management
            $totalRows = count($rows);
            $chunkedRows = array_chunk($rows, $chunkSize, true);
            $chunkProcessed = 0;
            
            foreach ($chunkedRows as $chunkIndex => $chunk) {
                // Check execution time before processing each chunk
                $currentTime = microtime(true);
                $elapsedTime = $currentTime - $startTime;
                $remainingTime = $maxExecutionTime - $elapsedTime;
                
                if ($remainingTime < $timeBuffer) {
                    $errors[] = "Time limit approaching. Stopped processing at chunk " . ($chunkIndex + 1) . " to avoid timeout. Processed {$maxMessagesProcessed} messages.";
                    break;
                }
                
                if ($maxMessagesProcessed >= $maxMessagesPerBatch) {
                    $errors[] = "Stopped at batch limit - Maximum {$maxMessagesPerBatch} messages processed to maintain system stability";
                    break;
                }
                
                foreach ($chunk as $rowIndex => $row) {
                    // Additional time check within chunk processing
                    $currentTime = microtime(true);
                    $elapsedTime = $currentTime - $startTime;
                    
                    if ($elapsedTime > ($maxExecutionTime - $timeBuffer)) {
                        $errors[] = "Time limit reached during row processing. Stopped at row " . ($rowIndex + 1) . " after {$maxMessagesProcessed} messages.";
                        break 2; // Break out of both loops
                    }
                    
                    if ($maxMessagesProcessed >= $maxMessagesPerBatch) {
                        $errors[] = "Batch limit reached at row " . ($rowIndex + 1) . " - Maximum {$maxMessagesPerBatch} messages processed";
                        break 2;
                    }

                try {
                    $originalPhone = trim($row['Mobile Number'] ?? '');
                    $phoneNumber = $originalPhone;
                    
                    // Clean phone number - remove apostrophe if present and handle scientific notation
                    $phoneNumber = ltrim($phoneNumber, "'"); // Remove leading apostrophe
                    
                    // Handle scientific notation (e.g., 9.16282E+11)
                    if (strpos(strtoupper($phoneNumber), 'E') !== false) {
                        // Convert scientific notation to regular number
                        $phoneNumber = sprintf('%.0f', floatval($phoneNumber));
                        $errors[] = "Row " . ($rowIndex + 1) . ": Scientific notation detected and converted: {$originalPhone} -> {$phoneNumber}";
                    }
                    
                    // Remove any non-digit characters except + at the beginning
                    $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
                    
                    // Remove + if present at the beginning
                    $phoneNumber = ltrim($phoneNumber, '+');
                    
                    // Enhanced phone number validation
                    if (empty($phoneNumber)) {
                        $errors[] = "Row " . ($rowIndex + 1) . ": Empty phone number (original: '{$originalPhone}')";
                        $failedCount++;
                        continue;
                    }
                    
                    if (!preg_match('/^\d{10,15}$/', $phoneNumber)) {
                        $errors[] = "Row " . ($rowIndex + 1) . ": Invalid phone number format - '{$phoneNumber}' (original: '{$originalPhone}'). Must be 10-15 digits with country code.";
                        $failedCount++;
                        continue;
                    }
                    
                    // Additional validation for common phone number patterns
                    if (strlen($phoneNumber) < 10) {
                        $errors[] = "Row " . ($rowIndex + 1) . ": Phone number too short - '{$phoneNumber}' (original: '{$originalPhone}'). Include country code.";
                        $failedCount++;
                        continue;
                    }

                    // Check for duplicate numbers in this batch
                    if (in_array($phoneNumber, $processedNumbers)) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Duplicate phone number - {$phoneNumber}";
                        $failedCount++;
                        continue;
                    }
                    $processedNumbers[] = $phoneNumber;

                    // Create bulk message log entry
                    $bulkMessage = new AisensyBulkMessageLog();
                    $bulkMessage->campaign_id = $campaign->id;
                    $bulkMessage->template_id = $template->id;
                    $bulkMessage->contact_number = $phoneNumber;
                    $bulkMessage->status = 'pending';
                    $bulkMessage->save();

                    // Build message payload
                    $messageData = [
                        'to' => $phoneNumber,
                        'type' => 'template',
                        'template' => [
                            'language' => [
                                'policy' => 'deterministic',
                                'code' => $template->language ?: 'en_us'
                            ],
                            'name' => $template->name,
                            'components' => []
                        ]
                    ];

                    // Build components based on template and Excel data
                    foreach ($components as $component) {
                        $componentData = [
                            'type' => strtolower($component->type)
                        ];

                        // Handle different component types
                        switch (strtoupper($component->type)) {
                            case 'HEADER':
                                if ($component->format === 'IMAGE' && !empty($post['header_media_id'])) {
                                    $imageParam = [
                                        'type' => 'image',
                                        'image' => [
                                            'id' => $post['header_media_id']
                                        ]
                                    ];
                                    // Add caption if provided
                                    if (!empty($post['header_media_caption'])) {
                                        $imageParam['image']['caption'] = $post['header_media_caption'];
                                    }
                                    $componentData['parameters'] = [$imageParam];
                                } elseif ($component->format === 'VIDEO' && !empty($post['header_media_id'])) {
                                    $videoParam = [
                                        'type' => 'video',
                                        'video' => [
                                            'id' => $post['header_media_id']
                                        ]
                                    ];
                                    // Add caption if provided
                                    if (!empty($post['header_media_caption'])) {
                                        $videoParam['video']['caption'] = $post['header_media_caption'];
                                    }
                                    $componentData['parameters'] = [$videoParam];
                                } elseif ($component->format === 'DOCUMENT' && !empty($post['header_media_id'])) {
                                    $documentParam = [
                                        'type' => 'document',
                                        'document' => [
                                            'id' => $post['header_media_id']
                                        ]
                                    ];
                                    // Add filename for documents (required for proper display)
                                    if (!empty($post['header_media_filename'])) {
                                        $documentParam['document']['filename'] = $post['header_media_filename'];
                                    }
                                    // Add caption if provided
                                    if (!empty($post['header_media_caption'])) {
                                        $documentParam['document']['caption'] = $post['header_media_caption'];
                                    }
                                    $componentData['parameters'] = [$documentParam];
                                }
                                break;

                            case 'BODY':
                                $parameters = [];
                                
                                // Extract parameters from body text and Excel data
                                if (!empty($component->text)) {
                                    preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                                    if (!empty($matches[1])) {
                                        foreach ($matches[1] as $index) {
                                            $paramKey = 'Body_Param_' . $index;
                                            if (!empty($row[$paramKey])) {
                                                $parameters[] = [
                                                    'type' => 'text',
                                                    'text' => trim($row[$paramKey])
                                                ];
                                            }
                                        }
                                    }
                                }
                                
                                if (!empty($parameters)) {
                                    $componentData['parameters'] = $parameters;
                                }
                                break;

                            case 'BUTTONS':
                                // Handle button parameters from Excel
                                $parameters = [];
                                $buttonsData = json_decode($component->buttons, true);
                                
                                if (is_array($buttonsData)) {
                                    foreach ($buttonsData as $buttonIndex => $button) {
                                        if ($button['type'] === 'URL' && strpos($button['url'], '{{') !== false) {
                                            $paramKey = 'Button_Param_' . $buttonIndex;
                                            if (!empty($row[$paramKey])) {
                                                $parameters[] = [
                                                    'type' => 'text',
                                                    'text' => trim($row[$paramKey])
                                                ];
                                            }
                                        }
                                    }
                                }
                                
                                if (!empty($parameters)) {
                                    $componentData['parameters'] = $parameters;
                                }
                                break;
                        }

                        if (!empty($componentData['parameters']) || strtoupper($component->type) === 'FOOTER') {
                            $messageData['template']['components'][] = $componentData;
                        }
                    }

                    // Send message via API
                    if (class_exists('yii\httpclient\Client')) {
                        $client = new Client();
                        
                        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setUrl('https://backend.aisensy.com/direct-apis/t1/marketing_messages')
                            ->addHeaders([
                                'Accept' => 'application/json, application/xml',
                                'Authorization' => 'Bearer ' . $aisensy_wa_key,
                                'Content-Type' => 'application/json'
                            ])
                            ->setContent(json_encode($messageData))
                            ->send();

                        if ($response->isOk) {
                            $successCount++;
                        } else {
                            $errors[] = "Row " . ($rowIndex + 2) . " ({$phoneNumber}): API Error - " . $response->statusCode;
                            $failedCount++;
                        }
                    } else {
                        // Fallback to cURL
                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => 'https://backend.aisensy.com/direct-apis/t1/marketing_messages',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => json_encode($messageData),
                            CURLOPT_HTTPHEADER => [
                                'Accept: application/json, application/xml',
                                'Authorization: Bearer ' . $aisensy_wa_key,
                                'Content-Type: application/json'
                            ],
                        ]);
                        
                        $response = curl_exec($curl);
                        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);
                        
                        if ($httpCode >= 200 && $httpCode < 300) {
                            $successCount++;
                            // Parse response to get message ID
                            $responseData = json_decode($response, true);
                            $messageId = $responseData['messages'][0]['id'] ?? null;
                            
                            // Update bulk message log as sent
                            $bulkMessage->status = 'sent';
                            $bulkMessage->message_id = $messageId;
                            $bulkMessage->sent_at = date('Y-m-d H:i:s');
                            $bulkMessage->api_response = $response;
                            $bulkMessage->save();
                        } else {
                            $errors[] = "Row " . ($rowIndex + 2) . " ({$phoneNumber}): API Error - " . $httpCode;
                            $failedCount++;
                            
                            // Update bulk message log as failed
                            $bulkMessage->status = 'failed';
                            $bulkMessage->failed_at = date('Y-m-d H:i:s');
                            $bulkMessage->error_message = "API Error - HTTP " . $httpCode;
                            $bulkMessage->save();
                        }
                    }

                    $maxMessagesProcessed++;
                    
                    // Anti-spam delay
                    if ($delayBetweenMessages > 0) {
                        sleep($delayBetweenMessages);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $failedCount++;
                    
                    // Update bulk message log as failed if it exists
                    if (isset($bulkMessage)) {
                        $bulkMessage->status = 'failed';
                        $bulkMessage->failed_at = date('Y-m-d H:i:s');
                        $bulkMessage->error_message = $e->getMessage();
                        $bulkMessage->save();
                    }
                }
                
                // Update processed count
                $chunkProcessed++;
                
                // Add a small delay between chunks to prevent overwhelming the API
                if ($chunkIndex < count($chunkedRows) - 1) {
                    usleep(100000); // 0.1 second delay between chunks
                }
            }
            }

            // Calculate final statistics
            $endTime = microtime(true);
            $totalExecutionTime = round($endTime - $startTime, 2);
            
            // Prepare comprehensive summary with timing information
            $totalRows = count($rows);
            $summaryMessage = "Bulk messaging completed. ";
            
            if ($scientificNotationWarning) {
                $summaryMessage .= "⚠️ Scientific notation detected in phone numbers. ";
            }
            
            $summaryMessage .= "Processed: {$totalRows}, Success: {$successCount}, Failed: {$failedCount}, Skipped: {$skippedCount}";
            
            if ($maxMessagesProcessed >= $maxMessagesPerBatch) {
                $summaryMessage .= " (Batch limit reached)";
            }

            // Update campaign with final counts and status
            $campaign->sent_count = $successCount;
            $campaign->failed_count = $failedCount;
            $campaign->skipped_count = $skippedCount;
            $campaign->campaign_status = 'completed';
            $campaign->completed_at = date('Y-m-d H:i:s');
            
            // Set performance metrics
            $performanceMetrics = [
                'execution_time' => $totalExecutionTime,
                'messages_per_second' => $totalRows > 0 ? round($totalRows / $totalExecutionTime, 2) : 0,
                'avg_time_per_message' => $totalRows > 0 ? round($totalExecutionTime / $totalRows, 2) : 0,
                'chunks_processed' => count($chunkedRows),
                'messages_per_chunk' => $chunkSize,
                'max_messages_processed' => $maxMessagesProcessed,
                'batch_limit_reached' => ($maxMessagesProcessed >= $maxMessagesPerBatch)
            ];
            $campaign->performance_metrics = json_encode($performanceMetrics);
            $campaign->save();

            return [
                'success' => true,
                'message' => $summaryMessage,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
                'total_processed' => $totalRows,
                'campaign_id' => $campaign->id,
                'scientific_notation_warning' => $scientificNotationWarning,
                'batch_limit_reached' => ($maxMessagesProcessed >= $maxMessagesPerBatch),
                'template_info' => [
                    'name' => $template->name,
                    'language' => $template->language,
                    'category' => $template->category
                ],
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            // Log the full error details
            Yii::error('Bulk message processing failed: ' . $e->getMessage(), __METHOD__);
            Yii::error('Stack trace: ' . $e->getTraceAsString(), __METHOD__);
            
            // Update campaign as failed if it exists
            if (isset($campaign)) {
                $campaign->campaign_status = 'failed';
                $campaign->error_message = $e->getMessage();
                $campaign->completed_at = date('Y-m-d H:i:s');
                $campaign->save();
            }
            
            return [
                'success' => false,
                'message' => 'Failed to process bulk messages: ' . $e->getMessage(),
                'total_processed' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => [$e->getMessage()],
                'debug_info' => [
                    'post_data' => $post ?? 'No POST data',
                    'files_data' => $_FILES ?? 'No FILES data'
                ]
            ];
        }
    }
}
