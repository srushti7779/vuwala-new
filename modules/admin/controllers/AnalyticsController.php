<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Wallet;
use app\models\Product;
use app\models\User;
use app\modules\admin\models\base\City;
use app\modules\admin\models\base\Orders;
use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\Subscriptions;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use \yii\helpers\ArrayHelper;

class AnalyticsController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'check-sort', 'vendors-by-category'],
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
                        'allow' => false
                    ]
                ]
            ]
        ];
    }
 
    // Action to render the Analytics page
    public function actionIndex() 
    {

        $toDayDate = date('Y-m-d 00:00:00');
        $last7Days = date('Y-m-d H:i:s', strtotime('today -7 days')); // Last 7 days
        $last30Days = date('Y-m-d H:i:s', strtotime('today -30 days'));
        $toDayDateTileTime = date('Y-m-d H:i:s');

        // Earnings Calculations
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


        // Orders stats 
        $data['new_orders'] = Orders::find()->where(['status' => Orders::STATUS_NEW_ORDER])->count();
        $data['accepted_orders'] = Orders::find()->where(['status' => Orders::STATUS_ACCEPTED])->count();
        $data['ongoing_orders'] = Orders::find()->where(['status' => Orders::STATUS_SERVICE_STARTED])->count();
        $data['completed_orders'] = Orders::find()->where(['status' => Orders::STATUS_SERVICE_COMPLETED])->count();

        // Vendor onboarding status
        $data['active_vendors'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->andwhere(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])->count(); 

        $data['active_vendors'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->count(); 
        $data['pending_onboarding'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])->count();
        $data['total_vendors'] = VendorDetails::find()
        ->where(['status' => VendorDetails::STATUS_ACTIVE])
        ->orWhere(['status' => VendorDetails::STATUS_VERIFICATION_PENDING])
        ->count();
    

        // Geographical data (example: based on city) 
        // $data['geo_data'] = City::find()->all();  


        $data['total_users'] = User::find()->where(['status' => User::STATUS_ACTIVE])->andWhere(['user_role' => User::ROLE_USER])->count();
   
        $data['total_shops'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->count();

        $data['total_spa'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->andWhere(['main_category_id' => 1])->count();
        $data['total_saloon'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->andWhere(['main_category_id' => 2])->count();
        $data['total_skin'] = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->andWhere(['main_category_id' => 3])->count(); 
        $data['total_subscriptions'] = Subscriptions::find()->where(['status' => Subscriptions::STATUS_ACTIVE])->count();

        return $this->render('index', [ 
            'data' => $data 
        ]);
    }
}
