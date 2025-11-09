<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\BusinessImages;
use app\modules\admin\models\search\BusinessImagesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BusinessImagesController implements the CRUD actions for BusinessImages model.
 */
class BusinessImagesController extends Controller
{

    public $userRole;

    public function __construct($id, $module, $config = [])
    {
        $this->userRole = \Yii::$app->user->identity->user_role;
        parent::__construct($id, $module, $config);
    }
    public $adminRoles = [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_QA,User::ROLE_VENDOR];


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
                        'actions' => ['index', 'view', 'create', 'update', 'bulk-delete','ajax-delete','delete', 'update-status'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin()|| User::isVendor()||User::isQa();
                        }

                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf',  'bulk-delete','ajax-delete','update-status'],
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
     * Lists all BusinessImages models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BusinessImagesSearch();
        if (in_array($this->userRole, $this->adminRoles)) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BusinessImages model.
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

    public function actionCreate()
{
    $model = new BusinessImages();
    $model->scenario = 'create';
    $vendor_details_id = Yii::$app->request->get('vendor_details_id') ?? null;
    
    if ($model->loadAll(Yii::$app->request->post())) {
    
        // Get all uploaded image instances
        $upload_images = \yii\web\UploadedFile::getInstances($model, 'image_file');
    
        if (empty($upload_images)) {
            Yii::$app->session->setFlash('error', 'No images uploaded.');
            return $this->render('create', ['model' => $model, 'vendor_details_id' => $vendor_details_id]);
        }
    
        $allImagesUploadedSuccessfully = true; 
    
        // Loop through each uploaded image and process
        foreach ($upload_images as $uploadedImage) {
    
            // Upload to image service (e.g. ImageKit)
            $imageUpload = Yii::$app->notification->imageKitUpload($uploadedImage);
            $image_url = $imageUpload['url'] ?? null;
    
            if (!empty($image_url)) {
                $newImage = new BusinessImages();
                $newImage->vendor_details_id = $vendor_details_id ?? $model->vendor_details_id;
                $newImage->status = BusinessImages:: STATUS_ACTIVE;
                $newImage->image_file = $image_url;
    
                if (!$newImage->save(false)) {
                    Yii::$app->session->setFlash('error', 'Failed to save image: ' . $uploadedImage->name);
                    $allImagesUploadedSuccessfully = false; 
                }
            } else {
                Yii::$app->session->setFlash('error', 'Image upload failed: ' . $uploadedImage->name);
                $allImagesUploadedSuccessfully = false; 
            }
        }
    
        // Set success message if all images are uploaded and saved successfully
        if ($allImagesUploadedSuccessfully) {
            Yii::$app->session->setFlash('success', 'Business Images Uploaded Successfully!');
        }
    
        // Redirect after all images processed
        if (!empty($vendor_details_id)) {
            return $this->redirect(['/admin/vendor-details/view', 'id' => $vendor_details_id]);
        }
    
        return $this->redirect(['index']);
    }
    
    return $this->render('create', ['model' => $model, 'vendor_details_id' => $vendor_details_id]);
}


public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $vendor_details_id = Yii::$app->request->get('vendor_details_id') ?? $model->vendor_details_id;

    // Store existing images before loading form data
    $existingImages = $model->image_file;

    if ($model->load(Yii::$app->request->post())) {

        $upload_images = \yii\web\UploadedFile::getInstances($model, 'image_file');

        if (!empty($upload_images)) {
            $newImages = [];
            foreach ($upload_images as $uploadedImage) {
                $imageUpload = Yii::$app->notification->imageKitUpload($uploadedImage);
                if (!empty($imageUpload['url'])) {
                    $newImages[] = $imageUpload['url'];
                }
            }

            // Only update image_file if there are new images
            if (!empty($newImages)) {
                // Append new images to existing ones
                if (!empty($existingImages)) {
                    $model->image_file = $existingImages . ',' . implode(',', $newImages);
                } else {
                    $model->image_file = implode(',', $newImages);
                }
            } else {
                // No new images uploaded, keep existing ones
                $model->image_file = $existingImages;
            }
        } else {
            // No files uploaded, keep existing images
            $model->image_file = $existingImages;
        }

        // Save the model
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Images updated successfully.');
            return $this->redirect(['/admin/vendor-details/view', 'id' => $vendor_details_id]);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update images.');
        }
    }

    return $this->render('update', [
        'model' => $model,
        'vendor_details_id' => $vendor_details_id
    ]);
}







    /**
     * Deletes an existing BusinessImages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
    
        if ($model !== null) {
            $vendor_details_id = $model->vendor_details_id;
            $model->delete();
    
            Yii::$app->session->setFlash('success', 'Business document deleted successfully.');
    
            return $this->redirect(['/admin/vendor-details/view', 'id' => $vendor_details_id]);
        }
    
        Yii::$app->session->setFlash('danger', 'Document not found.');
        return $this->redirect(['index']);
    }
    

    public function actionUpdateStatus()
    {
        $data = [];
        $post = \Yii::$app->request->post();
        \Yii::$app->response->format = 'json';
        if (! empty($post['id'])) {
            $model = BusinessImages::find()->where([
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
     * Finds the BusinessImages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BusinessImages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BusinessImages::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
     public function actionBulkDelete()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $ids = Yii::$app->request->post('ids', []);
    if (!empty($ids)) {
        $count = BusinessImages::deleteAll(['id' => $ids]);
        if ($count > 0) {
            return ['status' => 'success', 'message' => "$count Businessimage(s) deleted successfully."];
        }
        return ['status' => 'error', 'message' => 'No images were deleted.'];
    }
    return ['status' => 'error', 'message' => 'No images selected.'];
}

public function actionAjaxDelete($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $model = BusinessImages::findOne($id);
    if ($model && $model->delete()) {
        return ['status' => 'success', 'message' => 'BusinessImages deleted successfully.'];
    }
    return ['status' => 'error', 'message' => 'Failed to delete image or image not found.'];
}
}
