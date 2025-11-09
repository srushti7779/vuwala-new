<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\OrderComplaints;
use app\modules\admin\models\Orders;
use app\modules\admin\models\search\OrderComplaintsSearch;
use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OrderComplaintsController implements the CRUD actions for OrderComplaints model.
 */
class OrderComplaintsController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete','support'],
                        'roles' => ['@']
                    ],
                    [
                        'allow' => false
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all OrderComplaints models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderComplaintsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single OrderComplaints model.
     * @param integer $id
     * @return mixed
     */
public function actionView($id)
{
    $model = $this->findModel($id);

    $vendorComplaints = OrderComplaints::find()
        ->where(['store_id' => $model->store_id])
        ->all(); 
    return $this->render('view', [
        'model' => $model,
        'vendorComplaints' => $vendorComplaints,
    ]);
}



    /**
     * Creates a new OrderComplaints model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
   public function actionCreate()
{
    $model = new OrderComplaints();

    if (User::isVendor()) {
        $vendorDetails = \app\modules\admin\models\VendorDetails::findOne(['user_id' => Yii::$app->user->id]);
        if ($vendorDetails) {
            $model->store_id = $vendorDetails->id;  // Set the correct store_id
        }
    }

    if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
        return $this->redirect(['view', 'id' => $model->id]);
    } else {
        return $this->render('create', [
            'model' => $model,
        ]);
    }
}


    /**
     * Updates an existing OrderComplaints model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing OrderComplaints model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->deleteWithRelated();

        return $this->redirect(['index']);
    }

    
    /**
     * Finds the OrderComplaints model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderComplaints the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = OrderComplaints::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
 public function actionSupport($order_id)
{
    $order = Orders::findOne($order_id);
    if (!$order) {
        throw new NotFoundHttpException("Order not found.");
    }

    $model = new OrderComplaints();
    $model->order_id = $order->id;
    $model->user_id = Yii::$app->user->id;
    $model->store_id = Yii::$app->request->get('store_id') ?? $order->orderItems[0]->product->store_id ?? null;

    if ($model->load(Yii::$app->request->post())) {
        $model->store_id = Yii::$app->request->get('store_id') ?? $order->orderItems[0]->product->store_id ?? null;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Complaint submitted.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    return $this->render('support', [
        'model' => $model,
    ]);
}







}
