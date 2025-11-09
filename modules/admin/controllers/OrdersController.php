<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\modules\admin\models\base\Orders as BaseOrders;
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\Orders;
use app\modules\admin\models\Wallet;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\search\OrdersSearch;
use app\modules\admin\models\Services;
use app\modules\admin\models\Staff;
use app\modules\admin\models\VendorEarnings;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Dompdf\Dompdf;
use Dompdf\Options; 


/**
 * OrdersController implements the CRUD actions for Orders model.
 */
class OrdersController extends Controller
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
                        'actions' => [
                            'index',
                            'view',
                            'create',
                            'create-by-vendor',
                            'view_vendor',
                            'update',
                            'delete',
                            'update-status',
                            'add-coupons-applied',
                            'add-order-details',
                            'add-vendor-earnings',
                            'update-status',
                            'update-order-status',
                            'accepted-orders',
                            'new-orders',
                            'service-started',
                            'service-completed',
                            'expired-orders',
                            'assign-home-visitor',
                            'reassign-home-visitor',
                            'download-pdf',
                            'approve-order'
                        ],
                        'matchCallback' => function () {
                            return User::isAdmin() ||User::isVendor()|| User::isSubAdmin();
                        }

                    ],
                    [
                        'allow' => true,
                        'actions' => [

                            'index',
                            'view',
                            'create',
                            'create-by-vendor',
                            'view_vendor',
                            'update',
                            'delete',
                            'update-status',
                            'add-coupons-applied',
                            'add-order-details',
                            'add-vendor-earnings',
                            'update-status',
                            'update-order-status',
                            'accepted-orders',
                            'new-orders',
                            'service-started',
                            'service-completed',
                            'expired-orders',
                            'assign-home-visitor',
                            'reassign-home-visitor',
                            'download-pdf',
                            'approve-order'


                        ],
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
     * Lists all Orders models.
     * @return mixed
     */
    public function actionIndex()
    {
       
        $searchModel = new OrdersSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {

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
     * Displays a single Orders model.
     * @param integer $id
     * @return mixed
     */
public function actionView($id)
{
    $model = $this->findModel($id);
    
    // Existing providers
    $providerCouponsApplied = new \yii\data\ArrayDataProvider([
        'allModels' => $model->couponsApplieds,
    ]);

    $providerOrderDetails = new \yii\data\ArrayDataProvider([
        'allModels' => $model->orderDetails,
    ]);

    $providerVendorEarnings = new \yii\data\ArrayDataProvider([
        'allModels' => $model->vendorEarnings,
    ]);

    // ✅ Filter combo orders once (skip 0 package_id)
    $validComboOrders = array_filter($model->comboOrders, function($combo) {
        return $combo->combo_package_id != 0;
    });

    // ✅ Create provider only once
    $providerComboOrders = new \yii\data\ArrayDataProvider([
        'allModels' => $validComboOrders,
        'pagination' => [
            'pageSize' => 10, 
        ],
        'sort' => [
            'attributes' => ['id', 'combo_package_id', 'amount', 'created_on'],
        ],
    ]);
    $providerProductServiceOrders = new \yii\data\ArrayDataProvider([
        'allModels' => $model->productServiceOrders,
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'attributes' => ['id', 'product_order_id', 'status', 'created_on'],
        ],
    ]);



    return $this->render('view', [
        'model' => $model,
        'providerCouponsApplied' => $providerCouponsApplied,
        'providerOrderDetails' => $providerOrderDetails,
        'providerVendorEarnings' => $providerVendorEarnings,
        'providerComboOrders' => $providerComboOrders,
        'providerProductServiceOrders' =>$providerProductServiceOrders 
    ]);
}


    /**
     * Creates a new Orders model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Orders();

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
 public function actionCreateByVendor()
{
    $request = Yii::$app->request;
    $model = new Orders();

    // Fetch Vendor ID
    $vendor = VendorDetails::find()
        ->select(['id'])
        ->where(['user_id' => Yii::$app->user->id])
        ->asArray()
        ->one();

    if (!$vendor) {
        Yii::$app->session->setFlash('error', 'Vendor profile not found.');
        return $this->redirect(['site/index']);
    }

    $vendorId = $vendor['id'];

    // Get selected type & gender from POST/GET/default
    $type = $request->post('Orders')['type'] ?? $request->get('type', Services::TYPE_WALK_IN);
    $gender = $request->post('Orders')['gender'] ?? $request->get('gender', Services::SERVICE_FOR_UNISEX);
    $model->type = $type;
    $model->gender = $gender;
    $isFullSubmit = $request->isPost && $request->post('Orders') && !empty($request->post('Orders')['services']);

    if ($isFullSubmit && $model->load($request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->vendor_details_id = $vendorId;
            $model->qty = count((array)$request->post('Orders')['services']);
            $model->created_on = date('Y-m-d H:i:s');
            $model->create_user_id = Yii::$app->user->id;

            if (!$model->save()) {
                throw new \Exception('Failed to save order: ' . json_encode($model->getErrors()));
            }

            $services = (array)$request->post('Orders')['services'];
            foreach ($services as $serviceId) {
                $service = Services::findOne($serviceId);
                if (!$service) {
                    throw new \Exception("Service not found for ID: {$serviceId}");
                }

                $detail = new OrderDetails([
                    'order_id' => $model->id,
                    'service_id' => $serviceId,
                    'price' => $service->price,
                    'qty' => 1,
                    'total_price' => $service->price,
                    'is_next_visit' => 0,
                    'is_package_service' => 0,
                    'status' => OrderDetails::STATUS_ACTIVE,
                    'created_on' => date('Y-m-d H:i:s'),
                    'create_user_id' => Yii::$app->user->id,
                ]);

                if (!$detail->save()) {
                    throw new \Exception('Failed to save order detail: ' . json_encode($detail->getErrors()));
                }
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Order created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::$app->session->setFlash('error', 'Error creating order: ' . $e->getMessage());
        }
    }
    $services = Services::find()
        ->select(['id', 'service_name', 'duration', 'price', 'type', 'service_for'])
        ->where(['status' => Services::STATUS_ACTIVE])
        ->andWhere(['type' => $type])
        ->andWhere([
            'or',
            ['service_for' => $gender],
            ['service_for' => Services::SERVICE_FOR_UNISEX],
        ])
        ->orderBy('service_name')
        ->asArray()
        ->all();

    return $this->render('create-by-vendor', [
        'model' => $model,
        'services' => $services,
        'selectedServices' => (array)($request->post('Orders')['services'] ?? []),
        'type' => $type,
        'gender' => $gender,
    ]);
}

    /**
     * Updates an existing Orders model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Deletes an existing Orders model.
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



    public function actionNewOrders()
    {
        $searchModel = new OrdersSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', Orders::STATUS_NEW_ORDER);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAcceptedOrders()
    {
        $searchModel = new OrdersSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', Orders::STATUS_ACCEPTED);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

  public function actionServiceStarted()
{
    $searchModel = new OrdersSearch();

    $userRole = \Yii::$app->user->identity->user_role;
    $shopId = \Yii::$app->user->identity->shop_id ?? null;

    if ($userRole == User::ROLE_ADMIN || $userRole == User::ROLE_SUB_ADMIN) {
        // Admins and Sub-Admins see all service started orders
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', Orders::STATUS_SERVICE_STARTED);
    } else if ($userRole == User::ROLE_VENDOR) {
        // Vendors see only their own shop's service started orders
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $shopId, Orders::STATUS_SERVICE_STARTED);
    } else {
      
        throw new \yii\web\ForbiddenHttpException("You are not allowed to access this page.");
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}


    public function actionServiceCompleted()
    {
        $searchModel = new OrdersSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', Orders::STATUS_SERVICE_COMPLETED);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    public function actionExpiredOrders()
    {
        $searchModel = new OrdersSearch();
        if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUB_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $date = date('Y-m-d');
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '', Orders::STATUS_NEW_ORDER, $date);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }






    public function actionUpdateStatus()
    {
        $data = [];
        $post = \Yii::$app->request->post();
        \Yii::$app->response->format = 'json';
        if (!empty($post['id'])) {
            $model = Orders::find()->where([
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




    public function actionUpdateOrderStatus()
    {
        $data = [];
        $post = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($post['id'])) {
            $data['message'] = 'Missing order ID';
            return $data;
        }

        $model = Orders::findOne($post['id']);
        if (!$model) {
            $data['message'] = 'Order not found';
            return $data;
        }

        try {
            $newStatus = $post['val'];
            $model->status = $newStatus;

            if (in_array($newStatus, [
                Orders::STATUS_CANCELLED_BY_ADMIN,
                Orders::STATUS_CANCELLED_BY_USER,
                Orders::STATUS_CANCELLED_BY_OWNER,
                Orders::STATUS_CANCELLED_BY_HOME_VISITORS,
            ])) {
                $commission = VendorEarnings::findOne(['order_id' => $model->id]);
                if ($commission) {
                    $commission->status = VendorEarnings::STATUS_CANCELLED;
                    if (!$commission->update(false)) {
                        Yii::error("Failed to update VendorEarnings commission status for order {$model->id}", __METHOD__);
                        $data['message'] = 'Failed to update commission status';
                        return $data;
                    }
                }

                if ($model->payment_type != Orders::TYPE_COD) {
                    $wallet = new Wallet();
                    $wallet->order_id = $model->id;
                    $wallet->user_id = $model->user_id;
                    $wallet->amount = $model->total_w_tax;
                    $wallet->payment_type = Wallet::STATUS_CREDITED;
                    $wallet->method_reason = Yii::t("app", "Order cancelled. Order ID #") . $model->id;
                    $wallet->status = Wallet::STATUS_COMPLETED;

                    if (!$wallet->save(false)) {
                        throw new ServerErrorHttpException(Yii::t("app", "Failed to process wallet refund."));
                    }
                }
            } elseif ($newStatus == Orders::STATUS_SERVICE_COMPLETED) {
                $getCommission = VendorEarnings::findOne(['order_id' => $model->id]);
                if ($getCommission) {
                    $getCommission->status = VendorEarnings::STATUS_APPROVED;
                    if (!$getCommission->update(false)) {
                        Yii::error("Failed to update VendorEarnings commission status for order {$model->id}", __METHOD__);
                        $data['message'] = 'Failed to update commission status';
                        return $data;
                    }
                }
            }

            // Log order status change
            $orderStatus = new OrderStatus();
            $orderStatus->order_id = $model->id;
            $orderStatus->status = $newStatus;
            $orderStatus->remarks = Yii::t("app", "Order status updated to ") . $model->getStateOptionsBadges();
            if (!$orderStatus->save(false)) {
                Yii::error("Failed to log order status change for order {$model->id}", __METHOD__);
                $data['message'] = 'Failed to log order status change';
                return $data;
            }

            // Save the updated order status
            if ($model->save(false)) {



                // Send notification to user
                // Send notification to user
                $title = "Your Order Updated";
                $body = "Your order #{$model->id} status has been updated to " . strip_tags($model->getStateOptionsBadges());
                Yii::$app->notification->PushNotification($model->id, $model->user_id, $title, $body, 'redirect');

                // **Send notification to the vendor**
                $vendor = $model->vendorDetails->user_id;
                if ($vendor) {
                    $vendorTitle = "Order Update";
                    $vendorBody = "Order #{$model->id} status has been updated to " . strip_tags($model->getStateOptionsBadges());
                    Yii::$app->notification->PushNotification($model->id, $vendor, $vendorTitle, $vendorBody, 'redirect');
                }


                // If service is completed, update staff status
                if ($model->status == Orders::STATUS_SERVICE_COMPLETED) {
                    $home_visitors_has_orders = HomeVisitorsHasOrders::findOne(['order_id' => $model->id]);
                    if (!empty($home_visitors_has_orders)) {
                        $home_visitor_id  = $home_visitors_has_orders->home_visitor_id;
                        $staff = Staff::findOne(['id' => $home_visitor_id]);
                        if (!empty($staff)) {
                            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
                            $staff->save(false);
                        }
                    }
                }

                $data['message'] = 'Order status updated successfully';
                $data['status'] = 'success';
                $data['order_id'] = $model->id;
            } else {
                Yii::error("Failed to save order status for order {$model->id}", __METHOD__);
                $data['message'] = 'Failed to update order status';
            }
        } catch (\Exception $e) {
            Yii::error("Error updating order status for order {$model->id}: " . $e->getMessage(), __METHOD__);
            $data['message'] = 'An error occurred while updating the order status: ' . $e->getMessage();
        }

        return $data;
    }


    /**
     * Finds the Orders model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Orders the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Orders::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for CouponsApplied
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddCouponsApplied()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('CouponsApplied');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formCouponsApplied', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for OrderDetails
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddOrderDetails()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('OrderDetails');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formOrderDetails', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for VendorEarnings
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddVendorEarnings()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('VendorEarnings');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formVendorEarnings', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }



    public function actionAssignHomeVisitor()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $home_visitor_id = $post['Orders']['home_visitor_id'] ?? null;
        $order_id = $post['Orders']['id'] ?? null;

        if (empty($home_visitor_id) || empty($order_id)) {
            return ['status' => 'error', 'message' => 'Home visitor ID and order ID are required.'];
        }

        // Find the order by order_id
        $order = Orders::findOne($order_id);
        if (!$order) {
            return ['status' => 'error', 'message' => 'Invalid order ID. Order not found.'];
        }

        // Find the home visitor by home_visitor_id
        $staff = Staff::findOne(['id' => $home_visitor_id]);
        if (!$staff) {
            return ['status' => 'error', 'message' => 'Invalid home visitor ID. Home visitor not found.'];
        }

        // Check if the home visitor is available
        if ($staff->current_status != Staff::CURRENT_STATUS_IDLE && !empty($staff->current_status)) {
            return ['status' => 'error', 'message' => 'Home visitor is currently busy and cannot be assigned to a new order.'];
        }

        // Start database transaction   
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Assign home visitor to the order
            $home_visitors_has_orders = new HomeVisitorsHasOrders();
            $home_visitors_has_orders->order_id = $order_id;
            $home_visitors_has_orders->home_visitor_id = $home_visitor_id;
            $home_visitors_has_orders->status = HomeVisitorsHasOrders::STATUS_ACTIVE;

            if (!$home_visitors_has_orders->save()) {
                throw new \Exception('Failed to assign home visitor to the order.');
            }

            // Update order stat us
            $order->status = Orders::STATUS_ASSIGNED_SERVICE_STAFF;
            if (!$order->save(false)) {
                throw new \Exception('Failed to update order status.');
            }

            // Update staff's status to busy
            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
            if (!$staff->save()) {
                throw new \Exception('Failed to update home visitor status.');
            }

            // Save order status update in history
            $orderStatus = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status = $order->status;
            $orderStatus->remarks = "Order status updated to " . $order->getStateOptionsBadges();

            if (!$orderStatus->save(false)) {
                throw new \Exception('Failed to save order status in history.');
            }

            // Notify the user
            $isHomeVisit = $order->service_type == Orders::TRANS_TYPE_HOME_VISIT;
            $titleUser = Yii::t("app", "Your Order Assigned to Staff");
            $bodyUser = $isHomeVisit
                ? Yii::t("app", "Your order (#{$order_id}) has been assigned to a home visitor. Service will begin shortly.")
                : Yii::t("app", "Your order (#{$order_id}) has been assigned to a staff member. Please visit our location for service.");

            Yii::$app->notification->PushNotification(
                $order_id,
                $order->user_id,
                $titleUser,
                $bodyUser,
                $isHomeVisit ? "home_visit" : "walk_in"
            );

            // Notify home visitor if it's a home visit
            if ($isHomeVisit) {
                $titleVisitor = Yii::t("app", "New Order Assignment");
                $bodyVisitor = Yii::t("app", "You have been assigned a new home visit order (#{$order_id}). Please proceed with the service.");

                Yii::$app->notification->PushNotification(
                    $order_id,
                    $staff->user_id,
                    $titleVisitor,
                    $bodyVisitor,
                    "home_visit"
                );
            }

            // Commit the transaction
            $transaction->commit();

            return ['status' => 'success', 'message' => 'Home visitor assigned successfully.'];
        } catch (\Exception $e) {
            // Rollback the transaction in case of errors
            $transaction->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }








    public function actionReassignHomeVisitor()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $home_visitor_id = $post['Orders']['home_visitor_id'] ?? null;
        $order_id = $post['Orders']['id'] ?? null;

        if (empty($home_visitor_id) || empty($order_id)) {
            return ['status' => 'error', 'message' => 'Home visitor ID and order ID are required.'];
        }

        // Find the order by order_id
        $order = Orders::findOne($order_id);
        if (!$order) {
            return ['status' => 'error', 'message' => 'Invalid order ID. Order not found.'];
        }

        // Find the home visitor by home_visitor_id
        $staff = Staff::findOne(['id' => $home_visitor_id]);
        if (!$staff) {
            return ['status' => 'error', 'message' => 'Invalid home visitor ID. Home visitor not found.'];
        }

        // Check if the home visitor is available (idle or no current status)
        if ($staff->current_status != Staff::CURRENT_STATUS_IDLE && !empty($staff->current_status)) {
            return ['status' => 'error', 'message' => 'Home visitor is currently busy and cannot be reassigned to this order.'];
        }

        // Check if there is already a home visitor assigned to this order
        $home_visitors_has_orders = HomeVisitorsHasOrders::findOne(['order_id' => $order_id]);

        if (!$home_visitors_has_orders) {
            return ['status' => 'error', 'message' => 'No home visitor is currently assigned to this order.'];
        }

        // Start database transaction to ensure consistency
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Store the previous visitor's ID to reset their status
            $previous_visitor = $home_visitors_has_orders->home_visitor_id;

            // Reassign the new home visitor
            $home_visitors_has_orders->home_visitor_id = $home_visitor_id;
            if (!$home_visitors_has_orders->save(false)) {
                throw new \Exception('Failed to reassign home visitor.');
            }

            // Update the previous home visitor's status to idle
            $previousStaff = Staff::findOne(['id' => $previous_visitor]);
            if ($previousStaff) {
                $previousStaff->current_status = Staff::CURRENT_STATUS_IDLE;
                if (!$previousStaff->save(false)) {
                    throw new \Exception('Failed to update previous home visitor status.');
                }
            }

            // Update order status to reflect reassignment
            $order->status = Orders::STATUS_ASSIGNED_SERVICE_STAFF;
            if (!$order->save(false)) {
                throw new \Exception('Failed to update order status.');
            }

            // Update the newly assigned home visitor's status to busy
            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
            if (!$staff->save(false)) {
                throw new \Exception('Failed to update home visitor status.');
            }

            // Save order status update in history
            $orderStatus = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status = $order->status;
            $orderStatus->remarks = "Home visitor reassigned to " . $staff->full_name . ". Order status updated.";

            if (!$orderStatus->save(false)) {
                throw new \Exception('Failed to save order status in history.');
            }

            // Commit the transaction if everything is successful
            $transaction->commit();

            return ['status' => 'success', 'message' => 'Home visitor reassigned successfully.'];
        } catch (\Exception $e) {
            // Rollback transaction in case of errors
            $transaction->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    //Download Pdf  
  public function actionDownloadPdf($id)
{
    $model = Orders::findOne($id);

    if (!$model) {
        throw new \yii\web\NotFoundHttpException('Order not found.');
    }

    $providerOrderDetails = new \yii\data\ActiveDataProvider([
        'query' => OrderDetails::find()->where(['order_id' => $id])
    ]);

    $comboOrders = ComboOrder::find()->where(['order_id' => $id])->all();
    $homeVisitorAssign = HomeVisitorsHasOrders::find()->where(['order_id' => $id])->one();

    $content = $this->renderPartial('_pdf_invoice', [
        'model' => $model,
        'providerOrderDetails' => $providerOrderDetails,
        'comboOrders' => $comboOrders,
        'homeVisitorAssign' => $homeVisitorAssign,
    ]);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($content);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return Yii::$app->response->sendContentAsFile(
        $dompdf->output(),
        'Order_Invoice_' . $model->id . '.pdf',
        ['mimeType' => 'application/pdf']
    );
    }




    /**
 * Approve a new order and update its status
 */
public function actionApproveOrder()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isPost) {
        return [
            'status' => 'error',
            'message' => 'Invalid request method'
        ];
    }
    
    $orderId = Yii::$app->request->post('id');
    $nextStatus = Yii::$app->request->post('next_status', Orders::STATUS_ACCEPTED);
    
    if (empty($orderId)) {
        return [
            'status' => 'error',
            'message' => 'Order ID is required'
        ];
    }
    
    $model = Orders::findOne($orderId);
    if (!$model) {
        return [
            'status' => 'error',
            'message' => 'Order not found'
        ];
    }

    // Check if order is in WAITING_FOR_APPROVAL status
    if ($model->status != Orders::STATUS_WAITING_FOR_APPROVAL) {
        return [
            'status' => 'error',
            'message' => 'Order can only be approved when it is in Waiting for Approval status'
        ];
    }
    
    // Check vendor allow_order_approval setting
    $vendorDetails = VendorDetails::findOne($model->vendor_details_id);
    if (!$vendorDetails) {
        return [
            'status' => 'error',
            'message' => 'Vendor details not found'
        ];
    }
    
    $transaction = Yii::$app->db->beginTransaction();
    
    try {
        // Update order status
        $model->status = Orders::STATUS_NEW_ORDER ;
        $model->updated_on = date('Y-m-d H:i:s');
        
        if (!$model->save(false)) {
            throw new \Exception('Failed to update order status');
        }
        
        // Log the order status change
        $orderStatus = new OrderStatus();
        $orderStatus->order_id = $model->id;
        $orderStatus->status = $nextStatus;
        $orderStatus->remarks = "Order approved and status updated to " . $model->getStateOptionsBadges();
        $orderStatus->created_on = date('Y-m-d H:i:s');
        $orderStatus->create_user_id = Yii::$app->user->id;
        
        if (!$orderStatus->save(false)) {
            throw new \Exception('Failed to log order status change');
        }
        
 
        
        $transaction->commit();
        
        // Send notifications
        try {
            // Notify customer
            $customerTitle = "Order Approved";
            $customerBody = "Your order #{$model->id} has been approved and is being processed.";
            Yii::$app->notification->PushNotification(
                $model->id, 
                $model->user_id, 
                $customerTitle, 
                $customerBody, 
                'redirect'
            );
            
            // Notify vendor
            $vendorTitle = "Order Approved";
            $vendorBody = "Order #{$model->id} has been approved. Please proceed with service preparation.";
            Yii::$app->notification->PushNotification(
                $model->id, 
                $model->vendor_details_id , 
                $vendorTitle, 
                $vendorBody, 
                'redirect'
            );
        } catch (\Exception $e) {
            Yii::warning("Failed to send notifications for order approval: " . $e->getMessage());
        }
        
        return [
            'status' => 'success',
            'message' => 'Order approved successfully',
            'order_id' => $model->id,
            'new_status' => $model->status
        ];
        
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Error approving order {$orderId}: " . $e->getMessage(), __METHOD__);
        
        return [
            'status' => 'error',
            'message' => 'Failed to approve order: ' . $e->getMessage()
        ];
    }
}


}
