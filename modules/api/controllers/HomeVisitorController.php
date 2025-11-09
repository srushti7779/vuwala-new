<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\components\MyOperatorComponent;
use app\models\User;
use app\modules\admin\models\Auth;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\BypassNumbers;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\ServiceOrderImages;
use app\modules\admin\models\Staff;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\FcmNotification;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\ShopReview;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class HomeVisitorController extends BKController
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
                            'check',
                            'index',
                            'send-otp',
                            'resend-otp',
                            'verify-otp',
                            'my-orders',
                            'start-and-verify-otp-of-order',
                            'change-order-status',
                            'upload-service-order-images',
                            'view-order-by-id',
                            'my-profile',
                            'home-visitor-notifications',
                            'call-to-user',
                            'list-store-reviews',
                            'calendar',
                            'update-order-service-prices'







                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'check',
                            'index',
                            'send-otp',
                            'resend-otp',
                            'verify-otp',
                            'my-orders',
                            'start-and-verify-otp-of-order',
                            'change-order-status',
                            'upload-service-order-images',
                            'view-order-by-id',
                            'my-profile',
                            'home-visitor-notifications',
                            'call-to-user',
                            'list-store-reviews',
                             'calendar',
                            'update-order-service-prices'
















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

    //Check Address or Pin code deliverable or not





    public function actionCheck()
    {
        $data = [];

        $headers = getallheaders();
        $auth_code = isset($headers['auth_code']) ? $headers['auth_code'] : null;
        if ($auth_code == null) {
            $auth_code = \Yii::$app->request->get('auth_code');
        }
        if ($auth_code) {
            $auth_session = AuthSession::find()->where([
                'auth_code' => $auth_code,
            ])->one();
            if ($auth_session) {
                $user = $auth_session->createUser;
                $data['status'] = self::API_OK;
                $data['detail'] = $user->asJsonHomeVisitor();
                if (isset($_POST['AuthSession'])) {
                    $auth_session->device_token = $_POST['AuthSession']['device_token'];
                    if ($auth_session->save()) {
                        $data['auth_session'] = Yii::t("app", 'Auth Session updated');
                    } else {
                        $data['error'] = $auth_session->flattenErrors;
                    }
                }
            } else {
                $data['error'] = Yii::t("app", 'session not found');
            }
        } else {
            $data['error'] = Yii::t("app", 'Auth code not found');
            $data['auth'] = isset($auth_code) ? $auth_code : '';
        }

        return $this->sendJsonResponse($data);
    }


    public function actionSendOtp()
    {
        $data = [];
        try {
            $post = Yii::$app->request->post();
            if (!empty($post)) {
                $contact_no = $post['contact_no'];

                $send_otp = Yii::$app->notification->sendOtp($contact_no);

                $send_otp = json_decode($send_otp, true);

                if ($send_otp['Status'] == 'Success') {
                    $data['status'] = self::API_OK;
                    $data['details'] = $send_otp;
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error'] = Yii::t("app", "OTP failed");
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "No data posted");
            }
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }



    public function actionVerifyOtp()
    {
        $data = [];
        $post = Yii::$app->request->post();

        if (!empty($post)) {
            $contact_no = $post['contact_no'] ?? null;
            $session_code = $post['session_code'] ?? null;
            $otp_code = $post['otp_code'] ?? null;

            // Check if required fields are missing
            if (empty($contact_no) || empty($session_code) || empty($otp_code)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Missing required fields: contact number, session code, or OTP.");
                return $this->sendJsonResponse($data);
            }


            $bypass_numbers = BypassNumbers::find()->where(['mobile_number' => $contact_no])->one();




            // Bypass the OTP verification for specific numbers 
            if (!empty($bypass_numbers->mobile_number) && $bypass_numbers->mobile_number == $contact_no) {
                $send_otp['Status'] = 'Success';
            } else {
                // Verify the OTP
                $send_otp = Yii::$app->notification->verifyOtp($session_code, $otp_code);
                $send_otp = json_decode($send_otp, true);
            }










            if ($send_otp['Status'] === 'Success') {
                $providerId = User::ROLE_STAFF;
                $auth_id = $contact_no;

                // Check if user auth exists
                $auth = Auth::find()->where(['source' => $providerId, 'source_id' => $auth_id])->one();

                if (!empty($auth)) {
                    $user = $auth->user;

                    // Check if user is active
                    if ($user->status == User::STATUS_ACTIVE) {
                        $user->device_token = $post['device_token'] ?? null;
                        $user->device_type = $post['device_type'] ?? null;
                        $user->save(false);

                        Yii::$app->user->login($user);
                        $data['status'] = self::API_OK;
                        $data['details'] = $user;
                        $data['auth_code'] = AuthSession::newSession($user)->auth_code;
                    } else {
                        $data['status'] = self::API_NOK;
                        $data['error'] = Yii::t("app", "Your account is inactive or blocked. Please contact support.");
                    }
                } else {
                    // If auth does not exist, check if the number is already registered
$check = User::find()
    ->where(['contact_no' => $contact_no])
    ->andWhere(['IN', 'user_role', [User::ROLE_HOME_VISITOR, User::ROLE_STAFF]])
    ->one();


                    if (!empty($check)) {
                        $auth = new Auth();
                        $auth->user_id = $check->id;
                        $auth->source = $providerId;
                        $auth->source_id = $auth_id;

                        if ($auth->save(false)) {
                            $user = $auth->user;
                            $user->device_token = $post['device_token'] ?? null;
                            $user->device_type = $post['device_type'] ?? null;
                            $user->save(false);

                            Yii::$app->user->login($user);

                            $data['status'] = self::API_OK;
                            $data['details'] = $user;
                            $data['auth_code'] = AuthSession::newSession($user)->auth_code;
                        } else {
                            $data['status'] = self::API_NOK;
                            $data['error'] = Yii::t("app", "Failed to save authentication data.");
                        }
                    } else {
                        $data['status'] = self::API_NOK;
                        $data['error'] = Yii::t("app", "This number is not registered with us. Please contact support.");
                    }
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "OTP verification failed. Please try again.");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "No data was posted. Please send the required information.");
        }

        return $this->sendJsonResponse($data);
    }




    public function actionMyOrders()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $page = isset($post['page']) ? max(0, ((int)$post['page'] - 1)) : 0;
        $status = $post['status'] ?? '';
        $start_date = $post['start_date'] ?? null;
        $end_date = $post['end_date'] ?? null;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $home_visitor = Staff::findOne(['user_id' => $user_id]);
            if (empty($home_visitor)) {
                throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found. Please log in."));
            }

            $home_visitor_id = $home_visitor->id;

            $query = Orders::find()->alias('o')->innerJoinWith(['homeVisitorsHasOrders ho'])
                ->where(['ho.home_visitor_id' => $home_visitor_id]);

            if (!empty($status)) {
                if ($status == Orders::STATUS_CANCELLED) {
                    $cancelStatuses = [
                        Orders::STATUS_CANCELLED_BY_OWNER,
                        Orders::STATUS_CANCELLED_BY_USER,
                        Orders::STATUS_CANCELLED_BY_ADMIN,
                        Orders::STATUS_CANCELLED_BY_SERVICE_BOY,
                    ];
                    $query->andWhere(['in', 'o.status', $cancelStatuses]);
                } else {
                    $query->andWhere(['o.status' => $status]);
                }
            }

            // Date range filtering
            if (!empty($start_date) && !empty($end_date)) {
                $query->andWhere(['between', 'DATE(o.schedule_date)', $start_date, $end_date]);
            } elseif (!empty($start_date)) {
                $query->andWhere(['DATE(o.schedule_date)' => $start_date]);
            }
            

            // Clone query for aggregation
            $cloneQuery = clone $query;
            $totalOrders = $cloneQuery->count();
            $totalRevenue = $cloneQuery->sum('o.total_w_tax');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]], // ✅ fixed here
                'pagination' => [
                    'pageSize' => 20,
                    'page' => $page,
                ],
            ]);

            $ordersList = [];
            foreach ($dataProvider->models as $order) {
                $ordersList[] = $order->asJson();
            }

            if (!empty($ordersList)) {
                $data['status'] = self::API_OK;
                $data['message'] = Yii::t("app", "Orders retrieved successfully.");
                $data['details'] = $ordersList;
                $data['total_bookings'] = (int)$totalOrders;
                $data['total_revenue'] = (float)$totalRevenue;

                $pagination = $dataProvider->pagination;
                $data['pagination'] = [
                    'total_pages' => $pagination->getPageCount(),
                    'total_items' => $pagination->totalCount,
                    'current_page' => $pagination->getPage() + 1,
                    'page_size' => $pagination->getPageSize(),
                ];
            } else {
                throw new NotFoundHttpException(Yii::t("app", "No orders found for the given criteria."));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Authorization error: {message}", ['message' => $e->getMessage()]);
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "{message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred. Please try again later.");
        }

        return $this->sendJsonResponse($data);
    }






    public function actionChangeOrderStatus()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (!$user_id) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Find home visitor details
        $home_visitors = Staff::findOne(['user_id' => $user_id]);
        if (empty($home_visitors)) {
            throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found. Please log in."));
        }

        // Validate the required POST parameters
        if (empty($post['order_id']) || empty($post['status'])) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Order ID and status are required.");
            return $this->sendJsonResponse($data);
        }

        $order_id = $post['order_id'];
        $status = $post['status'];
        $latitude = $post['latitude']?? null;
        $longitude = $post['longitude']?? null;

   

        try {
            // Fetch Vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['id' => $home_visitors->vendor_details_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();
            $home_visitor_id = $home_visitors->id;

            // Fetch the order
            $order = Orders::find()->innerJoinWith(['homeVisitorsHasOrders as ho'])->where(['ho.home_visitor_id' => $home_visitor_id, 'orders.id' => $order_id])->one();
  
            if($status == Orders::STATUS_ARRIVED_CUSTOMER_LOCATION  && $order->service_type==Orders::SERVICE_TYPE_HOME_VISIT  && (empty($latitude) || empty($longitude))) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error' => Yii::t("app", "Latitude and longitude are required for service start.")
            ]);
        }


            if (!$order) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "No active order found with the given order ID.");
                return $this->sendJsonResponse($data);
            }


               if ($status == Orders::STATUS_SERVICE_COMPLETED) {
            $paidAmount = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');
            $paidAmount = number_format($paidAmount, 2, '.', '');


            $totalWithTax = $order->total_w_tax;

            if ($paidAmount < $totalWithTax) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error' => Yii::t("app", "Full payment is required before completing the order. Paid ₹{$paidAmount} of ₹{$totalWithTax}.")
                ]);
            }
        }
        

            // Update the order status
            $order->status = $status;
            if (!$order->save(false)) {
                $home_visitors_has_orders = HomeVisitorsHasOrders::findOne(['order_id' => $order_id, 'home_visitor_id' => $home_visitor_id]);
                if ($home_visitors_has_orders) {
                    $home_visitors_has_orders->status = $status;
                    if(!empty($latitude) && !empty($longitude)) {
                        $home_visitors_has_orders->latitude = $latitude;
                        $home_visitors_has_orders->longitude = $longitude;
                    }   
               
                $home_visitors_has_orders->save(false);
                }

                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Failed to update the order status.");
                return $this->sendJsonResponse($data);
            }

            // Handle payout logic if necessary
            if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                VendorEarnings::createVendorEaringsFromCompletedOrder($order, $vendorDetails);
                $staff = Staff::findOne(['user_id' => $user_id]);
                $staff->current_status = Staff::CURRENT_STATUS_IDLE;

                $staff->save(false);
                $order->completed = date('Y-m-d H:i:s');
                $order->save(false);
            }

            if ($order->status == Orders::STATUS_SERVICE_COMPLETED && ($order->trans_type == Orders::TRANS_TYPE_HOME_VISIT)) {
                $staff = Staff::findOne(['user_id' => $user_id]);
                $staff->current_status = Staff::CURRENT_STATUS_IDLE;
                $staff->save(false);
            }

            // Save order status update in history
            $orderStatus = new OrderStatus();
            $orderStatus->order_id = $order_id;
            $orderStatus->status = $order->status;
            $orderStatus->remarks = "Order status updated to " . $order->getStateOptionsBadges();
            if (!$orderStatus->save(false)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Failed to save order status history.");
                return $this->sendJsonResponse($data);
            }

            // Send notification to the user about the order status change
            $title = Yii::t("app", "Your Order Status Updated");
            $body = Yii::t("app", "Your order status has been updated to: ") . trim(strip_tags(html_entity_decode($order->getStateOptionsBadges())));

            Yii::$app->notification->PushNotification($order_id, $order->user_id, $title, $body, 'redirect');

            // Send notification to the vendor and homevisitor about the order status change
            $vendorTitle = Yii::t("app", 'Order Status Changed');
            $vendorBody = Yii::t("app", 'The status of order ID #') . $order->id . Yii::t("app", ' has been updated to: ') . trim(strip_tags(html_entity_decode($order->getStateOptionsBadges())));
            Yii::$app->notification->PushNotification($order->id, $order->vendorDetails->user_id, $vendorTitle, $vendorBody, 'redirect');

            Yii::$app->notification->PushNotification($order->id, $user_id, $vendorTitle, $vendorBody, 'redirect');


            // Success response
            $data['status'] = self::API_OK;
            $data['order_status'] = $order->status;
            $data['order_status_badges'] = strip_tags($order->getStateOptionsBadges());
            $data['details'] = Yii::t("app", "Order successfully updated.");
        } catch (\Exception $e) {
            Yii::error("Error changing order status: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while processing the request.");
        }

        return $this->sendJsonResponse($data);
    }




    public function actionStartAndVerifyOtpOfOrder()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (!$user_id) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Check if POST data exists
        if (empty($post)) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "No data provided.");
            return $this->sendJsonResponse($data);
        }

        // Check for required fields
        if (empty($post['orderId']) || empty($post['otp'])) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Order ID, and OTP are required.");
            return $this->sendJsonResponse($data);
        }

        $orderId = $post['orderId'];
        $otp = $post['otp'];

        // Fetch the order based on the provided criteria
        try {
            // Find home visitor details
            $home_visitors = Staff::findOne(['user_id' => $user_id]);
            if (empty($home_visitors)) {
                throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found. Please log in."));
            }
            $vendor_details_id = $home_visitors->vendor_details_id;
            $order = Orders::find()
                ->where(['id' => $orderId, 'vendor_details_id' => $vendor_details_id])
                ->one();

            if (!$order) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Order not found for the given details.");
                return $this->sendJsonResponse($data);
            }


            // Check if the provided OTP matches the order's OTP
            if ($order->otp != $otp) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "OTP verification failed. Please check the OTP and try again.");
                return $this->sendJsonResponse($data);
            }

            // Update the order's verification status
            $order->status = Orders::STATUS_SERVICE_STARTED;
            $order->is_verify = Orders::OTP_VERIFIED;
            if ($order->save(false)) {
                // Send a notification to the user after successful OTP verification
                $title = Yii::t("app", "Your Order OTP is Verified");
                $body = Yii::t("app", "Your OTP for order ID #{$order->id} has been successfully verified.");


                Yii::$app->notification->PushNotification(
                    $orderId,
                    $order->user_id,
                    $title,
                    $body,
                    'redirect' // Order type based on service type
                );
                // Yii::$app->notification->customVendorNoti($orderId, $order->user_id, $title, $body, 'redirect');

                $data['status'] = self::API_OK;
                $data['details'] = Yii::t("app", "OTP successfully verified.");
                $data['is_verify'] = $order->is_verify;
            } else {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Failed to update the order status.");
            }
        } catch (\Exception $e) {
            Yii::error("Error verifying OTP for order: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while processing the request.");
        }

        return $this->sendJsonResponse($data);
    }




    public function actionUploadServiceOrderImages()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (!$user_id) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Check if POST data exists
        if (empty($post)) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "No data provided.");
            return $this->sendJsonResponse($data);
        }

        // Check for required fields
        if (empty($post['orderId']) || empty($post['images_files'])) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Order ID and image files are required.");
            return $this->sendJsonResponse($data);
        }

        // Fetch the order based on the provided criteria
        try {
            $orderId = $post['orderId'];
            $images_files = $post['images_files'];

            if (!empty($images_files)) {
                $images_files_array = explode(',', $images_files);
                foreach ($images_files_array as $images_file) {
                    // Validate image file (e.g., check if it exists)
                    if (empty($images_file)) {
                        continue; // Skip empty entries
                    }

                    $service_order_images = new ServiceOrderImages();
                    $service_order_images->order_id = $orderId;
                    $service_order_images->image = $images_file;
                    $service_order_images->status = ServiceOrderImages::STATUS_ACTIVE;

                    // Attempt to save the image record
                    if (!$service_order_images->save(false)) {
                        Yii::error("Failed to save image for order ID {$orderId}: " . json_encode($service_order_images->errors), __METHOD__);
                        $data['status'] = self::API_NOK;
                        $data['error'] = Yii::t("app", "Failed to save image: " . implode(', ', $service_order_images->getFirstErrors()));
                        return $this->sendJsonResponse($data);
                    }
                }
            }

            $data['status'] = self::API_OK;
            $data['details'] = Yii::t("app", "Images uploaded successfully.");
        } catch (\Exception $e) {
            Yii::error("Error uploading images for order ID {$orderId}: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while processing the request: " . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }




    public function actionGetServiceOrderImages()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (!$user_id) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Check if POST data exists
        if (empty($post)) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "No data provided.");
            return $this->sendJsonResponse($data);
        }

        // Check for required fields
        if (empty($post['orderId'])) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Order ID is required.");
            return $this->sendJsonResponse($data);
        }

        try {
            $orderId = $post['orderId'];

            // Fetch the images for the specified order ID
            $images = ServiceOrderImages::find()
                ->where(['order_id' => $orderId, 'status' => ServiceOrderImages::STATUS_ACTIVE])
                ->all();

            if (empty($images)) {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "No images found for the given order ID.");
                return $this->sendJsonResponse($data);
            }

            // Prepare response data
            $imageDetails = [];
            foreach ($images as $image) {
                $imageDetails[] = [
                    'id' => $image->id,
                    'order_id' => $image->order_id,
                    'image_url' => Yii::getAlias('@web') . '/uploads/service_images/' . $image->image, // Construct the full image URL
                    'status' => $image->status,
                ];
            }

            $data['status'] = self::API_OK;
            $data['details'] = $imageDetails;
        } catch (\Exception $e) {
            Yii::error("Error fetching images for order ID {$orderId}: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while processing the request: " . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }










    public function actionViewOrderById()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $order_id = !empty($post['order_id']) ? $post['order_id'] : '';

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find home visitor details
            $home_visitors = Staff::findOne(['user_id' => $user_id]);
            if (empty($home_visitors)) {
                throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found. Please log in."));
            }

            // Find the order by ID and ensure it belongs to the authenticated home visitor
            $order = Orders::find()
                ->innerJoinWith(['homeVisitorsHasOrders as ho'])
                ->where(['orders.id' => $order_id, 'ho.home_visitor_id' => $home_visitors->id])
                ->one();

            if ($order) {
                // Prepare order data
                $orderData = $order->asJson();

                // Fetch images if the order status is "completed"
                if ($order->status == Orders::STATUS_SERVICE_COMPLETED) {
                    $serviceImages = ServiceOrderImages::find()
                        ->where(['order_id' => $order->id, 'status' => ServiceOrderImages::STATUS_ACTIVE])
                        ->all();

                    $imagesList = [];
                    foreach ($serviceImages as $image) {
                        $imagesList[] = $image->asJson(); // Assuming `asJson` method returns the full path of the image
                    }

                    $orderData['service_images'] = $imagesList;
                } else {
                    $orderData['service_images'] = [];
                }

                $data['status'] = self::API_OK;
                $data['details'] = $orderData;
            } else {
                $data['status'] = self::API_NOK;
                $data['error'] = Yii::t("app", "Order not found for the provided ID.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error("Error viewing order by ID: " . $e->getMessage(), __METHOD__); // Log the error
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while retrieving the order: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionMyProfile()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }


            $model = User::findOne($user_id);
            if (empty($model)) {
                throw new NotFoundHttpException(Yii::t("app", "User profile not found."));
            }

            // Prepare success response
            $data['status'] = self::API_OK;
            $data['details'] = $model->asJsonHomeVisitor();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }





    public function actionHomeVisitorNotifications()
    {
        $data = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post = Yii::$app->request->post();

        try {
            // Check user authentication
            if (empty($user_id)) {
                return $this->sendErrorResponse('User authentication failed. Please log in.');
            }

            // Set pagination parameters with defaults
            $pageSize = isset($post['page_size']) ? (int)$post['page_size'] : 10;
            $page = isset($post['page']) ? max((int)$post['page'] - 1, 0) : 0;

            // Fetch notifications with pagination
            $query = FcmNotification::find()->where(['user_id' => $user_id]);
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page' => $page,
                ],
                'sort' => [
                    'defaultOrder' => ['created_on' => SORT_DESC],
                ],
            ]);

            // Fetch notification data
            $notifications = $dataProvider->getModels();
            $totalCount = $dataProvider->getTotalCount();

            // Send dynamic Firebase notifications
            foreach ($notifications as $notification) {
                $title = $notification->title;  // Dynamic title from the database
                $body = $notification->message; // Dynamic body from the database

                // Access the FirebaseNotification component and call homevisitorNotification()
                Yii::$app->notification->homevisitorNotification($user_id, $title, $body);
            }


            // Prepare response data
            $notificationData = array_map(function ($notification) {
                return $notification->asJson(); // Assuming `asJson()` provides formatted output
            }, $notifications);

            // Calculate pagination details
            $pagination = [
                'total_count' => $totalCount,
                'page' => $dataProvider->pagination->page + 1, // Convert back to 1-based index
                'page_size' => $pageSize,
                'total_pages' => ceil($totalCount / $pageSize),
            ];

            // Count unread notifications
            $unreadCount = $query->andWhere(['is_read' => FcmNotification::IS_READ_NO])->count();

            // Finalize response
            $data['status'] = self::API_OK;
            $data['message'] = 'Notifications retrieved and sent successfully.';
            $data['details'] = $notificationData;
            $data['pagination'] = $pagination;
            $data['unread_count'] = $unreadCount;
        } catch (\Exception $e) {
            Yii::error("Error fetching notifications: " . $e->getMessage(), __METHOD__);
            return $this->sendErrorResponse('An unexpected error occurred: ' . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }


    public function actionCallToUser()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $post = Yii::$app->request->post();
            $order_id = $post['order_id'] ?? null;

            if (empty($order_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'order_id is required.'));
            }

            $order = Orders::findOne(['id' => $order_id]);
            $user_contact = $order->user->contact_no;
            $vendor_contact = $order->homeVisitorsHasOrders->homeVisitor->user->contact_no;
            $MyOperatorComponent = new MyOperatorComponent();
            $makeAnonymousCall =  $MyOperatorComponent->makeAnonymousCall($vendor_contact, $user_contact);

            $data['status'] = self::API_OK;
            $data['details'] = json_decode($makeAnonymousCall);
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error' => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage()
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }



    public function actionListStoreReviews()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find home visitor
            $homeVisitor = Staff::findOne(['user_id' => $user_id]);
            if (empty($homeVisitor)) {
                throw new NotFoundHttpException(Yii::t("app", "Home visitor not found."));
            }

            $page = isset($post['page']) ? max(1, (int)$post['page']) : 1;
            $pageSize = isset($post['page_size']) ? (int)$post['page_size'] : 10;

            // Get order IDs served by this home visitor
            $orderIds = (new \yii\db\Query())
                ->select('order_id')
                ->from('home_visitors_has_orders')
                ->where(['home_visitor_id' => $homeVisitor->id])
                ->column();

            if (empty($orderIds)) {
                throw new NotFoundHttpException(Yii::t("app", "No reviews found. You haven't served any orders yet."));
            }

            // Get reviews for those orders
            $query = ShopReview::find()
                ->where(['order_id' => $orderIds]);

            $totalCount = $query->count();

            $reviews = $query
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->orderBy(['id' => SORT_DESC])
                ->all();

            $reviewData = [];
            foreach ($reviews as $review) {
                $reviewData[] = $review->asJson(); // or build manually
            }

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Reviews for served orders fetched successfully.");
            $data['reviews'] = $reviewData;
            $data['pagination'] = [
                'total_count' => $totalCount,
                'page' => $page,
                'page_size' => $pageSize,
                'total_pages' => ceil($totalCount / $pageSize),
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error("Error in HomeVisitorController::actionListStoreReviews - " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }



public function actionCalendar()
{
    $data = [];
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    try {
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
        }

        $home_visitor = Staff::findOne(['user_id' => $user_id]);
        if (!$home_visitor) {
            throw new NotFoundHttpException(Yii::t("app", "Home visitor details not found."));
        }

        $home_visitor_id = $home_visitor->id;

        $start = new \DateTime(); // today's date
        $dates = [];

        for ($i = 0; $i < 30; $i++) {
            $currentDate = $start->format('Y-m-d');

       $orderCount = Orders::find()
    ->alias('o')
    ->innerJoin('home_visitors_has_orders ho', 'ho.order_id = o.id')
    ->where([
        'ho.home_visitor_id' => $home_visitor_id,
        'DATE(o.schedule_date)' => $currentDate,
    ])
    ->andWhere(['in', 'o.status', [
        Orders::STATUS_NEW_ORDER,
        Orders::STATUS_ACCEPTED,
        Orders::STATUS_SERVICE_STARTED,
        Orders::STATUS_ASSIGNED_SERVICE_STAFF,
        Orders::STATUS_ARRIVED_CUSTOMER_LOCATION,
        Orders::STATUS_WAITING_FOR_APPROVAL
    ]])
    ->andWhere([
        'or',
        ['o.payment_status' => Orders::PAYMENT_DONE],
        ['o.is_next_visit' => 1]
    ])
    ->count();

            $dates[] = [
                'date' => $currentDate,
                'order_count' => $orderCount
            ];

            $start->modify('+1 day');
        }

        $data['status'] = self::API_OK;
        $data['dates'] = $dates;
        $data['message'] = Yii::t("app", "Calendar data retrieved successfully.");
    } catch (UnauthorizedHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['message'] = $e->getMessage();
    } catch (NotFoundHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['message'] = $e->getMessage();
    } catch (\Exception $e) {
        $data['status'] = self::API_NOK;
        $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
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




}
