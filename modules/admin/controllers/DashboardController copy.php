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
    $user = Yii::$app->user->identity;

    $toDayDate = date('Y-m-d 00:00:00');
    $toDayDateTileTime = date('Y-m-d H:i:s');
    $last7Days = date('Y-m-d H:i:s', strtotime('-7 days'));
    $last30Days = date('Y-m-d H:i:s', strtotime('-30 days'));

    // Vendor specific filter
    $isVendor = User::isVendor();
    $vendorId = $user->id;

    // Base queries
    $ordersQuery = Orders::find();
    $earningsQuery = VendorEarnings::find();

    if (!$isVendor) {
        $ordersQuery->andWhere(['vendor_details_id ' => $vendorId]);
        $earningsQuery->andWhere(['vendor_details_id ' => $vendorId]);
    }

    // Earnings
    $baseEarningsQuery = clone $earningsQuery;

    $data['admin_commission_day'] = (clone $baseEarningsQuery)
        ->where(['status' => VendorEarnings::STATUS_APPROVED])
        ->andWhere(['between', 'created_on', $toDayDate, $toDayDateTileTime])
        ->sum('admin_commission_amount');

    $data['admin_commission_week'] = (clone $baseEarningsQuery)
        ->where(['status' => VendorEarnings::STATUS_APPROVED])
        ->andWhere(['between', 'created_on', $last7Days, $toDayDateTileTime])
        ->sum('admin_commission_amount');

    $data['admin_commission_month'] = (clone $baseEarningsQuery)
        ->where(['status' => VendorEarnings::STATUS_APPROVED])
        ->andWhere(['between', 'created_on', $last30Days, $toDayDateTileTime])
        ->sum('admin_commission_amount');

    // Orders
    $baseOrdersQuery = clone $ordersQuery;

    $data['new_orders'] = (clone $baseOrdersQuery)
        ->andWhere(['status' => Orders::STATUS_NEW_ORDER])
        ->count();

    $data['accepted_orders'] = (clone $baseOrdersQuery)
        ->andWhere(['status' => Orders::STATUS_ACCEPTED])
        ->count();

    $data['ongoing_orders'] = (clone $baseOrdersQuery)
        ->andWhere(['status' => Orders::STATUS_SERVICE_STARTED])
        ->count();

    $data['completed_orders'] = (clone $baseOrdersQuery)
        ->andWhere(['status' => Orders::STATUS_SERVICE_COMPLETED])
        ->count();

    // Admin / Manager stats
    if (!$isVendor) {
        $data['active_vendors'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->count();
        $data['pending_onboarding'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])->count();
        $data['total_vendors'] = VendorDetails::find()->where(['status' => [VendorDetails::STATUS_ACTIVE, VendorDetails::STATUS_VERIFICATION_PENDING]])->count();

        $data['total_users'] = User::find()
            ->where(['status' => User::STATUS_ACTIVE])
            ->andWhere(['user_role' => [User::ROLE_USER, User::ROLE_HOME_VISITOR, User::ROLE_STAFF, User::ROLE_VENDOR]])
            ->count();

        $data['only_users'] = User::find()
            ->where(['status' => User::STATUS_ACTIVE, 'user_role' => User::ROLE_USER])
            ->count();

        $data['total_staff_home'] = Staff::find()
            ->where(['status' => Staff::STATUS_ACTIVE])
            ->andWhere(['role' => [Staff::ROLE_HOME_VISITOR, Staff::ROLE_STAFF]])
            ->count();

        $data['total_homevisitors'] = Staff::find()
            ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_HOME_VISITOR])
            ->count();

        $data['total_staff'] = Staff::find()
            ->where(['status' => Staff::STATUS_ACTIVE, 'role' => Staff::ROLE_STAFF])
            ->count();

        $data['total_shops'] = VendorDetails::find()
            ->where(['status' => VendorDetails::STATUS_ACTIVE])
            ->count();

        $data['total_spa'] = VendorDetails::find()
            ->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 1])
            ->count();

        $data['total_saloon'] = VendorDetails::find()
            ->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 2])
            ->count();

        $data['total_skin'] = VendorDetails::find()
            ->where(['status' => VendorDetails::STATUS_ACTIVE, 'main_category_id' => 3])
            ->count();

        $data['total_subscriptions'] = Subscriptions::find()
            ->where(['status' => Subscriptions::STATUS_ACTIVE])
            ->count();

        $data['basic_subscriptions'] = Subscriptions::find()
            ->where([
                'subscription_type' => Subscriptions::SUBSCRIPTION_TYPE_IS_BASIC,
                'status' => Subscriptions::STATUS_ACTIVE
            ])
            ->count();

        $data['premium_subscriptions'] = Subscriptions::find()
            ->where([
                'subscription_type' => Subscriptions::SUBSCRIPTION_TYPE_IS_PREMIUM,
                'status' => Subscriptions::STATUS_ACTIVE
            ])
            ->count();
    }

    return $this->render('index', ['data' => $data]);
}


}
