<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\models\User;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\base\OrderTransactionDetails;
use app\modules\admin\models\Orders;
use Exception;


use yii\web\Response;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;


class GeneralController extends BKController
{

    public $enableCsrfValidation = false;
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [



            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [

                    'class' => AccessRule::className()
                ],

                'rules' => [
                    [
                        'actions' => [
                            'logout',
                            'qr-code',
                            'generate-referral-codes',
                            'update-service-prices',
                            'update-order-types'




                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'logout',
                            'qr-code',
                            'generate-referral-codes',
                            'update-service-prices',
                            'update-order-types'






                        ],

                        'allow' => true,
                        'roles' => [

                            '?',
                            '*',

                        ]
                    ]
                ]
            ]

        ]);
    }



    public function actionLogout()
    {
        $data = [];
        $headers = Yii::$app->request->headers->get('auth_code') ?: Yii::$app->request->getQueryParam('auth_code');
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (!empty($user_id)) {
            try {
                // Find the AuthSession model associated with the user
                $model = AuthSession::find()->where(['create_user_id' => $user_id])->one();

                if (!empty($model)) {
                    // Delete the session record
                    if ($model->delete()) {
                        // Perform Yii2 user logout
                        Yii::$app->user->logout(false);  // Avoid destroying session with 'false'

                        $data['status'] = self::API_OK;
                        $data['message'] = 'Successfully Logged Out';
                    } else {
                        throw new \Exception('Error deleting session record.');
                    }
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error'] = 'User session not found.';
                }
            } catch (\Exception $e) {
                Yii::error("Logout failed: " . $e->getMessage(), __METHOD__);
                $data['status'] = self::API_NOK;
                $data['error'] = 'An unexpected error occurred while logging out: ' . $e->getMessage();
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error'] = 'User authentication failed. Invalid or missing auth_code.';
        }

        return $this->sendJsonResponse($data);
    }



    public function actionQrCode($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;



        $url = Yii::$app->urlManager->createAbsoluteUrl(['/order/view', 'id' => $id]);

        $qrResult = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(150)
            ->margin(10)
            ->build();

        return [
            'order_id' => $id,
            'qr_code_base64' => 'data:image/png;base64,' . base64_encode($qrResult->getString()),
        ];
    }



    public function actionGenerateReferralCodes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $users = User::find()->all();
            $updated = 0;

            foreach ($users as $user) {
                if (empty($user->referral_code)) {
                    $user->referral_code = User::generateUniqueReferralCode();
                    if ($user->save(false)) {
                        $updated++;
                    }
                }
            }

            return [
                'status' => self::API_OK,
                'message' => "Referral codes generated for $updated users.",
            ];
        } catch (\Exception $e) {
            Yii::error("Referral code generation failed: " . $e->getMessage(), __METHOD__);
            return [
                'status' => self::API_NOK,
                'error' => 'An error occurred while generating referral codes.',
            ];
        }
    }


    public function actionUpdateServicePrices()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $services = \app\modules\admin\models\Services::find()->all();
            $updated = 0;

            foreach ($services as $service) {
                if (empty($service->price)) {
                    $service->price = $service->from_price;
                    if ($service->save(false)) {
                        $updated++;
                    }
                }
            }

            return [
                'status' => self::API_OK,
                'message' => "Prices updated for $updated services.",
            ];
        } catch (\Exception $e) {
            Yii::error("Service price update failed: " . $e->getMessage(), __METHOD__);
            return [
                'status' => self::API_NOK,
                'error' => 'An error occurred while updating service prices.',
            ];
        }
    }


public function actionUpdateOrderTypes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $orders = OrderTransactionDetails::find()->all();
            $updated = 0;

            foreach ($orders as $order) {
                if (empty($order->order_type)) {
                    $order->order_type = OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER;
                    if ($order->save(false)) {
                        $updated++;
                    }
                }
            }

            return [
                'status' => self::API_OK,
                'message' => "Order types updated for $updated orders.",
            ];
        } catch (\Exception $e) {
            Yii::error("Order type update failed: " . $e->getMessage(), __METHOD__);
            return [
                'status' => self::API_NOK,
                'error' => 'An error occurred while updating order types.',
            ];
        }

}

}
