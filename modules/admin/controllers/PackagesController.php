<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\base\ComboServices;
use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\search\ComboPackagesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ComboPackagesController implements the CRUD actions for ComboPackages model.
 */
class PackagesController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-combo-services'],
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
     * Lists all ComboPackages models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ComboPackagesSearch();
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
     * Displays a single ComboPackages model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerComboServices = new \yii\data\ArrayDataProvider([
            'allModels' => $model->comboServices,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerComboServices' => $providerComboServices,
        ]);
    }

    /**
     * Creates a new ComboPackages model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
public function actionCreate()
{
    $model = new ComboPackages();

    $user_id = Yii::$app->user->id; 
    $vendor = VendorDetails::findOne([
        'user_id' => $user_id, 
        'status' => VendorDetails::STATUS_ACTIVE
    ]);
    $vendorId = $vendor ? $vendor->id : null;
    $model->vendor_details_id = $vendorId;

    if ($model->load(Yii::$app->request->post())) {

        $duplicate = ComboPackages::find()
            ->where([
                'vendor_details_id' => $model->vendor_details_id,
                'title'             => $model->title,
            ])
            ->exists();

        if ($duplicate) {
            Yii::$app->session->setFlash('error', 'A package with this title already exists for this vendor.');
            return $this->render('create', [
                'model'    => $model,
                'vendorId' => $vendorId,
            ]);
        }

        if ($model->save()) {
            try {
                if (!empty($model->services_ids)) {
                    foreach ($model->services_ids as $serviceId) {
                        $comboService = new ComboServices();
                        $comboService->combo_package_id  = $model->id;
                        $comboService->vendor_details_id = $model->vendor_details_id;
                        $comboService->services_id       = $serviceId;
                        $comboService->status            = 1;
                        $comboService->save(false);
                    }
                }

                Yii::$app->session->setFlash('success', 'Package created successfully');
                return $this->redirect(['index']);

            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while saving services: ' . $e->getMessage());
            }
        } else {
            Yii::$app->session->setFlash('error', 'Failed to create package. Please check the form.');
        }
    }

    return $this->render('create', [
        'model'    => $model,
        'vendorId' => $vendorId, 
    ]);
}


public function actionUpdate($id)
{
    $model = $this->findModel($id);

    $model->services_ids = $model->getServices()->select('id')->column();

    if ($model->load(Yii::$app->request->post())) {

        $duplicate = ComboPackages::find()
            ->where([
                'vendor_details_id' => $model->vendor_details_id,
                'title'             => $model->title,
            ])
            ->andWhere(['<>', 'id', $model->id])
            ->exists();

        if ($duplicate) {
            Yii::$app->session->setFlash('error', 'Another package with this title already exists for this vendor.');
            return $this->render('update', [
                'model' => $model,
            ]);
        }

        if ($model->save()) {
            try {

                if (!empty($model->services_ids)) {
                    foreach ($model->services_ids as $serviceId) {
                        $comboService = new ComboServices();
                        $comboService->combo_package_id  = $model->id;
                        $comboService->vendor_details_id = $model->vendor_details_id;
                        $comboService->services_id       = $serviceId;
                        $comboService->status            = 1;
                        $comboService->save(false);
                    }
                }

                Yii::$app->session->setFlash('success', 'Package updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error while updating services: ' . $e->getMessage());
            }
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update package. Please check the form.');
        }
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}



    /**
     * Deletes an existing ComboPackages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = ComboPackages::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = ComboPackages::find()->where([
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
     * Finds the ComboPackages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ComboPackages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ComboPackages::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for ComboServices
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddComboServices()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('ComboServices');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formComboServices', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
