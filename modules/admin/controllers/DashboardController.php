<?php

namespace app\modules\admin\controllers;

use app\models\User;

use app\modules\admin\models\Reels;
use app\modules\admin\models\Wallet;

use app\modules\admin\models\base\Orders;
use app\modules\admin\models\base\Staff;
use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\Subscriptions;
use app\modules\admin\models\City;
use yii;
use yii\filters\VerbFilter;


class DashboardController extends Controller
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
                'denyCallback' => function ($rule, $action) {
                    // Redirect to home page if not allowed
                    return Yii::$app->response->redirect(['/site/index'])->send();
                },
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isVendor();
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
                        'allow' => false // fallback for others
                    ]
                ]
            ]
        ];
    }
    




    public function actionError() {}



  public function actionIndex()
{
    $toDayDate = date('Y-m-d 00:00:00');
    $toDayDateTileTime = date('Y-m-d H:i:s');
    $last7Days = date('Y-m-d H:i:s', strtotime('today -7 days'));
    $last30Days = date('Y-m-d H:i:s', strtotime('today -30 days'));

    $vendorId = null;
    $hasVendorOrders = false;
    $data = [];

    if (User::isVendor()) {
        $vendorDetails = VendorDetails::find()->where(['user_id' => Yii::$app->user->id])->one();

        if ($vendorDetails) {
            $vendorId = $vendorDetails->id;
            Yii::error("Vendor ID: $vendorId", 'debug');

            $vendorOrders = Orders::find()
                ->select(['id', 'status'])
                ->where(['vendor_details_id' => $vendorId])
                ->asArray()
                ->all();
            Yii::error("Vendor Orders: " . json_encode($vendorOrders), 'debug');

            $hasVendorOrders = Orders::find()
                ->where(['vendor_details_id' => $vendorId])
                ->exists();
        }
    }



    if ($vendorId) {
        if ($hasVendorOrders) {
            $data['admin_commission_day'] = VendorEarnings::find()
                ->where(['status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['between', 'created_on', $toDayDate, $toDayDateTileTime])
                ->andWhere(['vendor_details_id' => $vendorId])
                ->sum('admin_commission_amount');

            $data['admin_commission_week'] = VendorEarnings::find()
                ->where(['status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['between', 'created_on', $last7Days, $toDayDateTileTime])
                ->andWhere(['vendor_details_id' => $vendorId])
                ->sum('admin_commission_amount');

            $data['admin_commission_month'] = VendorEarnings::find()
                ->where(['status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['between', 'created_on', $last30Days, $toDayDateTileTime])
                ->andWhere(['vendor_details_id' => $vendorId])
                ->sum('admin_commission_amount');

            $data['vendor_earning_day'] = VendorEarnings::find()
                ->where(['status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['between', 'created_on', $toDayDate, $toDayDateTileTime])
                ->andWhere(['vendor_details_id' => $vendorId])
                ->sum('vendor_received_amount') ?? 0;

            $data['new_orders'] = Orders::find()
                ->where(['status' => Orders::STATUS_NEW_ORDER, 'vendor_details_id' => $vendorId])
                ->count();

            $data['accepted_orders'] = Orders::find()
                ->where(['status' => Orders::STATUS_ACCEPTED, 'vendor_details_id' => $vendorId])
                ->count();

            $data['ongoing_orders'] = Orders::find()
                ->where(['status' => Orders::STATUS_SERVICE_STARTED, 'vendor_details_id' => $vendorId])
                ->count();
            Yii::error("Ongoing Orders Count: " . $data['ongoing_orders'], 'debug');

            $data['completed_orders'] = Orders::find()
                ->where(['status' => Orders::STATUS_SERVICE_COMPLETED, 'vendor_details_id' => $vendorId])
                ->count();

            // ✅ Updated to include all cancellation statuses
            $data['cancelled_orders'] = Orders::find()
                ->where(['vendor_details_id' => $vendorId])
                ->andWhere(['in', 'status', [
                    Orders::STATUS_CANCELLED_BY_OWNER,
                    Orders::STATUS_CANCELLED_BY_USER,
                    Orders::STATUS_CANCELLED_BY_ADMIN,
                    Orders::STATUS_CANCELLED_BY_HOME_VISITORS,
                    Orders::STATUS_CANCELLED
                ]])
                ->count();

            // Debugging (optional)
            Yii::error("Cancelled Orders Count: " . $data['cancelled_orders'], 'debug');
        } else {
            $data = array_fill_keys([
                'admin_commission_day', 'admin_commission_week', 'admin_commission_month',
                'vendor_earning_day', 'new_orders', 'accepted_orders',
                'ongoing_orders', 'completed_orders', 'cancelled_orders'
            ], 0);
        }
    }

    if (!User::isVendor()) {
        $data['admin_commission_day'] = VendorEarnings::find()
            ->where(['status' => VendorEarnings::STATUS_APPROVED])
            ->andWhere(['between', 'created_on', $toDayDate, $toDayDateTileTime])
            ->sum('admin_commission_amount');

        $data['admin_commission_week'] = VendorEarnings::find()
            ->where(['status' => VendorEarnings::STATUS_APPROVED])
            ->andWhere(['between', 'created_on', $last7Days, $toDayDateTileTime])
            ->sum('admin_commission_amount');

        $data['admin_commission_month'] = VendorEarnings::find()
            ->where(['status' => VendorEarnings::STATUS_APPROVED])
            ->andWhere(['between', 'created_on', $last30Days, $toDayDateTileTime])
            ->sum('admin_commission_amount');

        $data['new_orders'] = Orders::find()->where(['status' => Orders::STATUS_NEW_ORDER])->count();
        $data['accepted_orders'] = Orders::find()->where(['status' => Orders::STATUS_ACCEPTED])->count();
        $data['ongoing_orders'] = Orders::find()->where(['status' => Orders::STATUS_SERVICE_STARTED])->count();
        $data['completed_orders'] = Orders::find()->where(['status' => Orders::STATUS_SERVICE_COMPLETED])->count();

        // Admin view also updated to include all cancellation statuses
        $data['cancelled_orders'] = Orders::find()
            ->where(['in', 'status', [
                Orders::STATUS_CANCELLED_BY_OWNER,
                Orders::STATUS_CANCELLED_BY_USER,
                Orders::STATUS_CANCELLED_BY_ADMIN,
                Orders::STATUS_CANCELLED_BY_HOME_VISITORS,
                Orders::STATUS_CANCELLED
            ]])
            ->count();
    }
    // Common data for all users
    $data['active_vendors'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->count();
    $data['pending_onboarding'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])->count();
    $data['total_vendors'] = VendorDetails::find()
        ->where(['status' => VendorDetails::STATUS_ACTIVE])
        ->orWhere(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])
        ->count();

    $data['total_users'] = User::find()
        ->where(['status' => User::STATUS_ACTIVE])
        ->andWhere(['user_role' => [User::ROLE_USER, User::ROLE_HOME_VISITOR, User::ROLE_STAFF, User::ROLE_VENDOR]])
        ->count();

    $data['only_users'] = User::find()
        ->where(['status' => User::STATUS_ACTIVE, 'user_role' => User::ROLE_USER])
        ->count();

    // ✅ Updated Section for Staff and Home Visitors
    if (User::isVendor()) {
        if ($vendorId) {
            $data['total_homevisitors'] = Staff::find()
                ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_HOME_VISITOR, 'vendor_details_id' => $vendorId])
                ->count();

            $data['total_staff'] = Staff::find()
                ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_STAFF, 'vendor_details_id' => $vendorId])
                ->count();
        } else {
            $data['total_homevisitors'] = 0;
            $data['total_staff'] = 0;
        }
    } else {
        $data['total_homevisitors'] = Staff::find()
            ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_HOME_VISITOR])
            ->count();

        $data['total_staff'] = Staff::find()
            ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_STAFF])
            ->count();
    }

    $data['total_staff_home'] = $data['total_homevisitors'] + $data['total_staff'];

    $data['total_shops'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->count();
    $data['total_spa'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 1])->count();
    $data['total_saloon'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 2])->count();
    $data['total_skin'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 3])->count();

    $data['total_subscriptions'] = Subscriptions::find()->where(['status' => Subscriptions::STATUS_ACTIVE])->count();
    $data['basic_subscriptions'] = Subscriptions::find()->where(['subscription_type' => Subscriptions::SUBSCRIPTION_TYPE_IS_BASIC, 'status' => Subscriptions::STATUS_ACTIVE])->count();
    $data['premium_subscriptions'] = Subscriptions::find()->where(['subscription_type' => Subscriptions::SUBSCRIPTION_TYPE_IS_PREMIUM, 'status' => Subscriptions::STATUS_ACTIVE])->count();

    if (User::isAdmin()) {
        return $this->render('index', ['data' => $data]);
    } elseif (User::isVendor()) {
        return $this->render('index_vendor', ['data' => $data]);
    }
}


    }

