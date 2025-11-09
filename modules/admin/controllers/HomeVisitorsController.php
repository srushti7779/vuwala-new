<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\HomeVisitors;
use app\modules\admin\models\search\HomeVisitorsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HomeVisitorsController implements the CRUD actions for HomeVisitors model.
 */
class HomeVisitorsController extends Controller
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
     * Lists all HomeVisitors models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HomeVisitorsSearch();
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
     * Displays a single HomeVisitors model.
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
     * Creates a new HomeVisitors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
public function actionCreate()
{
    $model = new HomeVisitors();

    if ($model->loadAll(Yii::$app->request->post())) {

        // Check if username or contact_number already exists in vendors
        $existingVendor = \app\modules\admin\models\User::find()
            ->where(['user_role' => 'vendor'])
            ->andWhere(['or',
                ['username' => $model->username],
                ['contact_no' => $model->contact_no]
            ])
            ->exists();

        if ($existingVendor) {
            Yii::$app->session->setFlash('error', 'A vendor with this username or contact number already exists.');
            return $this->render('create', ['model' => $model]);
        }

        if ($model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}


    /**
     * Updates an existing HomeVisitors model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
  public function actionUpdate($id)
{
    $model = $this->findModel($id);

    if ($model->loadAll(Yii::$app->request->post())) {

        // Check if username or contact_number already exists in vendors (excluding this record)
        $existingVendor = \app\modules\admin\models\User::find()
            ->where(['user_role' => 'vendor'])
            ->andWhere(['or',
                ['username' => $model->username],
                ['contact_number' => $model->contact_number]
            ])
            ->andWhere(['<>', 'id', $model->id]) // exclude current record
            ->exists();

        if ($existingVendor) {
            Yii::$app->session->setFlash('error', 'A vendor with this username or contact number already exists.');
            return $this->render('update', ['model' => $model]);
        }

        if ($model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}


    /**
     * Deletes an existing HomeVisitors model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = HomeVisitors::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = HomeVisitors::find()->where([
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
     * Finds the HomeVisitors model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return HomeVisitors the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HomeVisitors::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
