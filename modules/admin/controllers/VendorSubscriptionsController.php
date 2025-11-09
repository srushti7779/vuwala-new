<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\VendorSubscriptions;
use app\modules\admin\models\search\VendorSubscriptionsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * VendorSubscriptionsController implements the CRUD actions for VendorSubscriptions model.
 */
class VendorSubscriptionsController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status','vendor-payments'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isACCountManager();
                        }

                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status','vendor-payments'],
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


    public function actionError() {}

    /**
     * Lists all VendorSubscriptions models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VendorSubscriptionsSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN ) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionVendorPayments()
    {
        $searchModel = new VendorSubscriptionsSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN ) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }else if (\Yii::$app->user->identity->user_role == User::ROLE_ACCOUNT_MANAGER) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        return $this->render('vendor_payments', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    /**
     * Displays a single VendorSubscriptions model.
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
        $model = new VendorSubscriptions();

        if ($model->loadAll(Yii::$app->request->post())) {
            // Check if the status is being set to Active
            if ($model->status == VendorSubscriptions::STATUS_ACTIVE) {
                // Set start_date as today
                $model->start_date = date('Y-m-d');
                $model->payment_status = VendorSubscriptions::PAYMENT_STATUS_SUCCESS;
                // Calculate end_date based on duration
                $model->end_date = date('Y-m-d', strtotime("+{$model->duration} days", strtotime($model->start_date)));


                     // if old status changed need to send notification
                $title = ($model->status == VendorSubscriptions::STATUS_ACTIVE) ? Yii::t("app", "Your Subscription Activated") : Yii::t("app", "Your Subscription Cancelled");
                $body = ($model->status == VendorSubscriptions::STATUS_ACTIVE) 
                    ? Yii::t("app", "Your subscription (#{$model->id}) has been activated and is valid until {$model->end_date}.") 
                    : Yii::t("app", "Your subscription (#{$model->id}) has been cancelled.");

                // Push notification to the user
                Yii::$app->notification->PushNotification(
                    $model->id, 
                    $model->vendorDetails->user_id, 
                    $title,
                    $body,
                    'subscription' 
                );

            }

            if ($model->saveAll()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }





    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post())) {
            // Check if the status is being updated to Active
            if ($model->status == VendorSubscriptions::STATUS_ACTIVE) {
                // Set start_date as today
                $model->start_date = date('Y-m-d');
                $model->payment_status = VendorSubscriptions::PAYMENT_STATUS_SUCCESS; 

                // Calculate end_date by adding duration (in days) to start_date
                $model->end_date = date('Y-m-d', strtotime("+{$model->duration} days", strtotime($model->start_date)));

                // Send push notification to the vendor
                $title = Yii::t("app", "Subscription Activated");
                $body = Yii::t("app", "Your subscription (ID: #{$model->id}) has been activated");  
                // $body = Yii::t("app", "Your subscription (ID: #{$model->id}) has been activated and is valid until {$model->end_date}.");

                Yii::$app->notification->PushNotification( 
                    $model->id, // Subscription ID as reference
                    $model->vendorDetails->user_id,
                    $title,
                    $body,
                    'subscription' // Type of notification
                );  
            } 

            if ($model->save(false)) { // Skipping validation if not required
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }



    /**
     * Deletes an existing VendorSubscriptions model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (!empty($model)) {
            $model->status ='';
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
            $model = VendorSubscriptions::find()->where([
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
     * Finds the VendorSubscriptions model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VendorSubscriptions the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorSubscriptions::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
