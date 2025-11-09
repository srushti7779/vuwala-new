<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\admin\models\base\Wallet;
use app\modules\admin\models\GuestUserDeposits;
use app\modules\admin\models\MemberShips;
use app\modules\admin\models\ShopReview;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoresUsersMemberships;
use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use app\modules\api\controllers\BKController;
use Exception;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class ReviewsController extends BKController
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
                            'shop-reviews',
                         
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'shop-reviews',
                      
                        ],
                        'allow'   => true,
                        'roles'   => ['?', '*'],
                    ],
                ],
            ],
        ]);
    }




public function actionShopReviews()
{
    $data = [];
    $post = Yii::$app->request->post();
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    // Check if user is authenticated
    if (!$user_id) {
        $data['status']  = self::API_NOK;
        $data['message'] = Yii::t("app", "User authentication failed. Please log in.");
        return $this->sendJsonResponse($data);
    }

    try {
        // Fetch vendor details based on authenticated user
        $vendorDetails = VendorDetails::find()
            ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
            ->one();

        if (!$vendorDetails) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Vendor details not found for the logged-in user.");
            return $this->sendJsonResponse($data);
        }

        $vendor_id = $vendorDetails->id;

        // Pagination inputs (from POST). You can also accept GET if you prefer.
        $page = ($post['page'] ?? 1);
        $perPage = ($post['per_page'] ?? 10);

        // sanitize pagination values
        $page = $page < 1 ? 1 : $page;
        $perPage = $perPage < 1 ? 12 : $perPage;
        $maxPerPage = 100;
        $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

        // base query
        $query = ShopReview::find()->where(['vendor_details_id' => $vendor_id]);

        // total count
        $total = (int)$query->count();

        // calculate paging
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 0;
        $offset = ($page - 1) * $perPage;

        // fetch page of reviews (sorted newest first)
        $reviews = $query->orderBy(['id' => SORT_DESC])->offset($offset)->limit($perPage)->all();

        $list = [];
        if (!empty($reviews)) {
            foreach ($reviews as $review) {
                // keep using asJson() if each model has that helper; otherwise ->toArray()
                $list[] =$review->asJson();
            }
        }

        // Prepare response with pagination meta
        $data['status'] = self::API_OK;
        $data['reviews'] = $list;
        $data['pagination'] = [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ];

        // If no reviews, keep status OK but empty list (or switch to NOK if you prefer)
        if (empty($list)) {
            $data['message'] = Yii::t('app', 'No reviews found for this vendor.');
        }
    } catch (\Throwable $e) {
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);

        $data['status'] = self::API_NOK;
        $data['message'] = Yii::t('app', 'An error occurred while fetching reviews.');
    }

    return $this->sendJsonResponse($data);
}



   
}
