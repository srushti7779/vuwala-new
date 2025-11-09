<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\ServiceType;
use app\modules\admin\models\search\ServiceTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
 
/**
 * ServiceTypeController implements the CRUD actions for ServiceType model.
 */
class ServiceTypeController extends Controller
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
     * Lists all ServiceType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ServiceTypeSearch();
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
     * Displays a single ServiceType model.
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
     * Creates a new ServiceType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
{
    $model = new ServiceType();

    if ($model->loadAll(Yii::$app->request->post())) {

        // Ensure checkbox is properly set
        $model->is_tax_allowed = Yii::$app->request->post('ServiceType')['is_tax_allowed'] ?? 0;

        // Handle image upload
        $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');
        if ($upload_image) {
            $image = Yii::$app->notification->imageKitUpload($upload_image);
            $model->image = $image['url'] ?? '';
        } else {
            $model->image = '';
        }

        // Save model
        if ($model->saveAll()) {
            Yii::$app->session->setFlash('success', 'Service Type created successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to create Service Type.');
        }

        return $this->redirect(['index']);
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}



    /**
     * Updates an existing ServiceType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldImage = $model['image'];
    
        if ($model->loadAll(Yii::$app->request->post())) {
    
            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');
    
            if (!empty($upload_image)) {
                $image = Yii::$app->notification->imageKitUpload($upload_image);
                $model->image = $image['url'];
            } else {
                $model->image = $oldImage;
            }
    
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Data updated successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update the data.');
            }
    
            return $this->redirect(['index']);
        }
    
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    
    /**
     * Deletes an existing ServiceType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = ServiceType::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = ServiceType::find()->where([
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
     * Finds the ServiceType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ServiceType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ServiceType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
