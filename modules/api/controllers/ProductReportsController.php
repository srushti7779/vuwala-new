<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\models\User;
use app\modules\admin\models\ProductOrders;
use app\modules\api\controllers\BKController;
use Exception;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\UnauthorizedHttpException;

class ProductReportsController extends BKController
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'sales-summary-over-view',
                 
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'sales-summary-over-view',
                         
                        ],
                        'allow'   => true,
                        'roles'   => ['?', '*'],
                    ],
                ],
            ],
        ]);
    }


    public function actionSalesSummaryOverView()
    {
          $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }
            $product_revenue = ProductOrders::find()->where(['vendor_details_id'=>$vendor_details_id])->andWhere(['status'=>ProductOrders::STATUS_SUCCESS])->sum('total_with_tax');


            $product_dash_board['product_revenue'] = $product_revenue;
            $product_dash_board['product_sold'] = 0;
            $product_dash_board['total_products'] = 0;
            $product_dash_board['low_stock_items'] = 0;
            $product_dash_board['avg_product_price'] = 0;
            $product_dash_board['product_category'] = ['name' => '', 'revenue' => 0];


        
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order held successfully.");
        } catch (Exception $e) {

                $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error'] = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error'] = Yii::t("app", "An unexpected error occurred while deleting the service from order.");
                $data['error_code'] = 500;
            }

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



}
