<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\Reels;
use app\modules\admin\models\search\ReelsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ReelsController implements the CRUD actions for Reels model.
 */
class ReelsController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-reel-share-counts', 'add-reel-tags', 'add-reels-likes', 'add-reels-view-counts'],
                        'matchCallback' => function () {
                            return User::isAdmin()||User::isVendor()|| User::isSubAdmin();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status'],
                        'matchCallback' => function () {
                            return User::isManager()||User::isVendor();
                        }
                    ],
                    [
                        'allow' => true
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all Reels models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReelsSearch();
        if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN || Yii::$app->user->identity->user_role==User::ROLE_VENDOR){
            // For Admin, SubAdmin and Vendor
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
     * Displays a single Reels model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerReelShareCounts = new \yii\data\ArrayDataProvider([
            'allModels' => $model->reelShareCounts,
        ]);
        $providerReelTags = new \yii\data\ArrayDataProvider([
            'allModels' => $model->reelTags,
        ]);
        $providerReelsLikes = new \yii\data\ArrayDataProvider([
            'allModels' => $model->reelsLikes,
        ]);
        $providerReelsViewCounts = new \yii\data\ArrayDataProvider([
            'allModels' => $model->reelsViewCounts,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerReelShareCounts' => $providerReelShareCounts,
            'providerReelTags' => $providerReelTags,
            'providerReelsLikes' => $providerReelsLikes,
            'providerReelsViewCounts' => $providerReelsViewCounts,
        ]);
    }

    /**
     * Creates a new Reels model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

public function actionCreate()
{
    $model = new Reels();
    if ($model->load(Yii::$app->request->post())) {
        $videoFile = UploadedFile::getInstance($model, 'video');
        $thumbnailFile = UploadedFile::getInstance($model, 'thumbnail');

        $model->video = $videoFile;
        $model->thumbnail = $thumbnailFile;

        if ($model->validate()) {
            if ($videoFile) {
                $videoUploadResult = Yii::$app->notification->imageKitUpload($videoFile);
                $model->video = $videoUploadResult['url'] ?? null;
            }
            if ($thumbnailFile) {
                $thumbUploadResult = Yii::$app->notification->imageKitUpload($thumbnailFile);
                $model->thumbnail = $thumbUploadResult['url'] ?? null;
            }
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Reels Created successfully!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to save reel.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Validation failed. Please fix the errors below.');
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}


    /**
     * Updates an existing Reels model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
  public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $oldVideo = $model->video;
    $oldThumbnail = $model->thumbnail;

    if ($model->load(Yii::$app->request->post())) {
        $videoFile = UploadedFile::getInstance($model, 'video');
        $thumbnailFile = UploadedFile::getInstance($model, 'thumbnail');

        // Temporarily assign to model for validation
        $model->video = $videoFile ?: $oldVideo;
        $model->thumbnail = $thumbnailFile ?: $oldThumbnail;

        if ($model->validate()) {
            if ($videoFile) {
                $videoUploadResult = Yii::$app->notification->imageKitUpload($videoFile);
                $model->video = $videoUploadResult['url'] ?? $oldVideo;
            }

            if ($thumbnailFile) {
                $thumbUploadResult = Yii::$app->notification->imageKitUpload($thumbnailFile);
                $model->thumbnail = $thumbUploadResult['url'] ?? $oldThumbnail;
            }

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Reel updated successfully!');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update reel.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Validation failed. Please fix the errors below.');
        }
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}


    /**
     * Deletes an existing Reels model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = Reels::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = Reels::find()->where([
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
     * Finds the Reels model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Reels the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Reels::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for ReelShareCounts
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddReelShareCounts()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('ReelShareCounts');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formReelShareCounts', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for ReelTags
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddReelTags()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('ReelTags');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formReelTags', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for ReelsLikes
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddReelsLikes()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('ReelsLikes');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formReelsLikes', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for ReelsViewCounts
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddReelsViewCounts()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('ReelsViewCounts');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formReelsViewCounts', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
