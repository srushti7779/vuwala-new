<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\MainCategory;
use app\modules\admin\models\search\MainCategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MainCategoryController implements the CRUD actions for MainCategory model.
 */
class MainCategoryController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-sub-category', 'add-vendor-details'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isVendor();
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
     * Lists all MainCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MainCategorySearch();
        if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN||Yii::$app->user->identity->user_role==User::ROLE_VENDOR){
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
     * Displays a single MainCategory model.
     * @param integer $id
     * @return mixed
     */ 
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerSubCategory = new \yii\data\ArrayDataProvider([
            'allModels' => $model->subCategories,
        ]);
        $providerVendorDetails = new \yii\data\ArrayDataProvider([
            'allModels' => $model->vendorDetails,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerSubCategory' => $providerSubCategory,
            'providerVendorDetails' => $providerVendorDetails,
        ]);
    }

    /**
     * Creates a new MainCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MainCategory();

        if ($model->loadAll(Yii::$app->request->post())) {

            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');
            $upload_icon = \yii\web\UploadedFile::getInstance($model, 'icon');

                if(!empty($upload_image)){
                $image = Yii::$app->notification->imageKitUpload($upload_image);
                $model->image = $image['url'];
                }
           
                if(!empty( $upload_icon)){
                $icon = Yii::$app->notification->imageKitUpload($upload_icon);
                $model->icon = $icon['url'];
                }
           



            $model->saveAll();
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing MainCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post())) {



            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');
            $upload_icon = \yii\web\UploadedFile::getInstance($model, 'icon');

                if(!empty($upload_image)){
                $image = Yii::$app->notification->imageKitUpload($upload_image);
                $model->image = $image['url'];
                }else{
                $model->image = $model->getOldAttribute('image');
                }
           
                if(!empty( $upload_icon)){
                $icon = Yii::$app->notification->imageKitUpload($upload_icon);
                $model->icon = $icon['url'];
                }else{
                $model->icon = $model->getOldAttribute('icon');
                }
           

            $model->save();
            Yii::$app->session->setFlash('success','Main Categorys Updated Successsfully');
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MainCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = MainCategory::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = MainCategory::find()->where([
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
     * Finds the MainCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MainCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MainCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for SubCategory
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddSubCategory()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('SubCategory');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formSubCategory', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for VendorDetails
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddVendorDetails()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('VendorDetails');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formVendorDetails', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
