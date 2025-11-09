<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\BusinessDocuments;
use app\modules\admin\models\search\BusinessDocumentsSearch;
use Dompdf\Dompdf;
use Dompdf\Options;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter; 

/**
 * BusinessDocumentsController implements the CRUD actions for BusinessDocuments model.
 */
class BusinessDocumentsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status','download-image'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status','download-image'],
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
     * Lists all BusinessDocuments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BusinessDocumentsSearch();
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
     * Displays a single BusinessDocuments model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BusinessDocuments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
  public function actionCreate()
{
    $model = new BusinessDocuments();
    $vendor_details_id = Yii::$app->request->get('vendor_details_id');

    try {
        if ($model->loadAll(Yii::$app->request->post())) {
            $file = \yii\web\UploadedFile::getInstance($model, 'file');

            if ($file) {
                // Upload to ImageKit
                $image = Yii::$app->notification->imageKitUpload($file);

                if (!empty($image['url'])) {
                    $model->file = $image['url']; // save file URL into DB
                } else {
                    Yii::error("ImageKit upload failed. Response: " . var_export($image, true), __METHOD__);
                    Yii::$app->session->setFlash('danger', 'Failed to upload file. Please try again.');
                    return $this->render('create', [
                        'model' => $model,
                        'vendor_details_id' => $vendor_details_id,
                    ]);
                }
            } else {
                Yii::$app->session->setFlash('danger', 'Please select a file to upload.');
                return $this->render('create', [
                    'model' => $model,
                    'vendor_details_id' => $vendor_details_id,
                ]);
            }

            $model->vendor_details_id = $vendor_details_id;

            if ($model->saveAll()) {
                Yii::$app->session->setFlash('success', 'Business document added successfully.');

                return $vendor_details_id
                    ? $this->redirect(['/admin/vendor-details/view', 'id' => $vendor_details_id])
                    : $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('danger', 'Failed to save the document. Please check the inputs.');
            }
        }
    } catch (\Throwable $e) {
        Yii::error("Document upload failed: " . $e->getMessage(), __METHOD__);
        Yii::$app->session->setFlash('danger', 'An unexpected error occurred. Please try again later.');
    }

    return $this->render('create', [
        'model' => $model,
        'vendor_details_id' => $vendor_details_id,
    ]);
}

    

    /**
     * Updates an existing BusinessDocuments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $vendor_details_id = Yii::$app->request->get('vendor_details_id') ?? $model->vendor_details_id;
    
        try {
            if ($model->loadAll(Yii::$app->request->post())) {
                $file = \yii\web\UploadedFile::getInstance($model, 'file');
    
                if ($file) {
                    $image = Yii::$app->notification->imageKitUpload($file);
    
                    if (isset($image['url'])) {
                        $model->file = $image['url'];
                    } else {
                        Yii::$app->session->setFlash('danger', 'File upload failed. Please try again.');
                        return $this->render('update', [
                            'model' => $model,
                            'vendor_details_id' => $vendor_details_id
                        ]);
                    }
                }
    
                if ($model->saveAll()) {
                    Yii::$app->session->setFlash('success', 'Document updated successfully.');
    
                    if (empty($vendor_details_id)) {
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
    
                    return $this->redirect(['/admin/vendor-details/view', 'id' => $vendor_details_id]);
                } else {
                    Yii::$app->session->setFlash('danger', 'Failed to update the document. Please check the form.');
                }
            }
        } catch (\Exception $e) {
            Yii::error("Update failed: " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('danger', 'An error occurred: ' . $e->getMessage());
        }
    
        return $this->render('update', [
            'model' => $model,
            'vendor_details_id' => $vendor_details_id
        ]);
    }
    
    /**
     * Deletes an existing BusinessDocuments model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
    
        if ($model !== null) {
            $model->status = BusinessDocuments::STATUS_DELETE;
            $model->save(false);
    
            Yii::$app->session->setFlash('success', 'Business document deleted successfully.');
    
            return $this->redirect(['/admin/vendor-details/view', 'id' => $model->vendor_details_id]);
        }
    
        Yii::$app->session->setFlash('danger', 'Document not found or already deleted.');
        return $this->redirect(['index']);
    }
    
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = BusinessDocuments::find()->where([
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
     * Finds the BusinessDocuments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessDocuments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessDocuments::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    public function actionDownloadImage($id)
{
    $model = BusinessDocuments::findOne($id);
    if (!$model || empty($model->file)) {
        throw new \yii\web\NotFoundHttpException('File not found.');
    }

    $fileUrl = $model->file;
    $fileName = pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_FILENAME);

    $options = new Options();
    $options->set('isRemoteEnabled', true); 
    $dompdf = new Dompdf($options);

    $ext = strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
        $html = '<h3>Document Preview</h3>
                 <img src="' . $fileUrl . '" style="width:100%;max-height:700px;object-fit:contain;">';
    } elseif ($ext === 'pdf') {
        return Yii::$app->response->redirect($fileUrl);
    } else {
        $html = '<h3>Attached Document</h3>
                 <p>File: ' . basename($fileUrl) . '</p>
                 <p>(Original format: ' . strtoupper($ext) . ')</p>';
    }

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return Yii::$app->response->sendContentAsFile(
        $dompdf->output(),
        $fileName . ".pdf",
        [
            'mimeType' => 'application/pdf',
            'inline'   => false, 
        ]
    );
}
}
