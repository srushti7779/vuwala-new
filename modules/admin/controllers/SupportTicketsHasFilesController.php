<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\SupportTickets;
use Yii;
use app\models\User;
use app\modules\admin\models\SupportTicketsHasFiles;
use app\modules\admin\models\search\SupportTicketsHasFilesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * SupportTicketsHasFilesController implements the CRUD actions for SupportTicketsHasFiles model.
 */
class SupportTicketsHasFilesController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status'],
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
     * Lists all SupportTicketsHasFiles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupportTicketsHasFilesSearch();
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
     * Displays a single SupportTicketsHasFiles model.
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
     * Creates a new SupportTicketsHasFiles model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
 

public function actionCreate()
{
    $model = new SupportTickets();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {

        // Handle multiple files from TabularForm
        $postFiles = Yii::$app->request->post('SupportTicketsHasFiles');
        $uploadedFiles = UploadedFile::getInstancesByName('SupportTicketsHasFiles[file]');

        if (!empty($postFiles)) {
            foreach ($postFiles as $index => $fileData) {
                $uploadedFile = isset($uploadedFiles[$index]) ? $uploadedFiles[$index] : null;

                if ($uploadedFile) {
                    // Upload file to ImageKit
                    $imageKitResponse = Yii::$app->notification->imageKitUpload($uploadedFile);

                    if (!empty($imageKitResponse['url'])) {
                        $fileModel = new SupportTicketsHasFiles();
                        $fileModel->support_ticket_id = $model->id;
                        $fileModel->file = $imageKitResponse['url']; // Store ImageKit URL
                        $fileModel->status = $fileData['status'] ?? 1;
                        $fileModel->save(false);
                    } else {
                        Yii::error("ImageKit upload failed for file: {$uploadedFile->name}");
                    }
                }
            }
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('create', ['model' => $model]);
}


    /**
     * Updates an existing SupportTicketsHasFiles model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
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
     * Deletes an existing SupportTicketsHasFiles model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = SupportTicketsHasFiles::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = SupportTicketsHasFiles::find()->where([
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
     * Finds the SupportTicketsHasFiles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupportTicketsHasFiles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupportTicketsHasFiles::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
