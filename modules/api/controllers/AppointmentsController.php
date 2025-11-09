<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\components\OrderAssignmentService;
use app\models\User;
use app\modules\admin\models\base\Services;
use app\modules\admin\models\base\StoreTimings;
use app\modules\admin\models\BypassNumbers;
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\Days;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\MemberShips;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\ProductServices;
use app\modules\admin\models\ProductServicesUsed;
use app\modules\admin\models\RescheduleOrderLogs;
use app\modules\admin\models\Staff;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\Wallet;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class AppointmentsController extends BKController
{



    public $mainMenu = 'Orders';



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
                            'dashboard',
                            'my-shop-orders',
                            'staff-list',
                            'walk-in-immediate-order',
                            'pre-booking-order',
                            'get-vendor-active-services',
                            'assign-order-to-staff',
                            'verify-otp-order-create',
                            'change-order-status',
                            'available-slots',
                            'list-combo-packages',
                            'view-order-by-id',
                            'cancel-appointment-request',
                            'update-order-service-prices',
                            're-schedule',
                            'get-products-used-in-order',
                            'update-products-used-order',
                            'verify-user',
                            'get-memberships'







                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'dashboard',
                            'my-shop-orders',
                            'staff-list',
                            'walk-in-immediate-order',
                            'pre-booking-order',
                            'get-vendor-active-services',
                            'assign-order-to-staff',
                            'verify-otp-order-create',
                            'change-order-status',
                            'available-slots',
                            'list-combo-packages',
                            'view-order-by-id',
                            'cancel-appointment-request',
                            'update-order-service-prices',
                            're-schedule',
                            'get-products-used-in-order',
                            'update-products-used-order',
                            'verify-user',
                            'get-memberships'















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



    public function actionDashboard()
    {
        $data = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            // Get date filters from request
            $start_date = Yii::$app->request->post('start_date', date('Y-m-d'));
            $end_date   = Yii::$app->request->post('end_date', $start_date);

            // Normalize dates
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date   = date('Y-m-d', strtotime($end_date));

            // Base query with date filter
            $query = Orders::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['payment_status' => Orders::PAYMENT_DONE])
                ->andWhere(['between', 'schedule_date', $start_date, $end_date]);

            // Dashboard Metrics
            $dashboard = [];

            // New Orders (today’s or within range)
            $dashboard['new_orders'] = (clone $query)
                ->andWhere(['schedule_date' => date('Y-m-d')])
                ->count();

            // Completed Orders
            $dashboard['completed_orders'] = (clone $query)
                ->andWhere(['status' => Orders::STATUS_SERVICE_COMPLETED])
                ->count();

            // Ongoing Orders
            $dashboard['ongoing_orders'] = (clone $query)
                ->andWhere(['in', 'status', [
                    Orders::STATUS_SERVICE_STARTED,
                    Orders::STATUS_START_TO_LOCATION_HOME_VISIT,
                    Orders::STATUS_ARRIVED_CUSTOMER_LOCATION,
                    Orders::STATUS_ACCEPTED,
                    Orders::STATUS_ASSIGNED_SERVICE_STAFF,
                ]])
                ->count();

            // Upcoming Orders (future scheduled date in given range)
            $dashboard['upcoming_orders'] = (clone $query)
                ->andWhere(['>', 'schedule_date', date('Y-m-d')])
                ->count();

            // Revenue (sum of paid orders in date range)

            $revenue = VendorEarnings::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['between', 'created_on', $start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                ->andWhere(['status' => VendorEarnings::STATUS_APPROVED])
                ->sum('vendor_received_amount');
            $dashboard['revenue'] = $revenue ? number_format((float) $revenue, 2, '.', '') : '0.00';

            // Response
            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Dashboard data fetched successfully.");
            $data['details'] = $dashboard;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
        }

        return $this->sendJsonResponse($data);
    }




 public function actionMyShopOrders()
{
    $data    = [];
    $post    = Yii::$app->request->post();
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth    = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    // find shop early (keeps your original behavior)
    $shop = VendorDetails::findOne(['user_id' => $user_id]);
    if (! $shop) {
        throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
    }

    $page         = ! empty($post['page']) ? (int) $post['page'] : 0;
    $service_type = $post['service_type'] ?? '';
    $search       = $post['search'] ?? '';
    $status       = $post['status'] ?? Orders::STATUS_NEW_ORDER;

    // allow user to pass start_date and/or end_date; if none provided, default to today (preserves original behaviour)
    $start_date = array_key_exists('start_date', $post) && $post['start_date'] !== '' ? $post['start_date'] : null;
    $end_date   = array_key_exists('end_date', $post) && $post['end_date'] !== '' ? $post['end_date'] : null;

    try {
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
        }

        // normalize and validate date inputs
        $normalizeDate = function ($d) {
            if ($d === null || $d === '') {
                return null;
            }
            $ts = strtotime($d);
            if ($ts === false) {
                return false; // invalid
            }
            return date('Y-m-d', $ts);
        };

        $sd = $normalizeDate($start_date);
        $ed = $normalizeDate($end_date);

        if ($sd === false || $ed === false) {
            throw new BadRequestHttpException(Yii::t('app', 'Invalid date format. Use YYYY-MM-DD or a parseable date.'));
        }

        // If neither provided, default to today (keeps prior behavior)
        if ($sd === null && $ed === null) {
            $today = date('Y-m-d');
            $sd = $today;
            $ed = $today;
        }

        // If both are provided and start > end, swap them
        if ($sd !== null && $ed !== null) {
            if (strtotime($sd) > strtotime($ed)) {
                $tmp = $sd;
                $sd  = $ed;
                $ed  = $tmp;
            }
        }

        $query = Orders::find()
            ->where(['vendor_details_id' => $shop->id])
            ->andWhere(['payment_status' => Orders::PAYMENT_DONE]);

        // Date filtering logic:
        // - If both sd and ed present => between inclusive
        // - If only sd present (ed was null) => schedule_date = sd
        // - If only ed present (sd was null) => schedule_date <= ed
        if ($sd !== null && $ed !== null) {
            // if both equal -> between is fine (or you could use '=')
            $query->andWhere(['between', 'schedule_date', $sd, $ed]);
        } elseif ($sd !== null) {
            $query->andWhere(['schedule_date' => $sd]);
        } elseif ($ed !== null) {
            $query->andWhere(['<=', 'schedule_date', $ed]);
        }

        if (! empty($service_type)) {
            $query->andWhere(['service_type' => $service_type]);
        }

        if (! empty($status)) {
            if (in_array($status, [
                Orders::STATUS_CANCELLED,
                Orders::STATUS_CANCELLED_BY_ADMIN,
                Orders::STATUS_CANCELLED_BY_OWNER,
                Orders::STATUS_CANCELLED_BY_USER,
            ])) {
                $query->andWhere(['in', 'status', [
                    Orders::STATUS_CANCELLED_BY_ADMIN,
                    Orders::STATUS_CANCELLED_BY_OWNER,
                    Orders::STATUS_CANCELLED_BY_USER,
                ]]);
            } elseif (in_array($status, [
                Orders::STATUS_SERVICE_STARTED,
                Orders::STATUS_START_TO_LOCATION_HOME_VISIT,
                Orders::STATUS_ARRIVED_CUSTOMER_LOCATION,
            ])) {
                $query->andWhere(['in', 'status', [
                    Orders::STATUS_SERVICE_STARTED,
                    Orders::STATUS_START_TO_LOCATION_HOME_VISIT,
                    Orders::STATUS_ARRIVED_CUSTOMER_LOCATION,
                ]]);
            } else if (in_array($status, [
                Orders::STATUS_ACCEPTED,
                Orders::STATUS_ASSIGNED_SERVICE_STAFF,
            ])) {
                $query->andWhere(['in', 'status', [
                    Orders::STATUS_ACCEPTED,
                    Orders::STATUS_ASSIGNED_SERVICE_STAFF,
                ]]);
            } else {
                $query->andWhere(['status' => $status]);
            }
        }

        if (! empty($search)) {
            // you currently search by id only; keep as-is
            $query->andWhere(['id' => $search]);
        }

        $query->orderBy(['id' => SORT_DESC]);

        $pagination = new \yii\data\Pagination([
            'totalCount' => $query->count(),
            'pageSize'   => 10,
            'page'       => $page,
        ]);

        $orders = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $list   = array_map(function ($order) {
            return $order->asJsonVendor();
        }, $orders);

        if (! empty($list)) {
            $data['status']     = self::API_OK;
            $data['details']    = $list;
            $data['pagination'] = [
                'current_page' => $pagination->page + 1,
                'page_size'    => $pagination->pageSize,
                'total_pages'  => ceil($pagination->totalCount / $pagination->pageSize),
                'total_items'  => $pagination->totalCount,
            ];
            $data['message'] = Yii::t("app", "Orders retrieved successfully.");
        } else {
            throw new NotFoundHttpException(Yii::t("app", "No orders found for the given criteria."));
        }
    } catch (UnauthorizedHttpException $e) {
        $data['status']  = self::API_NOK;
        $data['message'] = $e->getMessage();
    } catch (NotFoundHttpException $e) {
        $data['status']  = self::API_NOK;
        $data['message'] = $e->getMessage();
    } catch (BadRequestHttpException $e) {
        $data['status']  = self::API_NOK;
        $data['message'] = $e->getMessage();
    } catch (\Exception $e) {
        $data['status']  = self::API_NOK;
        $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
    }

    return $this->sendJsonResponse($data);
}





    public function actionStaffList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            // Find all staff associated with the vendor
            $staffList = Staff::find()->where(['vendor_details_id' => $vendor_details_id])->all();

            if (! empty($staffList)) {
                $staffData = [];
                foreach ($staffList as $staff) {
                    $staffData[] = $staff->asJson(); // Assuming asJson() formats the staff details appropriately
                }

                $data['status']  = self::API_OK;
                $data['details'] = $staffData;
                $data['message'] = Yii::t("app", "Staff list retrieved successfully.");
            } else {
                $data['status']  = self::API_NOK;
                $data['details'] = [];
                $data['message'] = Yii::t("app", "No staff found for this vendor.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionWalkInImmediateOrder()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User not found or unauthorized.'),
            ]);
        }
        $vendorDetails = VendorDetails::getVendorDetailsByUserId($user_id);

        $vendor_details_id = $vendorDetails->id;
        $request           = Yii::$app->request;

        $rawBody = $request->getRawBody();      // Get raw JSON string
        $post    = json_decode($rawBody, true); // Decode as associative array

        $contact_no           = $post['contact_no'] ?? null;
        $full_name            = $post['full_name'] ?? null;
        $email                = $post['email'] ?? null;
        $description          = $post['description'] ?? '';
        $staff_id             = $post['staff_id'] ?? null;
        $service_payment_type = $post['service_payment_type'] ?? '';
        $membership_id = $post['membership_id'] ?? null;

        // Extract service_ids
        $raw_service_ids = $post['service_ids'] ?? [];
        $service_ids     = [];
        foreach ($raw_service_ids as $s) {
            if (! empty($s['service_id'])) {
                $service_ids[] = $s['service_id'];
            }
        }

        // Extract combo package IDs
        $raw_combo_ids = $post['combo_combo_packages_ids'] ?? [];
        $combo_ids     = [];
        foreach ($raw_combo_ids as $combo) {
            if (! empty($combo['combo_combo_package_id'])) {
                $combo_ids[] = $combo['combo_combo_package_id'];
            }
        }

        if (! $contact_no || ! $full_name || (empty($service_ids) && empty($combo_ids))) {
            return [
                'status' => self::API_NOK,
                'error'  => 'Missing required fields: contact_no, full_name, or service_ids/combo_ids',
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        // try {
        // Create or find guest user
        $user = User::find()->where(['contact_no' => $contact_no])->andWhere(['user_role' => User::ROLE_GUEST])->one();
        if (! $user) {
            $user             = new User();
            $user->first_name = $full_name;
            $user->contact_no = $contact_no;
            $user->username   = $contact_no . '@' . User::ROLE_GUEST . '.com';
            $user->referral_code = User::generateUniqueReferralCode();
            $user->email     = $email;
            $user->user_role = User::ROLE_GUEST;
            $user->status    = User::STATUS_ACTIVE;
            $user->setPassword('12345678');
            $user->generateAuthKey();

            if (! $user->save(false)) {
                throw new \Exception('Failed to create guest user.');
            }
        }
        User::assignUserToStore($user->id, $vendor_details_id, $membership_id);



        // Create order
        $order                       = new Orders();
        $order->user_id              = $user->id;
        $order->vendor_details_id    = $vendor_details_id;
        $order->qty                  = count($service_ids) + count($combo_ids);
        $order->trans_type           = Orders::ORDER_TYPE_WALK_IN_IMMEDIATE;
        $order->service_type         = Orders::SERVICE_TYPE_WALK_IN;
        $order->payment_type         = Orders::TYPE_COD;
        $order->service_payment_type = $service_payment_type;
        $order->status               = Orders::STATUS_NEW_ORDER;
        $order->service_instruction  = $description;
        $order->payment_mode         = Orders::PAYMENT_MODE_FULL;
        $order->payment_status       = Orders::PAYMENT_DONE;
        $order->otp                  = rand(1111, 9999);
        $order->schedule_date        = date('Y-m-d');
        $order->schedule_time        = date('h:i A');
        $order->ip_ress              = $_SERVER['REMOTE_ADDR'];
        $order->platform_source      = Orders::PLATFORM_SOURCE_WEB_VENDOR;


        if (! $order->save(false)) {
            throw new \Exception('Failed to save order.');
        }

        // Add individual services to order
        foreach ($service_ids as $service_id) {
            $service = Services::findOne(['id' => $service_id]);
            if (! $service) {
                continue;
            }

            $item              = new OrderDetails();
            $item->order_id    = $order->id;
            $item->service_id  = $service_id;
            $item->price       = $service->price ?? 0;
            $item->qty         = 1;
            $item->total_price = $service->price ?? 0;
            $item->delete_allowed = 1;
            $item->status      = 1;

            if (! $item->save(false)) {
                throw new \Exception('Failed to save order service.');
            }
        }

        // Add combo packages to order
        foreach ($combo_ids as $combo_id) {
            $combo = ComboPackages::findOne(['id' => $combo_id]);
            if (! $combo) {
                continue;
            }

            $combo_order                    = new ComboOrder();
            $combo_order->order_id          = $order->id;
            $combo_order->vendor_details_id = $vendor_details_id;
            $combo_order->combo_package_id  = $combo_id;
            $combo_order->amount            = $combo->discount_price ?? $combo->price;
            $combo_order->status            = 1;

            if (! $combo_order->save(false)) {
                throw new \Exception('Failed to save combo package.');
            }
        }
        $transaction->commit();

        Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);

        $order = Orders::findOne(['id' => $order->id]);

        // $order_transaction_details_exist = OrderTransactionDetails::findOne(['order_id' => $order->id]);

        // if (empty($order_transaction_details_exist)) {
        //     $order_transaction_details = new OrderTransactionDetails();

        //     // Ensure amount is not negative and has two decimal places
        //     $transaction_amount = floatval($order->payable_amount);
        //     if ($transaction_amount < 0) {
        //         $transaction_amount = 0.00;
        //     }
        //     $transaction_amount = number_format($transaction_amount, 2, '.', '');

        //     $order_transaction_details->order_id       = $order->id;
        //     $order_transaction_details->amount         = $transaction_amount;
        //     $order_transaction_details->payment_source = OrderTransactionDetails::PAYMENT_SOURCE_COD;
        //     $order_transaction_details->payment_type   = OrderTransactionDetails::PAYMENT_TYPE_COD;
        //     if ($service_payment_type == Orders::SERVICE_PAYMENT_TYPE_BEFORE) {
        //         $order_transaction_details->status = OrderTransactionDetails::STATUS_SUCCESS;
        //     } else {
        //         $order_transaction_details->status = OrderTransactionDetails::STATUS_PENDING;
        //     }
        //     $order_transaction_details->save(false);
        // }

        Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
        $order->refresh();

        // ✅ Now assign the order to staff if applicable
        if (! empty($staff_id)) {
            $assignmentResult = OrderAssignmentService::assign($user_id, $staff_id, $order->id);
            if ($assignmentResult['status'] !== self::API_OK) {
                Yii::warning("Order created but staff assignment failed: " . $assignmentResult['message'], __METHOD__);

                // Optional rollback status
                $order->status = Orders::STATUS_NEW_ORDER;
                $order->save(false);
            }
        }

        return [
            'status'   => self::API_OK,
            'message'  => 'Walk-in order created successfully.',
            'order_id' => $order->id,
            'otp'      => $order->otp,
        ];
        // } catch (\Exception $e) {
        //     $transaction->rollBack();
        //     Yii::error('Walk-in order failed: ' . $e->getMessage(), __METHOD__);
        //     return [
        //         'status' => self::API_NOK,
        //         'error'  => $e->getMessage(),
        //     ];
        // }
    }


    //make api pre booking order
    public function actionPreBookingOrder()
    {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User not found or unauthorized.'),
            ]);
        }
        $vendorDetails = VendorDetails::getVendorDetailsByUserId($user_id);

        $vendor_details_id = $vendorDetails->id;
        $request           = Yii::$app->request;

        $rawBody = $request->getRawBody();      // Get raw JSON string
        $post    = json_decode($rawBody, true); // Decode as associative array

        $contact_no  = $post['contact_no'] ?? null;
        $full_name   = $post['full_name'] ?? null;
        $email       = $post['email'] ?? null;
        $description = $post['description'] ?? '';
        $staff_id    = $post['staff_id'] ?? null;
        $date        = $post['date'] ?? null;
        $time        = $post['time'] ?? null;
        $membership_id = $post['membership_id'] ?? null;


        // Extract service_ids
        $raw_service_ids = $post['service_ids'] ?? [];
        $service_ids     = [];
        foreach ($raw_service_ids as $s) {
            if (! empty($s['service_id'])) {
                $service_ids[] = $s['service_id'];
            }
        }

        // Extract combo package IDs
        $raw_combo_ids = $post['combo_combo_packages_ids'] ?? [];
        $combo_ids     = [];
        foreach ($raw_combo_ids as $combo) {
            if (! empty($combo['combo_combo_package_id'])) {
                $combo_ids[] = $combo['combo_combo_package_id'];
            }
        }

        if (! $contact_no || ! $full_name || (empty($service_ids) && empty($combo_ids))) {
            return [
                'status' => self::API_NOK,
                'error'  => 'Missing required fields: contact_no, full_name, or service_ids/combo_ids',
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Create or find guest user
            $user = User::find()->where(['contact_no' => $contact_no])->andWhere(['user_role' => User::ROLE_USER])->one();
            if (! $user) {
                throw new \Exception('User with this contact number not found. Please register first.');
            }
            if (empty($user->email)) {
                $user->email = $email;
                $user->save(false);
            }

            if (empty($user->first_name)) {
                $user->first_name = $full_name;
                $user->save(false);
            }
            User::assignUserToStore($user->id, $vendor_details_id, $membership_id);


            // Create order
            $order                      = new Orders();
            $order->user_id             = $user->id;
            $order->vendor_details_id   = $vendor_details_id;
            $order->qty                 = count($service_ids) + count($combo_ids);
            $order->trans_type          = Orders::ORDER_TYPE_WALK_IN_IMMEDIATE;
            $order->service_type        = Orders::SERVICE_TYPE_WALK_IN;
            $order->payment_type        = Orders::TYPE_COD;
            $order->status              = Orders::STATUS_NEW_ORDER;
            $order->service_instruction = $description;
            $order->payment_status      = Orders::PAYMENT_DONE;
            $order->otp                 = rand(1111, 9999);
            $order->schedule_date       = $date;
            $order->platform_source      = Orders::PLATFORM_SOURCE_WEB_VENDOR;

            if (preg_match('/^\d{1,2}:\d{2} (AM|PM)$/i', $time)) {
                $order->schedule_time = $time;
            } else {
                $dt                   = new \DateTime($time);
                $order->schedule_time = $dt->format('h:i A');
            }

            $order->ip_ress = $_SERVER['REMOTE_ADDR'];

            if (! $order->save(false)) {
                throw new \Exception('Failed to save order.');
            }

            // Add individual services to order
            foreach ($service_ids as $service_id) {
                $service = Services::findOne(['id' => $service_id]);
                if (! $service) {
                    continue;
                }

                $item              = new OrderDetails();
                $item->order_id    = $order->id;
                $item->service_id  = $service_id;
                $item->price       = $service->price ?? 0;
                $item->qty         = 1;
                $item->total_price = $service->price ?? 0;
                $item->status      = 1;
                $item->delete_allowed = 1;


                if (! $item->save(false)) {
                    throw new \Exception('Failed to save order service.');
                }
            }

            // Add combo packages to order
            foreach ($combo_ids as $combo_id) {
                $combo = ComboPackages::findOne(['id' => $combo_id]);
                if (! $combo) {
                    continue;
                }

                $combo_order                    = new ComboOrder();
                $combo_order->order_id          = $order->id;
                $combo_order->vendor_details_id = $vendor_details_id;
                $combo_order->combo_package_id  = $combo_id;
                $combo_order->amount            = $combo->discount_price ?? $combo->price;
                $combo_order->status            = 1;

                if (! $combo_order->save(false)) {
                    throw new \Exception('Failed to save combo package.');
                }
            }
            $transaction->commit();
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();

            // ✅ Now assign the order to staff if applicable
            if (! empty($staff_id)) {
                $assignmentResult = OrderAssignmentService::assign($user_id, $staff_id, $order->id);
                if ($assignmentResult['status'] !== self::API_OK) {
                    Yii::warning("Order created but staff assignment failed: " . $assignmentResult['message'], __METHOD__);

                    // Optional rollback status
                    $order->status = Orders::STATUS_NEW_ORDER;
                    $order->save(false);
                }
            }

            return [
                'status'   => self::API_OK,
                'message'  => 'Walk-in order created successfully.',
                'order_id' => $order->id,
                'otp'      => $order->otp,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Walk-in order failed: ' . $e->getMessage(), __METHOD__);
            return [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        }
    }



    public function actionGetVendorActiveServices()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed. Please log in."),
            ]);
        }

        try {
            $sub_category_id = $post['sub_category_id'] ?? null;
            $service_for     = $post['service_for'] ?? null;
            $search          = $post['search'] ?? null;
            $service_type    = $post['service_type'] ?? null;


            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Active vendor not found."));
            }

            $query = Services::find()
                ->where(['vendor_details_id' => $vendorDetails->id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE]])
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ]);

            if (! empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'service_name', $search],
                    ['like', 'description', $search],
                ]);
            }

            if (! empty($sub_category_id)) {
                $query->andWhere(['sub_category_id' => $sub_category_id]);
            }

            if (! empty($service_for)) {
                $query->andWhere(['service_for' => $service_for]);
            }

            if (! empty($service_type)) {
                $query->andWhere(['type' => $service_type]);
            }



            // ⏬ Count for Walk-in and Home-Visit (not paginated)
            $baseQuery = Services::find()
                ->where(['vendor_details_id' => $vendorDetails->id])
                ->andWhere(['status' => Services::STATUS_ACTIVE])
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ]);

            if (! empty($sub_category_id)) {
                $baseQuery->andWhere(['sub_category_id' => $sub_category_id]);
            }

            if (! empty($service_for)) {
                $baseQuery->andWhere(['service_for' => $service_for]);
            }

            if (! empty($search)) {
                $baseQuery->andWhere([
                    'or',
                    ['like', 'service_name', $search],
                    ['like', 'description', $search],
                ]);
            }

            $walkInCount    = clone $baseQuery;
            $homeVisitCount = clone $baseQuery;

            $walk_in_count    = $walkInCount->andWhere(['type' => Services::TYPE_WALK_IN])->count();
            $home_visit_count = $homeVisitCount->andWhere(['type' => Services::TYPE_HOME_VISIT])->count();
            $services   = $baseQuery->all();

            $list = [];
            foreach ($services as $service) {
                $list[] = $service->asJson();
            }

            $data = [

                'details'          => $list,
                'walk_in_count'    => (int) $walk_in_count,
                'home_visit_count' => (int) $home_visit_count,
            ];
        } catch (\Exception $e) {
            $data = [
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Unexpected error: ") . $e->getMessage(),
            ];
        }

        return $this->sendJsonResponse($data);
    }


    public function actionAssignOrderToStaff()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated
        if (! $user_id) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        try {
            // Validate required fields
            if (empty($post['staff_id']) || empty($post['order_id'])) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "staff_id and Order ID are required.");
                return $this->sendJsonResponse($data);
            }

            $staff_id = $post['staff_id'];
            $order_id = $post['order_id'];

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return $this->sendJsonResponse($data);
            }

            // Fetch the order
            $order = Orders::findOne(['id' => $order_id]);
            if (! $order) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Order not found.");
                return $this->sendJsonResponse($data);
            }

            // Convert schedule time to 24-hour format for comparison
            $scheduledDateTime = \DateTime::createFromFormat('Y-m-d h:i A', $order->schedule_date . ' ' . $order->schedule_time);
            if (! $scheduledDateTime) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Invalid schedule format.");
                return $this->sendJsonResponse($data);
            }

            // Check if staff already has an order at the same time
            $conflictOrder = Orders::find()
                ->alias('o')
                ->innerJoin(HomeVisitorsHasOrders::tableName() . ' ho', 'ho.order_id = o.id')
                ->where(['ho.home_visitor_id' => $staff_id])
                ->andWhere(['o.schedule_date' => $order->schedule_date])
                ->andWhere(['o.status' => Orders::STATUS_ASSIGNED_SERVICE_STAFF])
                ->andWhere(['o.service_type' => $order->service_type])
                ->andWhere(['!=', 'o.id', $order_id])
                ->andWhere(['o.schedule_time' => $order->schedule_time])
                ->one();

            if ($conflictOrder) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "This staff member is already assigned to another order at the same time.");
                return $this->sendJsonResponse($data);
            }

            // Check if the order is already assigned 
            $existingAssignment = HomeVisitorsHasOrders::find()
                ->where(['order_id' => $order_id])
                ->one();

            if ($existingAssignment) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "This order is already assigned to another home visitor.");
                return $this->sendJsonResponse($data);
            }

            // Check if the order is already assigned to the same home visitor
            $sameAssignment = HomeVisitorsHasOrders::findOne([
                'order_id'        => $order_id,
                'home_visitor_id' => $staff_id,
            ]);

            if ($sameAssignment) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "This order is already assigned to the specified home visitor.");
                return $this->sendJsonResponse($data);
            }

            // Validate staff role based on order type
            $staff = Staff::findOne(['id' => $staff_id]);
            if (! $staff) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Staff not found.");
                return $this->sendJsonResponse($data);
            }

            if ($staff->status != Staff::STATUS_ACTIVE) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t(
                    "app",
                    $staff->status != Staff::STATUS_ACTIVE
                        ? "Selected staff is not active."
                        : "Selected staff is not active."
                );
                return $this->sendJsonResponse($data);
            }

            // Assign order and update status
            $order->status = Orders::STATUS_ASSIGNED_SERVICE_STAFF;

            if (! $order->save(false)) {
                Yii::error("Failed to update order status for order ID: {$order_id}", __METHOD__);
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to update the order status.");
                return $this->sendJsonResponse($data);
            }

            $homeVisitorsHasOrders                  = new HomeVisitorsHasOrders();
            $homeVisitorsHasOrders->order_id        = $order_id;
            $homeVisitorsHasOrders->home_visitor_id = $staff_id;
            $homeVisitorsHasOrders->status          = $order->status;

            if (! $homeVisitorsHasOrders->save(false)) {
                Yii::error("Failed to assign order ID: {$order_id} to home visitor ID: {$staff_id}", __METHOD__);
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to assign the order to the home visitor.");
                return $this->sendJsonResponse($data);
            }

            // Save order status history
            $orderStatus           = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status   = $order->status;
            $orderStatus->remarks  = Yii::t("app", "Order status updated to {status}", ['status' => $order->getStateOptionsBadges()]);
            if (! $orderStatus->save(false)) {
                Yii::error("Failed to save order status history for order ID: {$order_id}", __METHOD__);
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to save order status history.");
                return $this->sendJsonResponse($data);
            }

            // Update staff status
            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
            $staff->save(false);

            // Send notifications          
            try {

                // $otp = $order->otp;
                // Determine if the order is a home visit
                $isHomeVisit = $order->service_type == Orders::TRANS_TYPE_HOME_VISIT;

                $titleUser = Yii::t("app", "Your Order Assigned to Staff");
                $bodyUser  = $isHomeVisit
                    ? Yii::t("app", "Your order (#{$order_id}) has been assigned to a home visitor.")
                    : Yii::t("app", "Your order (#{$order_id}) has been assigned to a staff member.");

                // Push notification to the user 
                Yii::$app->notification->PushNotification(
                    $order_id,
                    $order->user_id,
                    $titleUser,
                    $bodyUser,
                    $isHomeVisit ? "home_visit" : "walk_in" // Order type based on service type 
                );

                // Notify home visitor only for home visit orders
                if ($isHomeVisit) {
                    $titleVisitor = Yii::t("app", "New Order Assignment");
                    $bodyVisitor  = Yii::t("app", "You have been assigned a new home visit order (#{$order_id}). Please proceed with the service.");

                    // Push notification to the home visitor
                    Yii::$app->notification->PushNotification(
                        $order_id,
                        $staff->user_id,
                        $titleVisitor,
                        $bodyVisitor,
                        "home_visit"
                    );
                }
            } catch (\Exception $e) {
                // Log the error
                Yii::error("Notification error: " . $e->getMessage(), __METHOD__);
            }

            //new updated code for sending push notification 

            // Success response 
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order successfully assigned to the staff.");
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Yii::error("Error processing order assignment: " . $e->getMessage(), __METHOD__);
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred while processing the request.");
            return $this->sendJsonResponse($data);
        }
    }


    public function actionVerifyOtpOrderCreate()
    {

        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User not found or unauthorized.'),
            ]);
        }

        $contact_no   = $post['contact_no'] ?? null;
        $session_code = $post['session_code'] ?? null;
        $otp_code     = $post['otp_code'] ?? null;

        if (! $contact_no || ! $session_code || ! $otp_code) {
            throw new BadRequestHttpException(Yii::t('app', 'Missing required parameters.'));
        }

        $bypass_numbers = BypassNumbers::find()->where(['mobile_number' => $contact_no])->one();

        // Bypass the OTP verification for specific numbers 
        if (! empty($bypass_numbers->mobile_number) && $bypass_numbers->mobile_number == $contact_no) {
            $send_otp['Status'] = 'Success';
        } else {
            // Call your OTP verification service
            $send_otp = Yii::$app->notification->verifyOtp($session_code, $otp_code);
            $send_otp = json_decode($send_otp, true);
        }

        if ($send_otp['Status'] !== 'Success') {
            $data['status'] = self::API_NOK;
            $data['error']  = $send_otp['Details'] ?? 'OTP verification failed';
            return $this->sendJsonResponse($data);
        }

        // New user registration flow
        $user = User::findOne(['contact_no' => $contact_no, 'user_role' => User::ROLE_GUEST]);
        if ($user) {
            $data['status']  = self::API_OK;
            $data['details'] = $user->asJsonUser();
            return $this->sendJsonResponse($data);
        }

        $user                 = new User();
        $user->username       = $contact_no . '@' . User::ROLE_GUEST . '.com';
        $user->contact_no     = $contact_no;
        $user->unique_user_id = User::generateUniqueUserId('GUEST');
        $user->device_token   = $post['device_token'] ?? null;
        $user->device_type    = $post['device_type'] ?? null;
        $user->user_role      = User::ROLE_GUEST;
        $user->status         = User::STATUS_ACTIVE;
        if (! $user->save(false)) {
            throw new ServerErrorHttpException(Yii::t('app', 'Failed to register user.'));
        }
        $data['status']  = self::API_OK;
        $data['details'] = $user->asJsonUser();

        return $this->sendJsonResponse($data);
    }



    public function actionChangeOrderStatus()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }

        if (empty($post['order_id']) || empty($post['status'])) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID and status are required."),
            ]);
        }

        $order_id = $post['order_id'] ?? null;
        $status   = $post['status'] ?? null;

        try {
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Active vendor not found for this user."),
                ]);
            }

            $order = Orders::find()
                ->where(['vendor_details_id' => $vendorDetails->id, 'id' => $order_id])
                ->one();

            if (! $order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "No active order found with the given order ID."),
                ]);
            }

            // Validate payment before completing order
            if ($status == Orders::STATUS_SERVICE_COMPLETED) {
                $paidAmount = OrderTransactionDetails::find()
                    ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                    ->sum('amount');
                $paidAmount = number_format($paidAmount, 2, '.', '');

                $totalWithTax = $order->total_w_tax;

                if ($paidAmount < $totalWithTax) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Full payment is required before completing the order. Paid ₹{$paidAmount} of ₹{$totalWithTax}."),
                    ]);
                }
            }

            // Update order status
            $order->status = $status;
            if (! $order->save(false)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Failed to update the order status."),
                ]);
            }
            $order->refresh();

            // Create vendor earnings only after order is completed and fully paid
            if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                VendorEarnings::createVendorEaringsFromCompletedOrder($order, $vendorDetails);
            }

            // Update existing earnings to approved for walk-in or home visit
            if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                $storeEarnings = VendorEarnings::find()
                    ->where(['vendor_details_id' => $vendorDetails->id, 'order_id' => $order->id])
                    ->one();

                if ($storeEarnings) {
                    $storeEarnings->status = VendorEarnings::STATUS_APPROVED;
                    $storeEarnings->save(false);
                }
            }

            // Update staff status to idle if assigned
            if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                $assignedStaff = HomeVisitorsHasOrders::find()
                    ->select('home_visitor_id')
                    ->where(['order_id' => $order->id])
                    ->one();

                if ($assignedStaff) {
                    $staff = Staff::findOne($assignedStaff->home_visitor_id);
                    if ($staff) {
                        $staff->current_status = Staff::CURRENT_STATUS_IDLE;
                        $staff->save(false);
                    }
                }
                $order->completed = date('Y-m-d H:i:s');
                $order->save(false);
            }

            // Save order status history
            $orderStatus           = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status   = $order->status;
            $orderStatus->remarks  = "Order status updated to " . $order->getStateOptionsBadges();
            $orderStatus->save(false);

            // Prepare notification message
            if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                $title = Yii::t("app", "Order Completed Successfully");
                $body  = Yii::t("app", "The order with ID #" . $order->id . " has been successfully completed.");
            } else {
                $title = Yii::t("app", "Order Status Updated");
                $body  = Yii::t("app", "The status of your order with ID #" . $order->id . " has been updated.");
            }

            // Send notifications

            Yii::$app->notification->PushNotification($order_id, $order->user_id, $title, $body, 'redirect');

            Yii::$app->notification->PushNotification($order->id, $vendorDetails->user_id, $title, $body, 'redirect');

            // Success response
            $data['status']              = self::API_OK;
            $data['order_status']        = $order->status;
            $data['order_status_badges'] = strip_tags($order->getStateOptionsBadges());
            $data['details']             = Yii::t("app", "Order successfully updated.");
        } catch (\Exception $e) {
            Yii::error("Error changing order status: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred while processing the request.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewOrderById()
    {
        $data     = [];
        $post     = Yii::$app->request->post();
        $headers  = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);
        $order_id = ! empty($post['order_id']) ? $post['order_id'] : '';

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return $this->sendJsonResponse($data);
            }

            $vendor_details_id = $vendorDetails->id;

            // Prepare the query
            $order = Orders::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'id' => $order_id])
                ->one();

            // Check if the order is found
            if ($order) {
                $data['status']  = self::API_OK;
                $data['details'] = $order->asJsonViewById();
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "Order not found for the provided ID.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error("Error viewing order by ID: " . $e->getMessage(), __METHOD__); // Log the error
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred while retrieving the order: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionAvailableSlots()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            $post = Yii::$app->request->post();

            // Validate user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
            }
            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return $this->sendJsonResponse($data);
            }

            $staff_id = $post['staff_id'] ?? null;
            if (empty($staff_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Staff ID is required."));
            }
            $staff = Staff::findOne(['id' => $staff_id]);
            if (empty($staff) || $staff->status != Staff::STATUS_ACTIVE) {
                throw new NotFoundHttpException(Yii::t("app", "Selected staff does not exist or is not active."));
            }




            // Validate required date parameter
            if (empty($post['date'])) {
                throw new BadRequestHttpException(Yii::t("app", "Date is required."));
            }

            $vendorDetails = VendorDetails::getVendorDetailsByUserId($user_id);
            if (empty($vendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }
            $vendor_details_id = $vendorDetails->id;

            $inputDate = $post['date'];
            // Attempt to parse the date into the correct format (Y-m-d)
            $timestamp = strtotime($inputDate);
            if (! $timestamp) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid date format."));
            }
            $date = date('Y-m-d', $timestamp); // Convert to Y-m-d format

            // Prevent showing past dates
            $currentDate = strtotime(date('Y-m-d'));
            if ($timestamp < $currentDate) {
                throw new BadRequestHttpException(Yii::t("app", "You cannot select a past date."));
            }

            // Get the day of the week
            $day  = date('l', $timestamp);
            $days = Days::findOne(['title' => $day]);
            if (empty($days)) {
                throw new NotFoundHttpException(Yii::t("app", "No matching day found in the system."));
            }

            $day_id = $days->id;

            // Find store timings for the specific day
            $storeTimings = StoreTimings::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'day_id' => $day_id, 'status' => StoreTimings::STATUS_ACTIVE])
                ->one();

            if (empty($storeTimings)) {
                throw new NotFoundHttpException(Yii::t("app", "No store timings found for the selected day."));
            }

            // Calculate time slots based on the current date and selected date
            $selectedDate   = strtotime($date);
            $daysDifference = floor(($selectedDate - $currentDate) / (60 * 60 * 24));

            if ($daysDifference == 0) {
                // If the selected date is today, restrict past time slots
                $currentTime = date('H:i');                                                        // Current time in 24-hour format
                $roundedTime = date("H:i", ceil(strtotime($currentTime) / (60 * 30)) * (60 * 30)); // Round to the nearest 30 minutes

                // Use the later of the store's start time or the rounded current time
                $storeStartTime = date('H:i', strtotime($storeTimings->start_time));
                $storeEndTime   = date('H:i', strtotime($storeTimings->close_time));
                $startSlotTime  = max($storeStartTime, $roundedTime);

                // Generate slots from the adjusted start time to the store's close time
                $slots = VendorDetails::getServiceScheduleSlots(30, 0, $startSlotTime, $storeEndTime);
            } else {
                // For future dates, show all available slots
                $slots = VendorDetails::getServiceScheduleSlots(30, 0, $storeTimings->start_time, $storeTimings->close_time);
            }

            // Build the response: only for this staff member
            if (! empty($slots)) {
                $result = [];
                foreach ($slots as $slot) {
                    // Check if staff is already assigned for this date and slot time
                    $isBusy = Orders::find()
                        ->alias('o')
                        ->innerJoin(HomeVisitorsHasOrders::tableName() . ' ho', 'ho.order_id = o.id')
                        ->where([
                            'ho.home_visitor_id' => $staff_id,
                            'o.schedule_date'    => $date,
                            'o.schedule_time'    => $slot, // slot time string must match db format (eg "10:00 AM")
                            'o.status'           => Orders::STATUS_ASSIGNED_SERVICE_STAFF,
                        ])
                        ->exists();

                    $result[] = [
                        'time'      => $slot,
                        'available' => ! $isBusy,
                    ];
                }
                $data['status']  = self::API_OK;
                $data['details'] = $result;
            } else {
                $data['status']  = self::API_NOK;
                $data['details'] = Yii::t("app", "No slots available for the selected date or staff.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }





    public function actionCancelAppointmentRequest()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }
            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return $this->sendJsonResponse($data);
            }

            $order_id      = $post['order_id'];
            $cancel_reason = $post['cancel_reason'];

            // Validate the required parameters
            if (empty($post['order_id'])) {
                throw new BadRequestHttpException(Yii::t("app", "Order ID is required."));
            }

            // Validate the required parameters
            if (empty($post['cancel_reason'])) {
                throw new BadRequestHttpException(Yii::t("app", "Cancel Reason is required."));
            }

            // Find the order
            $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $vendorDetails->id]);
            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or does not belong to the user."));
            }

            // Check if the order is eligible for cancellation
            if ($order->status != Orders::STATUS_NEW_ORDER) {
                throw new BadRequestHttpException(Yii::t("app", "Only new orders can be canceled."));
            }

            // Cancel the order
            $order->status        = Orders::STATUS_CANCELLED_BY_OWNER;
            $order->cancel_reason = $cancel_reason;

            if (! $order->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to cancel the order. Please try again."));
            }

            $amount = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');
            if (empty($amount)) {
                $amount = Wallet::find()->where(['order_id' => $order->id, 'user_id' => $order->user_id, 'status' => Wallet::STATUS_COMPLETED])->sum('amount');
            }

            $wallet                = new Wallet();
            $wallet->order_id      = $order->id;
            $wallet->user_id       = $order->user_id;
            $wallet->amount        = $amount ?? 0;
            $wallet->payment_type  = Wallet::STATUS_CREDITED;
            $wallet->method_reason = "Order cancelled";
            $wallet->description   = Yii::t("app", "Order cancelled. Order ID #") . $order->id;
            $wallet->status        = Wallet::STATUS_COMPLETED;
            $wallet->save(false);
            // Successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order canceled successfully.");
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionUpdateOrderServicePrices()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }
        // Fetch vendor details
        $vendorDetails = VendorDetails::find()
            ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
            ->one();

        if (! $vendorDetails) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Vendor details not found.");
            return $this->sendJsonResponse($data);
        }

        $rawBody = Yii::$app->request->getRawBody();
        $post    = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Invalid JSON format."),
            ]);
        }

        $orderId  = $post['order_id'] ?? null;
        $services = $post['services'] ?? [];

        if (empty($orderId) || ! is_array($services) || count($services) === 0) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID and at least one service are required."),
            ]);
        }

        try {
            $order = Orders::findOne(['id' => $orderId]);

            if (! $order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Order not found."),
                ]);
            }

            foreach ($services as $index => $service) {
                $orderDetailId = $service['order_details_id'] ?? null;
                $newTotalPrice = $service['total_price'] ?? null;

                if (! $orderDetailId || ! is_numeric($newTotalPrice)) {
                    Yii::warning("Skipping invalid service data at index $index", __METHOD__);
                    continue;
                }

                $orderDetail = OrderDetails::findOne(['id' => $orderDetailId, 'order_id' => $order->id]);
                if (! $orderDetail) {
                    Yii::warning("Order detail ID $orderDetailId not found for order $orderId", __METHOD__);
                    continue;
                }

                $orderDetail->total_price = $newTotalPrice;
                if (! $orderDetail->save(false)) {
                    Yii::error("Failed to save order detail ID $orderDetailId", __METHOD__);
                }
            }

            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);

            $order = Orders::findOne(['id' => $orderId]);

            return $this->sendJsonResponse([
                'status'  => self::API_OK,
                'message' => Yii::t("app", "Order services updated successfully."),
                'details' => $order->asJson(),
            ]);
        } catch (\Exception $e) {
            Yii::error("Exception in updating order services for order $orderId: " . $e->getMessage(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Something went wrong while updating services."),
            ]);
        }
    }


    public function actionReSchedule()
    {
        $data    = [];
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }

        // Fetch vendor details
        $vendorDetails = VendorDetails::find()
            ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
            ->one();

        if (! $vendorDetails) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Vendor details not found.");
            return $this->sendJsonResponse($data);
        }

        $post    = Yii::$app->request->post();
        $orderId = $post['orderId'] ?? Yii::$app->request->get('orderId');
        $message = $post['message'] ?? null;
        $date    = $post['date'] ?? null;
        $time    = $post['time'] ?? null;

        if (empty($orderId) || empty($date) || empty($time)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID, date, and time are required."),
            ]);
        }

        try {
            $order = Orders::findOne(['id' => $orderId]);

            if (! $order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Order not found."),
                ]);
            }

            $old_dt_time = $order->schedule_date . ' ' . $order->schedule_time;
            $new_dt_time = $date . ' ' . $time;

            $order->schedule_date = $date;
            $order->schedule_time = $time;
            $order->status        = Orders::STATUS_NEW_ORDER;

            if (! $order->save(false)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Failed to update order schedule."),
                ]);
            }

            $reschedule_order_logs                        = new RescheduleOrderLogs();
            $reschedule_order_logs->order_id              = $order->id;
            $reschedule_order_logs->reschedule_by_user_id = $user_id;
            $reschedule_order_logs->message               = $message;
            $reschedule_order_logs->old_dt_time           = $old_dt_time;
            $reschedule_order_logs->new_dt_time           = $new_dt_time;

            if (! $reschedule_order_logs->save(false)) {
                Yii::error("Failed to save reschedule log for order ID: {$order->id}", __METHOD__);
            }

            return $this->sendJsonResponse([
                'status'  => self::API_OK,
                'details' => $order->asJson(),
            ]);
        } catch (\Exception $e) {
            Yii::error("Reschedule error for order ID $orderId: " . $e->getMessage(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Something went wrong while rescheduling the order."),
            ]);
        }
    }




    public function getProductsUsedInOrder()
    {
        $data    = [];
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post = Yii::$app->request->post();
        $orderId = $post['orderId'] ?? '';

        if (!$user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }

        // Validate orderId
        if (empty($orderId)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID is required."),
            ]);
        }

        // Fetch vendor details
        try {
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (!$vendorDetails) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Vendor details not found."),
                ]);
            }

            $order = Orders::findOne(['id' => $orderId, 'vendor_details_id' => $vendorDetails->id]);
            if (!$order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Order not found."),
                ]);
            }

            $order_details = OrderDetails::find()
                ->select('service_id')
                ->where(['order_id' => $order->id])
                ->asArray()
                ->all();

            if (empty($order_details)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "No order details found for this order."),
                ]);
            }

            $serviceIds = array_column($order_details, 'service_id');

            $product_services = Services::find()
                ->joinWith('productServices')
                ->where(['service.id' => $serviceIds])
                ->all();

            $list = [];
            if (!empty($product_services)) {
                foreach ($product_services as $product_services_data) {
                    $list[] = $product_services_data->asJsonForProductAndServiceUom();
                }
            }
            return $this->sendJsonResponse([
                'status' => self::API_OK,
                'data'   => $list,
            ]);
        } catch (\Throwable $e) {
            Yii::error("Error fetching products used in order ID $orderId: " . $e->getMessage(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Something went wrong while fetching product details for this order."),
            ]);
        }
    }


    public function actionUpdateProductsUsedOrder()
    {
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post = json_decode(Yii::$app->request->getRawBody(), true);
        $orderId = $post['orderId'] ?? '';
        $products_used = $post['products_used'] ?? [];


        if (!$user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }

        if (empty($orderId)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID is required."),
            ]);
        }

        if (empty($products_used) || !is_array($products_used)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Products used data is required and should be array."),
            ]);
        }

        try {
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (!$vendorDetails) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Vendor details not found."),
                ]);
            }

            $order = Orders::findOne(['id' => $orderId, 'vendor_details_id' => $vendorDetails->id]);
            if (!$order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Order not found."),
                ]);
            }

            $errors = [];
            foreach ($products_used as $index => $products_used_data) {
                $product_id = $products_used_data['product_id'] ?? null;
                $service_id = $products_used_data['service_id'] ?? null;
                $quantity = $products_used_data['quantity'] ?? null;
                $uom_id = $products_used_data['uom_id'] ?? null;
                $order_id = $products_used_data['order_id'] ?? null;

                if (empty($product_id)) {
                    $errors[] = "Missing product_id at index $index.";
                    continue;
                }
                if (!is_numeric($quantity)) {
                    $errors[] = "Invalid 'quantity' value for product_id $product_id at index $index.";
                    continue;
                }


                $product_services_used = new ProductServicesUsed();
                $product_services_used->product_id = $product_id;
                $product_services_used->service_id = $service_id;
                $product_services_used->quantity = $quantity;
                $product_services_used->uom_id = $uom_id;
                $product_services_used->order_id = $order_id;
                if (!$product_services_used->save(false)) {
                    $errors[] = "Failed to save ProductServicesUsed for product_id $product_id.";
                }
            }

            if (!empty($errors)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Some records failed to update or insert: ") . implode(' ', $errors),
                ]);
            }

            return $this->sendJsonResponse([
                'status' => self::API_OK,
                'message' => Yii::t("app", "Products used status updated/created successfully."),
            ]);
        } catch (\Throwable $e) {
            Yii::error("Error updating/creating products used in order ID $orderId: " . $e->getMessage(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Something went wrong while updating product usage for this order."),
            ]);
        }
    }


    public function actionVerifyUser()
    {
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User not found or unauthorized.'),
            ]);
        }

        $post       = Yii::$app->request->post();
        $contact_no = $post['contact_no'] ?? null;

        if (empty($contact_no)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Contact number is required.'),
            ]);
        }

        $vendorDetails = VendorDetails::getVendorDetailsByUserId($user_id);

        if (empty($vendorDetails)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Vendor details not found for the user.'),
            ]);
        }

        $user_guest = User::find()
            ->where([
                'contact_no' => $contact_no,
                'user_role'  => User::ROLE_GUEST,
            ])
            ->one();

        if (! $user_guest) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Guest user not found with the given contact number.'),
            ]);
        }
        $storesHasUsers = StoresHasUsers::find()->where(['guest_user_id' => $user_guest->id, 'vendor_details_id' => $vendorDetails->id])->one();

        if (! empty($storesHasUsers)) {
            return $this->sendJsonResponse([
                'status'  => self::API_OK,
                'details' => $user_guest->asJsonUserClient($vendorDetails->id),
            ]);
        } else {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User does not have store access.'),
            ]);
        }
    }


    public function actionGetMemberships()
    {
        $data        = [];

        try {

            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Get vendor ID for logged-in user
            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }


            if (!Yii::$app->request->isGet) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only GET requests are allowed."));
            }


            $list = MemberShips::getMemberships($vendorId);


            $data['status']      = self::API_OK;
            $data['message']     = Yii::t("app", "MemberShips fetched successfully.");
            $data['memberships'] = $list;
        } catch (\Throwable $e) {


            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->getCode() ?: 500;

            Yii::error([
                'message' => $e->getMessage(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
}
 