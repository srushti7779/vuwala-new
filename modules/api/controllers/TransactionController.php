<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\models\User;
use app\modules\admin\models\base\Sku;
use app\modules\admin\models\base\VendorExpensesTypes;
use app\modules\admin\models\base\VendorPayout;
use app\modules\admin\models\ProductOrderItems;
use app\modules\admin\models\ProductOrders;
use app\modules\admin\models\Products;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\VendorExpenses;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class TransactionController extends BKController
{




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
                            'check',
                            'vendor-earnings',
                            'payouts',
                            'payouts-dashboard',
                            'add-or-update-vendor-expense-type',
                            'get-vendor-expense-types',
                            'add-or-update-vendor-expenses',
                            'expenses-list',
                            'expenses-dashboard',
                            'earnings-dashboard',
                            'payout-dashboard',
                            'profit-dashboard'






                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'check',
                            'vendor-earnings',
                            'payouts',
                            'payouts-dashboard',
                            'add-or-update-vendor-expense-type',
                            'get-vendor-expense-types',
                            'add-or-update-vendor-expenses',
                            'expenses-list',
                            'expenses-dashboard',
                            'earnings-dashboard',
                            'payout-dashboard',
                            'profit-dashboard'













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


    public function actionIndex()
    {

        $data['details'] =  ['hi'];
        return $this->sendJsonResponse($data);
    }




    public function actionVendorEarnings()
    {
        $data = [
            'status' => self::API_OK,
            'message' => '',
            'details' => [],
            'pagination' => [],
        ];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }

            // Get vendor ID for logged-in user
            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }

            // Pagination parameters
            $request = Yii::$app->request;
            $page = (int)$request->post('page', 1);
            $perPage = (int)$request->post('per_page', 10);
            if ($perPage < 1) $perPage = 10;
            if ($page < 1) $page = 1;

            // Query for paginated data
            $query = VendorEarnings::find()->where(['vendor_details_id' => $vendorId]);
            $countQuery = clone $query;

            $pagination = new \yii\data\Pagination([
                'totalCount' => $countQuery->count(),
                'pageSize' => $perPage,
                'page' => $page - 1, // zero-based for Pagination
            ]);

            $vendor_earnings = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            $list = [];
            if (!empty($vendor_earnings)) {
                foreach ($vendor_earnings as $earning) {
                    $list[] = $earning->asJsonList();
                }
                $data['message'] = Yii::t("app", "Earnings fetched successfully.");
            } else {
                $data['message'] = Yii::t("app", "No earnings found for this vendor.");
            }
            $data['details'] = $list;
            $data['pagination'] = [
                'total_count' => $pagination->totalCount,
                'page_count' => $pagination->getPageCount(),
                'current_page' => $pagination->getPage() + 1, // back to 1-based
                'per_page' => $pagination->getPageSize(),
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = $e->getMessage();
            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred.");
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionPayouts()
    {
        $data = [
            'status' => self::API_OK,
            'message' => '',
            'details' => [],
            'pagination' => [],
        ];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }

            // Get vendor ID for logged-in user
            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }

            // Pagination parameters
            $request = Yii::$app->request;
            $page = (int)$request->post('page', 1);
            $perPage = (int)$request->post('per_page', 10);
            if ($perPage < 1) $perPage = 10;
            if ($page < 1) $page = 1;

            $query = VendorPayout::find()->where(['vendor_details_id' => $vendorId]);
            $countQuery = clone $query;

            $pagination = new \yii\data\Pagination([
                'totalCount' => $countQuery->count(),
                'pageSize' => $perPage,
                'page' => $page - 1, // zero-based for Pagination
            ]);

            $payouts = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            $list = [];
            if (!empty($payouts)) {
                foreach ($payouts as $payout) {
                    $list[] = $payout->asJson();
                }
                $data['message'] = Yii::t("app", "Payouts fetched successfully.");
            } else {
                $data['message'] = Yii::t("app", "No payouts found for this vendor.");
            }
            $data['details'] = $list;
            $data['pagination'] = [
                'total_count' => $pagination->totalCount,
                'page_count' => $pagination->getPageCount(),
                'current_page' => $pagination->getPage() + 1, // 1-based
                'per_page' => $pagination->getPageSize(),
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = $e->getMessage();
            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred.");
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
 

public function actionPayoutDashboard()
{
    $data = [
        'status' => self::API_OK,
        'message' => '',
        'details' => [
            'pending' => [],
            'paid' => [],
            'summary' => [
                'total_pending_amount' => 0.0,
                'total_paid_amount' => 0.0,
                'pending_count' => 0,
                'paid_count' => 0,
            ],
        ],
        'pagination' => [],
    ];

    try {
        // Ensure user is authenticated
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
        }

        // Get vendor ID for logged-in user
        $vendorId = User::getVendorIdByUserId($user_id);
        if (empty($vendorId)) {
            throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
        }

        // Calculate current week (Monday to Sunday)
        $currentDate = new \DateTime('2025-09-12', new \DateTimeZone('Asia/Kolkata')); // Current date in IST
        $weekStart = (clone $currentDate)->modify('monday this week')->setTime(0, 0, 0);
        $weekEnd = (clone $currentDate)->modify('sunday this week')->setTime(23, 59, 59);
        $startDate = $weekStart->format('Y-m-d H:i:s');
        $endDate = $weekEnd->format('Y-m-d H:i:s');

        // Fetch pending payouts (status = STATUS_PROCESSING)
        $pendingPayouts = VendorPayout::find()
            ->where(['vendor_details_id' => $vendorId, 'status' => VendorPayout::STATUS_PROCESSING])
            ->andWhere(['between', 'created_on', $startDate, $endDate])
            ->orderBy(['created_on' => SORT_DESC])
            ->asArray()
            ->all();

        // Fetch paid payouts (status = STATUS_APPROVED)
        $paidPayouts = VendorPayout::find()
            ->where(['vendor_details_id' => $vendorId, 'status' => VendorPayout::STATUS_APPROVED])
            ->andWhere(['between', 'created_on', $startDate, $endDate])
            ->orderBy(['created_on' => SORT_DESC])
            ->asArray()
            ->all();

        // Format pending payouts
        $totalPendingAmount = 0.0;
        foreach ($pendingPayouts as $payout) {
            $amount = (float)$payout['amount'];
            $totalPendingAmount += $amount;
            $data['details']['pending'][] = [
                'payout_id' => $payout['id'],
                'amount' => $amount,
                'created_on' => $payout['created_on'],
                'status' => 'pending',
                'payment_type' => $payout['payment_type'],
            ];
        }

        // Format paid payouts
        $totalPaidAmount = 0.0;
        foreach ($paidPayouts as $payout) {
            $amount = (float)$payout['amount'];
            $totalPaidAmount += $amount;
            $data['details']['paid'][] = [
                'payout_id' => $payout['id'],
                'amount' => $amount,
                'created_on' => $payout['created_on'],
                'status' => 'paid',
                'payment_type' => $payout['payment_type'],
            ];
        }

        // Update summary
        $data['details']['summary'] = [
            'total_pending_amount' => round($totalPendingAmount, 2),
            'total_paid_amount' => round($totalPaidAmount, 2),
            'pending_count' => count($data['details']['pending']),
            'paid_count' => count($data['details']['paid']),
        ];

        $data['message'] = Yii::t("app", "Payout dashboard data retrieved successfully.");
    } catch (UnauthorizedHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
        $data['message'] = Yii::t("app", "Authentication failed.");
        Yii::warning([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);
    } catch (BadRequestHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
        $data['message'] = $e->getMessage();
        Yii::warning([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);
    } catch (\Throwable $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = Yii::t("app", "An unexpected error occurred.");
        $data['message'] = Yii::t("app", "An unexpected error occurred.");
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);
    }

    return $this->sendJsonResponse($data);
}













    public function actionAddOrUpdateVendorExpenseType()
    {
        $data = [];
        $transaction = null;

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            // 2. Only POST allowed
            if (!Yii::$app->request->isPost) {
                throw new \yii\web\MethodNotAllowedHttpException(Yii::t('app', 'Only POST requests are allowed.'));
            }

            // 3. Vendor info
            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'Vendor details not found for this user.'));
            }

            $main_vendor_user_id = User::getVendorParentUser($vendor_details_id);

            // 4. Get POST params
            $post = Yii::$app->request->post();
            $id   = !empty($post['id']) ? (int)$post['id'] : null;
            $type = !empty($post['type']) ? trim($post['type']) : null;

            if (empty($type)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'type is required.'));
            }

            // 5. Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            // 6. If id provided -> update; else create
            $isNew = true;
            if (!empty($id)) {
                $vendor_expenses_types = VendorExpensesTypes::findOne(['id' => $id, 'vendor_details_id' => $vendor_details_id]);
                if (!$vendor_expenses_types) {
                    throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor Expense Type not found for the given ID.'));
                }
                $isNew = false;
            } else {
                $vendor_expenses_types = new VendorExpensesTypes();
                $vendor_expenses_types->vendor_details_id = $vendor_details_id;
                $vendor_expenses_types->main_vendor_user_id = $main_vendor_user_id;
                $vendor_expenses_types->status = VendorExpensesTypes::STATUS_ACTIVE;
            }

            // 7. Duplicate check (exclude current record if updating)
            $duplicateExists = VendorExpensesTypes::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'type' => $type])
                ->andFilterWhere(['!=', 'id', $id])
                ->exists();

            if ($duplicateExists) {
                throw new \yii\web\ConflictHttpException(Yii::t('app', 'Vendor Expense Type with this type already exists.'));
            }

            // 8. Assign & save (use validation)
            $vendor_expenses_types->type = $type;

            if ($vendor_expenses_types->save() === false) {
                // collect validation errors and return a short message
                $errors = $vendor_expenses_types->getFirstErrors();
                $firstError = reset($errors) ?: Yii::t('app', 'Failed to save Vendor Expense Type.');
                throw new \yii\web\ServerErrorHttpException($firstError);
            }

            $transaction->commit();

            $data['status']  = self::API_OK;
            $data['message'] = $isNew
                ? Yii::t('app', 'Vendor Expense Type added successfully.')
                : Yii::t('app', 'Vendor Expense Type updated successfully.');
            $data['record']  = method_exists($vendor_expenses_types, 'asJson') ? $vendor_expenses_types->asJson() : $vendor_expenses_types->toArray();
        } catch (\yii\web\HttpException $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);

            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['error_code'] = $e->statusCode ?? 400;
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);

            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t('app', 'An unexpected error occurred.');
            $data['error_code'] = 500;
        }

        return $this->sendJsonResponse($data);
    }




    public function actionGetVendorExpenseTypes()
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
                throw new UnauthorizedHttpException(Yii::t("app", "VendorDetails Not found For this user.."));
            }

            // 2. Validate request method
            if (!Yii::$app->request->isGet) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only GET requests are allowed."));
            }

            // 3. Check if ID is passed
            $id = Yii::$app->request->get('id');  // from query param
            if (!empty($id)) {
                $record = VendorExpensesTypes::find()
                    ->where(['id' => (int)$id, 'vendor_details_id' => $vendor_details_id])
                    ->asArray()
                    ->one();

                if (!$record) {
                    throw new BadRequestHttpException(Yii::t("app", "Vendor ExpenseType not found for given ID."));
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Vendor ExpenseType retrieved successfully.");
                $data['record']  = $record;
            } else {
                // Fetch all expense types for this vendor
                $records = VendorExpensesTypes::find()
                    ->where(['vendor_details_id' => $vendor_details_id])
                    ->orderBy(['created_on' => SORT_DESC])
                    ->asArray()
                    ->all();

                if (empty($records)) {
                    $data['status']  = self::API_OK;
                    $data['message'] = Yii::t("app", "No Vendor ExpenseTypes found.");
                    $data['records'] = [];
                } else {
                    $data['status']  = self::API_OK;
                    $data['message'] = Yii::t("app", "Vendor ExpenseTypes retrieved successfully.");
                    $data['records'] = $records;
                }
            }
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
    public function actionAddOrUpdateVendorExpenses()
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
                throw new UnauthorizedHttpException(Yii::t("app", "VendorDetails not found for this user."));
            }

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post              = Yii::$app->request->post();
            $id                = isset($post['id']) ? (int)$post['id'] : null;
            $expense_type_id   = isset($post['expense_type_id']) ? (int)$post['expense_type_id'] : null;
            $payment_mode      = isset($post['payment_mode']) ? (int)$post['payment_mode'] : null;
            $expense_date      = isset($post['expense_date']) ? trim($post['expense_date']) : null;
            $amount            = isset($post['amount']) ? (float)$post['amount'] : null;
            $notes             = isset($post['notes']) ? trim($post['notes']) : null;
            $image_url         = isset($post['image_url']) ? trim($post['image_url']) : null;




            // 4. Validate required fields
            $missingFields = [];
            foreach (['expense_type_id', 'payment_mode', 'expense_date', 'amount'] as $field) {
                if (empty($$field) && $$field !== 0) $missingFields[] = $field; // allow 0 values
            }
            if (!empty($missingFields)) {
                throw new BadRequestHttpException(Yii::t("app", "Missing required fields: " . implode(", ", $missingFields)));
            }

            // 5. Additional validations
            if (!is_numeric($expense_type_id) || $expense_type_id <= 0) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid expense_type_id."));
            }
            if (!is_numeric($payment_mode) || $payment_mode <= 0) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid payment_mode."));
            }
            if (!is_numeric($amount) || $amount < 0) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid amount."));
            }
            if (!\DateTime::createFromFormat('Y-m-d', $expense_date)) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid expense_date format. Expected YYYY-MM-DD."));
            }

            // 6. Validate expense type existence
            $expenseType = VendorExpensesTypes::findOne(['id' => $expense_type_id, 'vendor_details_id' => $vendor_details_id]);
            if (!$expenseType) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid expense_type_id or not found for this vendor."));
            }

            // 7. Check if record exists (update or create)
            $isNew = true;
            if (!empty($id)) {
                $expense = VendorExpenses::findOne(['id' => $id, 'vendor_details_id' => $vendor_details_id]);
                if (!$expense) {
                    throw new BadRequestHttpException(Yii::t("app", "Vendor Expense not found for the given ID."));
                }
                $expense->update_user_id = $user_id;
                $expense->updated_on     = date('Y-m-d H:i:s');
                $isNew = false;
            } else {
                $expense = new VendorExpenses();
                $expense->create_user_id = $user_id;
                $expense->created_on     = date('Y-m-d H:i:s');
            }



            // 9. Assign attributes
            $expense->vendor_details_id = $vendor_details_id;
            $expense->expense_type_id   = $expense_type_id;
            $expense->payment_mode      = $payment_mode;
            $expense->expense_date      = $expense_date;
            $expense->amount            = $amount;
            $expense->notes             = $notes;
            $expense->image_url         = $image_url;
            $expense->status            = VendorExpenses::STATUS_ACTIVE;

            // 10. Save record
            if (!$expense->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save vendor expense."));
            }

            // 11. Response
            $data['status']  = self::API_OK;
            $data['message'] = $isNew
                ? Yii::t("app", "Vendor expense added successfully.")
                : Yii::t("app", "Vendor expense updated successfully.");
            $data['record'] = $expense->attributes;
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionExpensesList()
    {
        $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'VendorDetails not found for this user.'));
            }

            // Pagination inputs (from POST). Keep defaults and sane caps.
            $post    = Yii::$app->request->post();
            $page    = isset($post['page']) ? (int)$post['page'] : 1;
            $perPage = isset($post['per_page']) ? (int)$post['per_page'] : 10;
            $search  = isset($post['search']) ? trim($post['search']) : '';
            $start_date = !empty($post['start_date']) ? trim($post['start_date']) : '';
            $end_date = !empty($post['end_date']) ? trim($post['end_date']) : '';

            $page = $page < 1 ? 1 : $page;
            $perPage = $perPage < 1 ? 10 : $perPage;
            $maxPerPage = 100;
            $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

            // Build base query
            $query = VendorExpenses::find()->where(['vendor_details_id' => $vendor_details_id]);

            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'notes', $search],
                    ['like', 'amount', $search]
                ]);
            }
            if (!empty($start_date) && \DateTime::createFromFormat('Y-m-d', $start_date)) {
                $query->andWhere(['>=', 'expense_date', $start_date]);
            }

            if (!empty($end_date) && \DateTime::createFromFormat('Y-m-d', $end_date)) {
                $query->andWhere(['<=', 'expense_date', $end_date]);
            }


            // total count
            $total = (int) (clone $query)->count();

            // calculate offset and total pages
            $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
            $offset = ($page - 1) * $perPage;

            // fetch paginated records (order by id desc by default)
            $records = $query->orderBy(['id' => SORT_DESC])->offset($offset)->limit($perPage)->all();

            $types_map = [];
            if (!empty($records)) {
                foreach ($records as $vendor_expenses) {
                    $types_map[] = method_exists($vendor_expenses, 'asJson')
                        ? $vendor_expenses->asJson()
                        : $vendor_expenses->toArray();
                }
            }

            $data['status'] = self::API_OK;
            $data['message'] = !empty($types_map)
                ? Yii::t('app', 'Vendor expense types retrieved successfully.')
                : Yii::t('app', 'No expense types found for this vendor.');
            $data['expenses'] = $types_map;
            $data['pagination'] = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred.');
            $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionExpensesDashboard()
    {
        $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'VendorDetails not found for this user.'));
            }

            // 2. Calculate totals
            $totalExpenses = (clone VendorExpenses::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->sum('amount');
            $totalExpenses = $totalExpenses ? (float)$totalExpenses : 0.0;

            $today_expenses = (clone VendorExpenses::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['DATE(expense_date)' => date('Y-m-d')])
                ->sum('amount');
            $weekly_expenses = (clone VendorExpenses::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['>=', 'expense_date', date('Y-m-d', strtotime('-7 days'))])
                ->sum('amount');
            $monthly_expenses = (clone VendorExpenses::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['>=', 'expense_date', date('Y-m-d', strtotime('-30 days'))])
                ->sum('amount');



            $data['status'] = self::API_OK;
            $data['message'] = Yii::t('app', 'Vendor expenses dashboard retrieved successfully.');
            $data['dashboard'] = [
                'total_expenses' => $totalExpenses,
                'today_expenses' => $today_expenses ?: 0.0,
                'weekly_expenses' => $weekly_expenses ?: 0.0,
                'monthly_expenses' => $monthly_expenses ?: 0.0,
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred.');
            $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
 


    public function actionEarningsDashboard()
    {
        $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'VendorDetails not found for this user.'));
            }

            // 2. Calculate totals
            $totalEarnings = (clone VendorEarnings::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['status' => VendorEarnings::STATUS_APPROVED])
                ->sum('vendor_received_amount');
            $totalEarnings = $totalEarnings ? (float)$totalEarnings : 0.0;

            $today_earnings = (clone VendorEarnings::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['DATE(created_on)' => date('Y-m-d')])
                ->andWhere(['status' => VendorEarnings::STATUS_APPROVED])
                ->sum('vendor_received_amount');
            $weekly_earnings = (clone VendorEarnings::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['>=', 'created_on', date('Y-m-d', strtotime('-7 days'))])
                ->andWhere(['status' => VendorEarnings::STATUS_APPROVED])
                ->sum('vendor_received_amount');
            $monthly_earnings = (clone VendorEarnings::find())
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['>=', 'created_on', date('Y-m-d', strtotime('-30 days'))])
                ->andWhere(['status' => VendorEarnings::STATUS_APPROVED])
                ->sum('vendor_received_amount');

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t('app', 'Vendor earnings dashboard retrieved successfully.');
            $data['dashboard'] = [
                'total_earnings' => $totalEarnings,
                'today_earnings' => $today_earnings ?: 0.0,
                'weekly_earnings' => $weekly_earnings ?: 0.0,
                'monthly_earnings' => $monthly_earnings ?: 0.0,
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred.');
            $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
 

    public function actionProfitDashboard(){
        $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            $vendor_details_id = User::getVendorIdByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'VendorDetails not found for this user.'));
            }

            // Date calculations
            $today = date('Y-m-d');
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $monthStart = date('Y-m-01');
            
            // Get all SKUs for this vendor
            $skus = Sku::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => Sku::STATUS_ACTIVE])
                ->all();

            // Initialize totals
            $totalProfitToday = 0;
            $totalProfitWeek = 0;
            $totalProfitMonth = 0;
            $totalRevenueToday = 0;
            $totalRevenueWeek = 0;
            $totalRevenueMonth = 0;
            
            $skuProfitDetails = [];

            // Calculate profit for each SKU
            foreach ($skus as $sku) {
                $profitStats = $sku->getProfitStatistics();
                
                $totalProfitToday += $profitStats['today']['total_profit'];
                $totalProfitWeek += $profitStats['this_week']['total_profit'];
                $totalProfitMonth += $profitStats['this_month']['total_profit'];
                
                $totalRevenueToday += $profitStats['today']['total_revenue'];
                $totalRevenueWeek += $profitStats['this_week']['total_revenue'];
                $totalRevenueMonth += $profitStats['this_month']['total_revenue'];
                
                // Store individual SKU data for detailed view
                $skuProfitDetails[] = [
                    'sku_id' => $sku->id,
                    'sku_name' => $sku->product_name,
                    'sku_code' => $sku->sku_code,
                    'today_profit' => $profitStats['today']['total_profit'],
                    'week_profit' => $profitStats['this_week']['total_profit'],
                    'month_profit' => $profitStats['this_month']['total_profit'],
                    'today_profit_formatted' => Sku::formatAmountInK($profitStats['today']['total_profit']),
                    'week_profit_formatted' => Sku::formatAmountInK($profitStats['this_week']['total_profit']),
                    'month_profit_formatted' => Sku::formatAmountInK($profitStats['this_month']['total_profit']),
                    'profit_margin' => $profitStats['this_month']['profit_margin']
                ];
            }

            // Calculate profit margins
            $profitMarginToday = $totalRevenueToday > 0 ? round(($totalProfitToday / $totalRevenueToday) * 100, 2) : 0;
            $profitMarginWeek = $totalRevenueWeek > 0 ? round(($totalProfitWeek / $totalRevenueWeek) * 100, 2) : 0;
            $profitMarginMonth = $totalRevenueMonth > 0 ? round(($totalProfitMonth / $totalRevenueMonth) * 100, 2) : 0;

            // Generate chart data for the last 30 days
            $chartStartDate = date('Y-m-d', strtotime('-29 days'));
            $chartEndDate = $today;
            $dailyChartData = Sku::getDailyProfitChart($vendor_details_id, $chartStartDate, $chartEndDate);

            // Generate weekly chart data for the last 12 weeks
            $weeklyChartData = $this->getWeeklyProfitChart($vendor_details_id);

            // Generate monthly chart data for the last 12 months
            $monthlyChartData = $this->getMonthlyProfitChart($vendor_details_id);

            // Get top performing SKUs by profit
            $topSkus = array_slice(
                $this->arrayOrderBy($skuProfitDetails, 'month_profit', SORT_DESC),
                0, 5
            );

            // Prepare response data
            $data['status'] = self::API_OK;
            $data['message'] = 'Profit dashboard data retrieved successfully';
            
            // Summary statistics
            $data['profit_summary'] = [
                'today' => [
                    'profit' => round($totalProfitToday, 2),
                    'profit_formatted' => Sku::formatAmountInK($totalProfitToday),
                    'revenue' => round($totalRevenueToday, 2),
                    'revenue_formatted' => Sku::formatAmountInK($totalRevenueToday),
                    'profit_margin' => $profitMarginToday
                ],
                'this_week' => [
                    'profit' => round($totalProfitWeek, 2),
                    'profit_formatted' => Sku::formatAmountInK($totalProfitWeek),
                    'revenue' => round($totalRevenueWeek, 2),
                    'revenue_formatted' => Sku::formatAmountInK($totalRevenueWeek),
                    'profit_margin' => $profitMarginWeek
                ],
                'this_month' => [
                    'profit' => round($totalProfitMonth, 2),
                    'profit_formatted' => Sku::formatAmountInK($totalProfitMonth),
                    'revenue' => round($totalRevenueMonth, 2),
                    'revenue_formatted' => Sku::formatAmountInK($totalRevenueMonth),
                    'profit_margin' => $profitMarginMonth
                ]
            ];

            // Chart data for visualization
            $data['charts'] = [
                'daily' => [
                    'title' => 'Daily Profit Trend (Last 30 Days)',
                    'type' => 'line',
                    'data' => $dailyChartData
                ],
                'weekly' => [
                    'title' => 'Weekly Profit Trend (Last 12 Weeks)',
                    'type' => 'bar',
                    'data' => $weeklyChartData
                ],
                'monthly' => [
                    'title' => 'Monthly Profit Trend (Last 12 Months)',
                    'type' => 'bar',
                    'data' => $monthlyChartData
                ]
            ];

            // Detailed SKU data
            $data['sku_details'] = $skuProfitDetails;
            $data['top_performing_skus'] = $topSkus;

            // Additional insights
            $data['insights'] = [
                'total_active_skus' => count($skus),
                'profitable_skus_today' => count(array_filter($skuProfitDetails, function($sku) {
                    return $sku['today_profit'] > 0;
                })),
                'profitable_skus_month' => count(array_filter($skuProfitDetails, function($sku) {
                    return $sku['month_profit'] > 0;
                })),
                'best_profit_margin_sku' => !empty($topSkus) ? $topSkus[0] : null
            ];

        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred.');
            $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    /**
     * Get weekly profit chart data
     * @param int $vendorDetailsId
     * @return array
     */
    private function getWeeklyProfitChart($vendorDetailsId)
    {
        try {
            $weeks = [];
            $profits = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $weekStart = date('Y-m-d', strtotime("-{$i} weeks monday"));
                $weekEnd = date('Y-m-d', strtotime("-{$i} weeks sunday"));
                
                $weekProfit = ProductOrderItems::find()
                    ->select('SUM((product_order_items.selling_price - products.purchased_price) * product_order_items.quantity) as profit')
                    ->joinWith(['product', 'productOrder'])
                    ->where(['products.vendor_details_id' => $vendorDetailsId])
                    ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
                    ->andWhere(['product_orders.status' => ProductOrders::STATUS_COMPLETED])
                    ->andWhere(['>=', 'DATE(product_orders.created_on)', $weekStart])
                    ->andWhere(['<=', 'DATE(product_orders.created_on)', $weekEnd])
                    ->scalar();
                
                $weeks[] = 'Week ' . date('M d', strtotime($weekStart));
                $profits[] = round($weekProfit ?: 0, 2);
            }

            return [
                'labels' => $weeks,
                'profits' => $profits,
                'datasets' => [[
                    'label' => 'Weekly Profit',
                    'data' => $profits,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]]
            ];

        } catch (\Exception $e) {
            return [
                'labels' => [],
                'profits' => [],
                'datasets' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get monthly profit chart data
     * @param int $vendorDetailsId
     * @return array
     */
    private function getMonthlyProfitChart($vendorDetailsId)
    {
        try {
            $months = [];
            $profits = [];
            
            for ($i = 11; $i >= 0; $i--) {
                $monthStart = date('Y-m-01', strtotime("-{$i} months"));
                $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
                
                $monthProfit = ProductOrderItems::find()
                    ->select('SUM((product_order_items.selling_price - products.purchased_price) * product_order_items.quantity) as profit')
                    ->joinWith(['product', 'productOrder'])
                    ->where(['products.vendor_details_id' => $vendorDetailsId])
                    ->andWhere(['product_order_items.status' => ProductOrderItems::STATUS_ACTIVE])
                    ->andWhere(['product_orders.status' => ProductOrders::STATUS_COMPLETED])
                    ->andWhere(['>=', 'DATE(product_orders.created_on)', $monthStart])
                    ->andWhere(['<=', 'DATE(product_orders.created_on)', $monthEnd])
                    ->scalar();
                
                $months[] = date('M Y', strtotime($monthStart));
                $profits[] = round($monthProfit ?: 0, 2);
            }

            return [
                'labels' => $months,
                'profits' => $profits,
                'datasets' => [[
                    'label' => 'Monthly Profit',
                    'data' => $profits,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]]
            ];

        } catch (\Exception $e) {
            return [
                'labels' => [],
                'profits' => [],
                'datasets' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Helper function to sort array by key
     * @param array $array Array to sort
     * @param string $key Key to sort by
     * @param int $direction SORT_ASC or SORT_DESC
     * @return array Sorted array
     */
    private function arrayOrderBy($array, $key, $direction = SORT_ASC)
    {
        if (empty($array)) {
            return $array;
        }

        $keys = array_column($array, $key);
        array_multisort($keys, $direction, $array);
        return $array;
    }

}
