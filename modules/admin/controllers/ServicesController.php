<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\Services;
use app\modules\admin\models\search\ServicesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ServicesController implements the CRUD actions for Services model.
 */
class ServicesController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'add-cart-items'],
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
     * Lists all Services models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ServicesSearch();
        if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUB_ADMIN){
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
     * Displays a single Services model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $providerCartItems = new \yii\data\ArrayDataProvider([
            'allModels' => $model->cartItems,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerCartItems' => $providerCartItems,
        ]);
    } 

    protected function generateSlug($string)
    {
        // Convert to lowercase, replace spaces with hyphens, and remove special characters
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($string)));
        $slug = trim($slug, '-'); // Remove any leading or trailing hyphens
        return $slug;
    }

 


// public function actionCreate()
// { 
//     $model = new Services();
//     $model->scenario = 'create';

//     if ($model->loadAll(Yii::$app->request->post())) {

//         $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');

//         if (empty($upload_image) || $upload_image == null) {
//             $model->image = "";
//         } else {
//             $image = Yii::$app->notification->imageKitUpload($upload_image);
//             $model->image = $image['url'];
//         }
//       // Generate the slug from the service_name in a URL-friendly format
//       $slug = $this->generateSlug($model->service_name) . '-' . $model->vendor_details_id;
//       $model->slug = $slug;
//       $model->price = $model->original_price;

        
//         if ($model->save()) {
      

//             // Save the model again with the generated slug
//             if ($model->save(false)) {
//                 Yii::$app->session->setFlash('success', Yii::t('app', 'Service created successfully.'));
//                 return $this->redirect(['index']);
//             } else {
//                 Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to update the slug.'));
//             }
//         } else {
//             Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to create the service.'));
//         }
//     }

//     return $this->render('create', [
//         'model' => $model,
//     ]);
// } 
 

public function actionCreate()
{ 
    $model = new Services();
    $model->scenario = 'create';

    if ($model->loadAll(Yii::$app->request->post())) {

        // Directly assign the image URL if it is provided in the POST request
        $image_url = Yii::$app->request->post('Services')['image'];  

        // If the image URL is not provided, set it to an empty string
        if (empty($image_url)) {
            $model->image = "";
        } else {
            $model->image = $image_url;  // Store the provided image URL directly
        }

        // Generate the slug from the service_name in a URL-friendly format
        $slug = $this->generateSlug($model->service_name) . '-' . $model->vendor_details_id;
        $model->slug = $slug;
        $model->price = $model->original_price; 

        if ($model->save()) {
            // Save the model again with the generated slug
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Service created successfully.'));
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to update the slug.'));
            }
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to create the service.'));
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}






    // public function actionUpdate($id)
    // {
    //     $model = $this->findModel($id);

    //     $image_old =$model->image;
    
    //     if ($model->loadAll(Yii::$app->request->post())) {

    //         $upload_image = \yii\web\UploadedFile::getInstance($model, 'image');
    //         if(!empty($upload_image)){
    //             $image = Yii::$app->notification->imageKitUpload($upload_image);
    //             $model->image = $image['url'];

    //         }else{
    //             $model->image = $image_old;
    //         }
    //         $model->price = $model->discount_price;


    //         // Generate a proper slug from the title
    //         $slug = $this->generateSlug($model->service_name) . '-' . $model->vendor_details_id;
    //         $model->slug = $slug;
    
    //         if ($model->save()) {
    //             Yii::$app->session->setFlash('success', Yii::t('app', 'Service updated successfully.'));
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         } else {
    //             Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to update the service.'));
    //         }
    //     }
    
    //     return $this->render('update', [
    //         'model' => $model,
    //     ]);
    // }

    

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        // Save the old image URL in case it's needed
        $image_old = $model->image;
    
        if ($model->loadAll(Yii::$app->request->post())) {
    
            // Retrieve the image URL directly from the form submission
            $image_url = Yii::$app->request->post('Services')['image'];
    
            // If a new image URL is provided, use it, otherwise keep the old image URL
            if (!empty($image_url)) {
                $model->image = $image_url;
            } else {
                $model->image = $image_old;  // Keep the old image if no new URL is provided
            }
    
            // Set the price to the discount price
            $model->price = $model->discount_price;
    
            // Generate the slug from the service name
            $slug = $this->generateSlug($model->service_name) . '-' . $model->vendor_details_id;
            $model->slug = $slug;
    
            // Save the updated model
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Service updated successfully.'));
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to update the service.'));
            }
        }
    
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    


    /**
     * Deletes an existing Services model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = Services::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = Services::find()->where([
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
     * Finds the Services model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Services the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Services::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for CartItems
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddCartItems()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('CartItems');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formCartItems', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
