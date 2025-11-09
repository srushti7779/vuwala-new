<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\CouponsApplied;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\VendorDetails;
use Yii;
use app\models\User;
use app\modules\admin\models\base\CouponHasDays;
use app\modules\admin\models\base\CouponHasTimeSlots;
use app\modules\admin\models\base\Days;
use app\modules\admin\models\base\ServiceHasCoupons;
use app\modules\admin\models\base\StoreTimings;
use app\modules\admin\models\Coupon;
use app\modules\admin\models\search\CouponSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CouponController implements the CRUD actions for Coupon model.
 */
class CouponController extends Controller
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
                        'index', 'view','create-vendor', 'create',
                        'update', 'delete', 'update-status',
                        'add-coupon-vendor', 'add-coupons-applied'
                    ],
                    'matchCallback' => function () {
                        return User::isAdmin() || User::isVendor() || User::isSubAdmin();
                    }
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'update','create-vendor', 'pdf', 'update-status'],
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
     * Lists all Coupon models.
     * @return mixed
     */
   public function actionIndex()
{
    $searchModel = new CouponSearch();
    // var_dump( $searchModel);

    if (in_array(Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_VENDOR])) {
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    } elseif (Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
        $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams); // Fixed from managersearch
    }

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    

    /**
     * Displays a single Coupon model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        //  print_r($model);
        $providerCouponVendor = new \yii\data\ArrayDataProvider([
            'allModels' => $model->couponVendors,
        ]);
        $providerCouponsApplied = new \yii\data\ArrayDataProvider([
            'allModels' => $model->couponsApplieds,
        ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'providerCouponVendor' => $providerCouponVendor,
            'providerCouponsApplied' => $providerCouponsApplied,
        ]);
    }

    /**
     * Creates a new Coupon model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
public function actionCreate()
{
    $model = new Coupon();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        $vendorIds = Yii::$app->request->post('vendor_details_id', []);
        // print_r($vendorIds);

        foreach ($vendorIds as $vendorId) {
            $cv = new CouponVendor();
            $cv->coupon_id = $model->id;
            $cv->vendor_details_id = $vendorId;
            $cv->create_user_id = Yii::$app->user->id;
            $cv->update_user_id = Yii::$app->user->id;
            $cv->created_on = date('Y-m-d H:i:s');
            $cv->updated_on = date('Y-m-d H:i:s');
            $cv->save();
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('create', [
        'model' => $model,
       
    ]);
}



 public function actionCreateVendor()
    {
        if (!(User::isVendor() || User::isAdmin() || User::isSubAdmin())) {
            throw new \yii\web\ForbiddenHttpException("You are not allowed to access this page.");
        }

        $model = new Coupon();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->is_global = '0';
            $model->status = 1;

            if ($model->save()) {
                // Save Vendor Details
                $vendorDetails = VendorDetails::findOne(['user_id' => Yii::$app->user->id]);
                if ($vendorDetails) {
                    $cv = new CouponVendor();
                    $cv->coupon_id = $model->id;
                    $cv->vendor_details_id = $vendorDetails->id;
                    $cv->create_user_id = Yii::$app->user->id;
                    $cv->update_user_id = Yii::$app->user->id;
                    $cv->created_on = date('Y-m-d H:i:s');
                    $cv->updated_on = date('Y-m-d H:i:s');
                    if (!$cv->save(false)) {
                        \Yii::error('Failed to save CouponVendor: ' . print_r($cv->errors, true));
                    }
                } else {
                    \Yii::warning("VendorDetails not found for user_id: " . Yii::$app->user->id);
                }

                // Handle Happy Hour time slots
                if ($model->coupon_type == Coupon::COUPON_TYPE_HAPPY_HOUR) {
                    $timeSlots = Yii::$app->request->post('timeSlots', []);
                    \Yii::info('Time Slots: ' . print_r($timeSlots, true));

                    if (!empty($timeSlots)) {
                        foreach ($timeSlots as $slot) {
                            if (!empty($slot['day']) && !empty($slot['start_time']) && !empty($slot['end_time'])) {

                                // Ensure end_time > start_time
                                if (strtotime($slot['end_time']) <= strtotime($slot['start_time'])) {
                                    \Yii::warning("Invalid time range skipped: " . print_r($slot, true));
                                    continue;
                                }

                                // Check if coupon_has_days entry exists
                                $day = CouponHasDays::findOne([
                                    'coupon_id' => $model->id,
                                    'day'       => $slot['day']
                                ]);

                                if (!$day) {
                                    $day = new CouponHasDays();
                                    $day->coupon_id = $model->id;
                                    $day->day = $slot['day'];
                                    $day->status = 1;
                                    $day->created_on = date('Y-m-d H:i:s');
                                    $day->updated_on = date('Y-m-d H:i:s');
                                    $day->create_user_id = Yii::$app->user->id;
                                    $day->update_user_id = Yii::$app->user->id;

                                    if (!$day->save(false)) {
                                        \Yii::error('Failed to save coupon day: ' . print_r($day->errors, true));
                                        continue;
                                    }
                                }

                                // Prevent duplicate slots
                                $exists = CouponHasTimeSlots::findOne([
                                    'coupon_id'        => $model->id,
                                    'coupon_has_day_id' => $day->id,
                                    'start_time'       => date('h:i A', strtotime($slot['start_time'])),
                                    'end_time'         => date('h:i A', strtotime($slot['end_time']))
                                ]);
                                if ($exists) {
                                    continue;
                                }

                                // Save time slot
                                $ts = new CouponHasTimeSlots();
                                $ts->coupon_id = $model->id;
                                $ts->coupon_has_day_id = $day->id;
                                $ts->start_time = date('h:i A', strtotime($slot['start_time']));
                                $ts->end_time   = date('h:i A', strtotime($slot['end_time']));
                                $ts->status = 1;
                                $ts->created_on = date('Y-m-d H:i:s');
                                $ts->updated_on = date('Y-m-d H:i:s');
                                $ts->create_user_id = Yii::$app->user->id;
                                $ts->update_user_id = Yii::$app->user->id;

                                if (!$ts->save(false)) {
                                    \Yii::error('Failed to save time slot: ' . print_r($ts->errors, true));
                                }
                            }
                        }
                    }
                }

                // Handle Specific Services
                if ($model->offer_type == Coupon::OFFER_TYPE_SPECIFIC_SERVICES) {
                    $serviceIds = $model->service_ids ?: [];
                    \Yii::info('Service IDs: ' . print_r($serviceIds, true));

                    if (!empty($serviceIds)) {
                        ServiceHasCoupons::deleteAll(['coupon_id' => $model->id]);
                        foreach ($serviceIds as $serviceId) {
                            $sc = new ServiceHasCoupons();
                            $sc->service_id = $serviceId;
                            $sc->coupon_id = $model->id;
                            $sc->status = 1;
                            $sc->created_on = date('Y-m-d H:i:s');
                            $sc->updated_on = date('Y-m-d H:i:s');
                            $sc->create_user_id = Yii::$app->user->id;
                            $sc->update_user_id = Yii::$app->user->id;
                            if (!$sc->save(false)) {
                                \Yii::error('Failed to save service coupon: ' . print_r($sc->errors, true));
                            }
                        }
                    }
                }

                Yii::$app->session->setFlash('success', 'Happy Coupons created successfully.');
                return $this->redirect(['index']);
            } else {
                \Yii::error('Failed to save coupon: ' . print_r($model->errors, true));
            }
        } else {
            \Yii::error('Validation errors: ' . print_r($model->errors, true));
        }

        $allDaySlots = [];
        $dayOptions = [];

        $vendorDetails = VendorDetails::findOne(['user_id' => Yii::$app->user->id, 'status' => VendorDetails::STATUS_ACTIVE]);
         $day         = Days:: findone('day');

        if ($vendorDetails) {
            $days = Days::findOne(['title' => $day]);
            $storeTimings = StoreTimings::find()
                ->where(['vendor_details_id' => $vendorDetails->id, 'status' => StoreTimings::STATUS_ACTIVE])
                ->all();

            foreach ($storeTimings as $timing) {
                if (isset($days[$timing->day_id])) {
                    $day = $days[$timing->day_id];
                    $slots = VendorDetails::getServiceScheduleSlots(
                        30,
                        0,
                        $timing->start_time,
                        $timing->close_time
                    );
                    $allDaySlots[$day->title] = $slots;
                    $dayOptions[] = $day->title;
                }
            }
        }

        return $this->render('create_vendor', [
            'model'      => $model,
            'dayOptions' => $dayOptions,
            'daySlots'   => $allDaySlots,
        ]);
    }






    /**
     * Updates an existing Coupon model.
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
     * Deletes an existing Coupon model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = Coupon::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = Coupon::find()->where([
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
     * Finds the Coupon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Coupon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Coupon::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
    * Action to load a tabular form grid
    * for CouponVendor
    * @author Yohanes Candrajaya <moo.tensai@gmail.com>
    * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
    *
    * @return mixed
    */
    public function actionAddCouponVendor()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('CouponVendor');
            if (!empty($row)) {
                $row = array_values($row);
            }
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formCouponVendor', ['row' => $row]);
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
            if((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add')
                $row[] = [];
            return $this->renderAjax('_formCouponsApplied', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
