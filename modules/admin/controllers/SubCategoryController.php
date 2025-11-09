<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\SubCategory;
use app\modules\admin\models\search\SubCategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SubCategoryController implements the CRUD actions for SubCategory model.
 */
class SubCategoryController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status','get-vendors-by-category'],
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
     * Lists all SubCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SubCategorySearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetVendorsByCategory($category_id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $vendors = \app\modules\admin\models\VendorDetails::find()
            ->where(['main_category_id' => $category_id]) // Filter vendors by category
            ->orderBy('business_name')
            ->asArray()
            ->all();

        return array_map(function($vendor) { return ['id' => $vendor['id'], 'text' => $vendor['business_name']]; }, $vendors);  
    } 
 

    /**
     * Displays a single SubCategory model.
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
     * Creates a new SubCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SubCategory();

        if ($model->loadAll(Yii::$app->request->post())) {

            $slug = User::generateUniqueSlug($model->title);
            $model->slug = $slug;

            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');


            if (empty($upload_image) || $upload_image == null) {
                $model->image = "";
            } else {
                $image = Yii::$app->notification->imageKitUpload($upload_image);
                $model->image = $image['url'];
            } 



            $model->saveAll();

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }




    /**
     * Updates an existing SubCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldImage = $model['image'];


        if ($model->loadAll(Yii::$app->request->post())) {

            $slug = User::generateUniqueSlug($model->title);
            $model->slug = $slug;
            $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');

            if (!empty($upload_image)) {

                $image = Yii::$app->notification->imageKitUpload($upload_image);

                $model->image = $image['url'];
            } else {
                $model->image = $oldImage;
            }



            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SubCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (!empty($model)) {
            $model->status = SubCategory::STATUS_DELETE;
            $model->save(false);
        }

        return $this->redirect(['index']);
    }

    public function actionUpdateStatus()
    {
        $data = [];
        $post = \Yii::$app->request->post();
        \Yii::$app->response->format = 'json';
        if (! empty($post['id'])) {
            $model = SubCategory::find()->where([
                'id' => $post['id'],
            ])->one();
            if (!empty($model)) {

                $model->status = $post['val'];
            }
            if ($model->save(false)) {
                $data['message'] = "Updated";
                $data['id'] = $model->status;
            } else {
                $data['message'] = "Not Updated";
            }
        }
        return $data;
    }


    /**
     * Finds the SubCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SubCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SubCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
