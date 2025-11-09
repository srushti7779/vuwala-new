<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\base\BannerChargeLogs;
use app\modules\admin\models\base\BannerTimings;
use Yii;
use app\models\User;
use app\modules\admin\models\Banner;
use app\modules\admin\models\search\BannerSearch;
use app\modules\admin\models\VendorDetails;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use \yii\helpers\ArrayHelper;
use yii\web\Response;




/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-banner-timings', 'vendors-by-category', 'check-sort'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status', 'vendors-by-category', 'check-sort'],
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
     * Lists all Banner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BannerSearch();
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
     * Displays a single Banner model.
     * @param integer $id
     * @return mixed
     */
public function actionView($id)
{
    $model = $this->findModel($id);

    $bannerTimings = $model->bannerTimings;
    $bannerChargeLogs = $model->bannerChargeLogs;
    $bannerRecharges = $model->bannerRecharges; 

    return $this->render('view', [
        'model' => $model,
        'providerBannerTimings' =>$bannerTimings,
        'bannerChargeLogs' => $bannerChargeLogs,
        'bannerRecharges' => $bannerRecharges, 
    ]);
}


 

    public function actionCreate()
    {
        $model = new Banner();

        if ($model->load(Yii::$app->request->post())) {
            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');

            if (empty($upload_image) || $upload_image == null) {
                $model->image = "";
            } else {
                $image = Yii::$app->notification->imageKitUpload($upload_image);
                $model->image = $image['url'];
            } 

            if ($model->saveAll()) {

                if ($model->id) {
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {

                    Yii::$app->session->setFlash('error', 'Failed to save the model.');
                    return $this->redirect(['index']);
                }
            } else {

                Yii::$app->session->setFlash('error', 'Failed to save the model.');
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Banner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $oldImage = $model['image'];

        if ($model->loadAll(Yii::$app->request->post(), ['user_id'])) {
            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');

            if (!empty($upload_image)) {

                $image = Yii::$app->notification->imageKitUpload($upload_image);

                $model->image = $image['url'];
            } else {
                $model->image = $oldImage;
            }

            $model->saveAll();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Banner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (!empty($model)) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }




 
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = Banner::find()->where([
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
     * Finds the Banner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Banner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for BannerTimings
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddBannerTimings()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('BannerTimings');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formBannerTimings', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }



    public function actionVendorsByCategory()
    {
        $mainCategoryId = Yii::$app->request->post('main_category_id');
        
        // Validate input
        if (!$mainCategoryId) {
            throw new \yii\web\BadRequestHttpException('main_category_id is required.');
        }
    
        // Fetch vendors based on main category ID 
        $vendors = VendorDetails::find()  
            ->select(['id', 'business_name']) // Adjust field names if needed
            ->where(['main_category_id' => $mainCategoryId, 'status' => VendorDetails::STATUS_ACTIVE]) // Include status filter
            ->asArray()
            ->all();
    
        // Format response as key-value pairs
        return Json::encode(ArrayHelper::map($vendors, 'id', 'business_name')); 
    }


    public function actionCheckSort()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sortValue = Yii::$app->request->post('sort');

        $existingBanner = Banner::findOne(['sort' => $sortValue, 'status' => Banner::STATUS_ACTIVE]);

        if ($existingBanner !== null) {
            return ['status' => 'taken'];
        } else {
            return ['status' => 'available'];
        }
    }

}
