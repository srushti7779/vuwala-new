<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\components\MyOperatorComponent;
use app\components\Razorpay;
use app\components\WhatsApp;
use app\models\User;
use app\modules\admin\models\Auth;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\Banner;
use app\modules\admin\models\BannerChargeLogs;
use app\modules\admin\models\base\ShopReview;
use app\modules\admin\models\base\StoreServiceTypes;
use app\modules\admin\models\base\WhatsappWebhookLogs;
use app\modules\admin\models\BusinessDocuments;
use app\modules\admin\models\BusinessImages;
use app\modules\admin\models\BypassNumbers;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\ComboServices;
use app\modules\admin\models\Coupon;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\Days;
use app\modules\admin\models\EmailOtpVerifications;
use app\modules\admin\models\FcmNotification;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\NextVisitDetails;
use app\modules\admin\models\Notification;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\OrderDiscounts;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderStatus;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\ProductServices;
use app\modules\admin\models\Reels;
use app\modules\admin\models\ReelTags;
use app\modules\admin\models\RescheduleOrderLogs;
use app\modules\admin\models\Services;
use app\modules\admin\models\ServiceType;
use app\modules\admin\models\Staff;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\SubCategory;
use app\modules\admin\models\Subscriptions;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\VendorMainCategoryData;
use app\modules\admin\models\VendorPayout;
use app\modules\admin\models\VendorSubscriptions;
use app\modules\admin\models\Wallet;
use app\modules\admin\models\WebSetting;
use app\modules\admin\models\WhatsappApiLogs;
use app\modules\api\controllers\BKController;
use DateTime;
use Exception;
use yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

class VendorController extends BKController
{

    public $enableCsrfValidation = false;
    const OTP_EXPIRY_MINUTES     = 10;
    const RATE_LIMIT_ATTEMPTS    = 5;
    const RATE_LIMIT_WINDOW      = 3600; // 1 hour in seconds

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
                            'check',
                            'index',
                            'send-otp',
                            'resend-otp',
                            'verify-otp',
                            'update-profile',
                            'create-catlog',
                            'my-profile',
                            'category',
                            'sub-category',
                            'vendor-selected-main-category',
                            'business-details-create-or-update',
                            'add-or-update-address',
                            'vendor-details-upload-documents',
                            'store-timings',
                            'submit-store-timings',
                            'get-main-services',
                            'delete-main-services',
                            'add-or-update-service-type',
                            'add-or-update-service',
                            'update-service',
                            'services-list',
                            'delete-service',
                            'change-service-status',
                            'my-shop-orders',
                            'add-or-update-staff',
                            'update-staff',
                            'staff-list',
                            'active-staff-list',
                            'business-profile',
                            'delete-business-image',
                            'add-reels',
                            'reels-list',
                            'subscriptions-list',
                            'active-subscription',
                            'accept-or-reject',
                            'change-order-status',
                            'start-and-verify-otp-of-order',
                            'start-order',
                            'dashboard',
                            'my-earnings',
                            'home-visitor-list',
                            'assign-order-to-staff',
                            'upload-business-images',
                            'booking-history',
                            'view-order-by-id',
                            'my-profile',
                            'cancel-appointment-request',
                            'my-earnings-history',
                            'add-or-update-coupon',
                            'update-coupon',
                            'coupon-list',
                            'list-store-reviews',
                            'reel-delete',
                            'staff-inactive-or-active',
                            'notifications',
                            'clear-notifications',
                            'read-notification',
                            'update-staff-status',
                            'view-staff-by-id',
                            'view-reel-by-id',
                            'view-coupon-by-id',
                            'delete-coupon',
                            'get-service-list-by-order-id',
                            'save-next-visit-details',
                            'update-reels',
                            'verify-subscription-payment',
                            'payment-conformation-webhooks',
                            'create-subscription-order',
                            'verify-payment',
                            'login',
                            'get-service-types',
                            'shop-reviews',
                            'update-store-timings',
                            'get-no-of-visits',
                            'vendor-qr-pay-history',
                            'create-subscription-request',
                            'view-subscription-details',
                            'collect-cash',
                            'enable-sub-services',
                            'add-or-update-bank-details',
                            'get-vendor-servicies',
                            'view-services-by-id',
                            'update-child-services',
                            'delete-child-services',
                            'self-update-vendor-categories',
                            'calendar',
                            'vendor-service-types',
                            'add-or-update-sub-category',
                            'add-or-update-combo-package',
                            'add-or-update-combo-service',
                            'view-combo-services',
                            'change-combo-package-status',
                            'get-sub-categories',
                            'toggle-vendor-service-type-status',
                            'change-sub-category-status',
                            'list-combo-packages',
                            'services-list-for-combo',
                            'call-to-user',
                            'banners-list',
                            'view-banner-by-id',
                            'send-email-otp',
                            'verify-email-otp',
                            'change-banner-status',
                            'order-status',
                            're-schedule',
                            'whatsapp-webhook',
                            'update-all-vendor-addresses',
                            'update-order-service-prices',
                            'available-slots',
                            'store-available-by-date',
                            'reels-dashboard',
                            'banners-dashboard',
                            'coupons-dashboard',
                            'coupon-change-status',
                            'view-business-details-by-id',
                            'change-reel-status',
                            'apply-service-discount',
                            'apply-order-discount',
                            'mark-as-paid',
                            'get-all-services-list-for-next-visit',
                            'test-whatsapp'

                        ],

                        'allow'   => true,
                        'roles'   => [
                            '@',
                        ],
                    ],
                    [

                        'actions' => [
                            'check',
                            'index',
                            'send-otp',
                            'resend-otp',
                            'verify-otp',
                            'update-profile',
                            'my-profile',
                            'create-catlog',
                            'category',
                            'sub-category',
                            'vendor-selected-main-category',
                            'vendor-details',
                            'business-details-create-or-update',
                            'add-or-update-address',
                            'vendor-details-upload-documents',
                            'store-timings',
                            'submit-store-timings',
                            'get-main-services',
                            'delete-main-services',
                            'add-or-update-service-type',
                            'add-or-update-service',
                            'update-service',
                            'services-list',
                            'delete-service',
                            'change-service-status',
                            'my-shop-orders',
                            'add-or-update-staff',
                            'update-staff',
                            'staff-list',
                            'active-staff-list',
                            'business-profile',
                            'business-profiles',
                            'delete-business-image',
                            'add-reels',
                            'reels-list',
                            'subscriptions-list',
                            'active-subscription',
                            'accept-or-reject',
                            'change-order-status',
                            'start-and-verify-otp-of-order',
                            'start-order',
                            'dashboard',
                            'my-earnings',
                            'home-visitor-list',
                            'assign-order-to-staff',
                            'upload-business-images',
                            'booking-history',
                            'view-order-by-id',
                            'my-profile',
                            'cancel-appointment-request',
                            'my-earnings-history',
                            'add-or-update-coupon',
                            'update-coupon',
                            'list-store-reviews',
                            'coupon-list',
                            'reel-delete',
                            'staff-inactive-or-active',
                            'notifications',
                            'read-notification',
                            'update-staff-status',
                            'view-staff-by-id',
                            'view-reel-by-id',
                            'view-coupon-by-id',
                            'delete-coupon',
                            'get-service-list-by-order-id',
                            'save-next-visit-details',
                            'update-reels',
                            'verify-subscription-payment',
                            'payment-conformation-webhooks',
                            'create-subscription-order',
                            'verify-payment',
                            'login',
                            'get-service-types',
                            'shop-reviews',
                            'update-store-timings',
                            'get-no-of-visits',
                            'vendor-qr-pay-history',
                            'create-subscription-request',
                            'view-subscription-details',
                            'clear-notifications',
                            'collect-cash',
                            'enable-sub-services',
                            'add-or-update-bank-details',
                            'get-vendor-servicies',
                            'view-services-by-id',
                            'update-child-services',
                            'delete-child-services',
                            'self-update-vendor-categories',
                            'calendar',
                            'vendor-service-types',
                            'add-or-update-sub-category',
                            'add-or-update-combo-package',
                            'add-or-update-combo-service',
                            'view-combo-services',
                            'change-combo-package-status',
                            'get-sub-categories',
                            'toggle-vendor-service-type-status',
                            'change-sub-category-status',
                            'list-combo-packages',
                            'services-list-for-combo',
                            'call-to-user',
                            'banners-list',
                            'view-banner-by-id',
                            'send-email-otp',
                            'verify-email-otp',
                            'change-banner-status',
                            'order-status',
                            're-schedule',
                            'whatsapp-webhook',
                            'update-all-vendor-addresses',
                            'update-order-service-prices',
                            'available-slots',
                            'store-available-by-date',
                            'reels-dashboard',
                            'banners-dashboard',
                            'coupons-dashboard',
                            'coupon-change-status',
                            'view-business-details-by-id',
                            'change-reel-status',
                            'apply-service-discount',
                            'apply-order-discount',
                            'mark-as-paid',
                            'get-all-services-list-for-next-visit',
                            'test-whatsapp'

                        ],

                        'allow'   => true,
                        'roles'   => [

                            '?',
                            '*',

                        ],
                    ],
                ],
            ],

        ]);
    }

    public function actionIndex()
    {
        $data['details'] = ['hi'];
        return $this->sendJsonResponse($data);
    }

    protected function convertToDate($dateString)
    {
        try {
            $date = new \DateTime($dateString);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    }

    public function actionSendOtp()
    {
        $data = [];
        try {
            $post = Yii::$app->request->post();
            if (! empty($post)) {
                $contact_no = $post['contact_no'];
                $send_otp   = Yii::$app->notification->sendOtp($contact_no);
                $send_otp   = json_decode($send_otp, true);
                if ($send_otp['Status'] == 'Success') {
                    $data['status']  = self::API_OK;
                    $data['details'] = $send_otp;
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "OTP failed");
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No data posted");
            }
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVerifyOtp()
    {
        $transaction = null;

        try {
            // 1. Validate input data
            $post = Yii::$app->request->post();
            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t('app', 'No data posted.'));
            }

            $contact_no   = $post['contact_no'] ?? null;
            $session_code = $post['session_code'] ?? null;
            $otp_code     = $post['otp_code'] ?? null;
            $device_type  = $post['device_type'] ?? null;

            if (! $contact_no || ! $session_code || ! $otp_code) {
                throw new BadRequestHttpException(Yii::t('app', 'Missing required parameters: contact_no, session_code, or otp_code.'));
            }

            // 2. Verify OTP (with bypass logic)
            $otpResult = $this->verifyOtpWithBypass($contact_no, $session_code, $otp_code);
            if ($otpResult['Status'] !== 'Success') {
                throw new UnprocessableEntityHttpException(
                    $otpResult['Details'] ?? 'OTP verification failed'
                );
            }

            if ($device_type == User::DEVICE_TYPE_WEB) {
                $user_vendor = User::find()
                    ->where(['contact_no' => $contact_no, 'user_role' => User::ROLE_VENDOR])
                    ->one();
                if (empty($user_vendor)) {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t('app', 'User not found or inactive.');
                    return $this->sendJsonResponse($data);
                }
            }

            // 3. Check for existing authentication
            $providerId = User::ROLE_VENDOR;
            $auth_id    = $contact_no;
            $auth       = Auth::find()->where(['source' => $providerId, 'source_id' => $auth_id])->one();

            if ($auth) {
                // Existing user flow
                $user = $auth->user;
                if (! $user) {
                    throw new ServerErrorHttpException(Yii::t('app', 'Auth record exists but user not found.'));
                }

                if ($user->status != User::STATUS_ACTIVE) {
                    throw new ForbiddenHttpException(Yii::t('app', 'User account is inactive or blocked.'));
                }

                $user = $this->updateUserDeviceInfo($user, $post);
            } else {
                // New user registration flow
                $transaction = Yii::$app->db->beginTransaction();

                // Check if user already exists without auth record (data inconsistency)
                $existingUser = User::findOne(['contact_no' => $contact_no, 'user_role' => User::ROLE_VENDOR]);
                if ($existingUser) {
                    // User exists but no auth record - create the missing auth record
                    $auth            = new Auth();
                    $auth->user_id   = $existingUser->id;
                    $auth->source    = $providerId;
                    $auth->source_id = $auth_id;

                    if (! $auth->save(false)) {
                        $transaction->rollBack();
                        throw new ServerErrorHttpException(
                            Yii::t('app', 'Failed to create auth record: {errors}', [
                                'errors' => implode(', ', $auth->getFirstErrors()),
                            ])
                        );
                    }

                    $user = $this->updateUserDeviceInfo($existingUser, $post);
                    $transaction->commit();
                } else {
                    // Create completely new user
                    $user = $this->createNewUser($contact_no, $post);

                    // Create Auth record for new user
                    $auth            = new Auth();
                    $auth->user_id   = $user->id;
                    $auth->source    = $providerId;
                    $auth->source_id = $auth_id;

                    if (! $auth->save()) {
                        $transaction->rollBack();
                        throw new ServerErrorHttpException(
                            Yii::t('app', 'Failed to create auth record: {errors}', [
                                'errors' => implode(', ', $auth->getFirstErrors()),
                            ])
                        );
                    }

                    $transaction->commit();
                }
            }

            // 4. Login user and prepare response
            Yii::$app->user->login($user);

            return $this->sendJsonResponse([
                'status'    => self::API_OK,
                'details'   => $user->asJsonVendor(),
                'auth_code' => AuthSession::newSession($user)->auth_code,
            ]);
        } catch (\yii\web\HttpException $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            return $this->sendJsonResponse([
                'status'     => self::API_NOK,
                'error'      => $e->getMessage(),
                'error_code' => $e->statusCode,
            ]);
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            Yii::error([
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'contact_no' => $contact_no ?? 'unknown',
            ], __METHOD__);

            return $this->sendJsonResponse([
                'status'     => self::API_NOK,
                'error'      => Yii::t('app', 'An unexpected error occurred. Please try again.'),
                'error_code' => 500,
            ]);
        }
    }

    /**
     * Verify OTP with bypass number logic
     */
    private function verifyOtpWithBypass($contactNo, $sessionCode, $otpCode)
    {
        $bypassNumbers = BypassNumbers::find()
            ->where(['mobile_number' => $contactNo])
            ->one();

        if ($bypassNumbers && $bypassNumbers->mobile_number === $contactNo) {
            return ['Status' => 'Success'];
        }

        $result = Yii::$app->notification->verifyOtp($sessionCode, $otpCode);
        return is_array($result) ? $result : json_decode($result, true);
    }

    /**
     * Create a new user
     */
    private function createNewUser($contactNo, $postData)
    {
        $user                 = new User();
        $user->username       = $contactNo . '@' . User::ROLE_VENDOR . '.com';
        $user->contact_no     = $contactNo;
        $user->unique_user_id = User::generateUniqueUserId('P');
        $user->device_token   = $postData['device_token'] ?? null;
        $user->device_type    = $postData['device_type'] ?? null;
        $user->user_role      = User::ROLE_VENDOR;
        $user->status         = User::STATUS_ACTIVE;
        $user->referral_code  = User::generateUniqueReferralCode();

        if (! $user->save()) {
            throw new ServerErrorHttpException(
                Yii::t('app', 'Failed to create user: {errors}', [
                    'errors' => implode(', ', $user->getFirstErrors()),
                ])
            );
        }

        return $user;
    }

    /**
     * Update user device information
     */
    private function updateUserDeviceInfo($user, $postData)
    {
        $user->device_token = $postData['device_token'] ?? $user->device_token;
        $user->device_type  = $postData['device_type'] ?? $user->device_type;

        if (! $user->save(false)) {
            Yii::warning("Failed to update device info for user {$user->id}", __METHOD__);
        }

        return $user;
    }

    public function actionUpdateProfile()
    {
        $data      = [];
        $post      = Yii::$app->request->post();
        $auth_code = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($auth_code);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t('app', 'No data posted.'));
            }

            $model = User::findOne(['id' => $user_id, 'user_role' => User::ROLE_VENDOR]);
            if (! $model) {
                throw new NotFoundHttpException(Yii::t('app', 'User not found.'));
            }

            // Update first name if provided
            if (! empty($post['first_name'])) {
                $model->first_name = $post['first_name'];
            }

            // Check for email uniqueness
            if (! empty($post['email'])) {
                $existingUser = User::find()
                    ->where(['email' => $post['email']])
                    ->andWhere(['!=', 'id', $user_id])
                    ->one();

                if ($existingUser) {
                    throw new BadRequestHttpException(Yii::t('app', 'This email is already taken.'));
                }

                $model->email = $post['email'];
            }

            // Update gender if provided
            if (! empty($post['gender'])) {
                $model->gender = $post['gender'];
            }

            // Update date of birth if provided, ensuring it's stored in the correct format
            if (! empty($post['date_of_birth'])) {
                $model->date_of_birth = Yii::$app->formatter->asDate($post['date_of_birth'], 'php:Y-m-d');
            }

            // Save the model and handle errors
            if ($model->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $model->asJsonUser();
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to update profile. Please try again.'));
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
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionBusinessDetailsCreateOrUpdate()
    {
        $data                      = [];
        $post                      = Yii::$app->request->post();
        $auth_code                 = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                      = new AuthSettings();
        $user_id                   = $auth->getAuthSession($auth_code);
        $vendor_main_category_data = $post['vendor_main_category_data'] ?? null;
        $gender_type               = $post['gender_type'] ?? null;

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Ensure required fields are provided
            // Ensure required fields are provided
            if (empty($post['business_name']) || empty($post['logo'])) {
                throw new BadRequestHttpException(Yii::t('app', 'Business name and logo are required.'));
            }

            $user = User::findOne(['id' => $user_id, 'user_role' => User::ROLE_VENDOR]);
            if (! $user) {
                throw new NotFoundHttpException(Yii::t('app', 'User not found.'));
            }

            $main_vendor_user_id = $user->create_user_id ?? null;

            // Check if vendor details already exist for the user
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);

            // If vendor details exist, update them; otherwise, create new ones
            if ($vendorDetails === null) {
                $vendorDetails          = new VendorDetails();
                $vendorDetails->user_id = $user_id;
                $vendorDetails->status  = VendorDetails::STATUS_VERIFICATION_PENDING;
            }

            // Populate vendor details
            $vendorDetails->main_category_id    = $post['main_category_id'] ?? null;
            $vendorDetails->business_name       = $post['business_name'];
            $vendorDetails->website_link        = ! empty($post['website_link']) ? $post['website_link'] : null;
            $vendorDetails->gst_number          = $post['gst_number'] ?? null;
            $vendorDetails->msme_number         = $post['msme_number'] ?? null;
            $vendorDetails->gender_type         = $gender_type;
            $vendorDetails->main_vendor_user_id = $main_vendor_user_id;
            $vendorDetails->logo                = $post['logo'] ?? null;
            $vendorDetails->no_of_branches      = $post['no_of_branches'] ?? null;
            $vendorDetails->no_of_sitting       = $post['no_of_sitting'] ?? null;
            $vendorDetails->no_of_staff         = $post['no_of_staff'] ?? null;

            // Save vendor details
            if ($vendorDetails->save(false)) {

                // New vendor details created, so we generate store timings
                User::generateStoreTimings($vendorDetails->id);

                $business_images = ! empty($post['business_images']) ? $post['business_images'] : '';
                if (! empty($business_images)) {
                    $business_images_arr = explode(',', $business_images);
                    if (! empty($business_images_arr)) {
                        foreach ($business_images_arr as $business_image) {
                            $business_images                    = new BusinessImages();
                            $business_images->vendor_details_id = $vendorDetails->id;
                            $business_images->image_file        = $business_image;
                            $business_images->status            = BusinessImages::STATUS_ACTIVE;
                            $business_images->save(false);
                        }
                    }
                }

                if (! empty($vendor_main_category_data)) {
                    $vendor_main_category_data_array = explode(',', $vendor_main_category_data);

                    if (! empty($vendor_main_category_data_array)) {
                        // Optionally clear existing entries for this vendor_details_id and user_id
                        VendorMainCategoryData::deleteAll(['vendor_details_id' => $vendorDetails->id, 'user_id' => $user_id]);

                        foreach ($vendor_main_category_data_array as $mainCategoryId) {
                            $mainCategoryId = trim($mainCategoryId); // Just in case

                            if (! empty($mainCategoryId)) {
                                $vendorCategory                    = new VendorMainCategoryData();
                                $vendorCategory->user_id           = $user_id;
                                $vendorCategory->vendor_details_id = $vendorDetails->id;
                                $vendorCategory->main_category_id  = $mainCategoryId;
                                $vendorCategory->status            = VendorMainCategoryData::STATUS_ACTIVE;
                                $vendorCategory->save(false); // Skipping validation, add validation if needed
                            }
                        }
                    }
                }

                $data['status']  = self::API_OK;
                $data['details'] = $vendorDetails->asJson();
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to save vendor details. Please try again.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUploadBusinessImages()
    {
        $data      = [];
        $post      = Yii::$app->request->post();
        $auth_code = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($auth_code);

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details for the authenticated user
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found for this user.'));
            }

            // Check if business images are provided
            $business_images = ! empty($post['business_images']) ? $post['business_images'] : '';

            if (! empty($business_images)) {

                $business_images_arr = explode(',', $business_images);
                foreach ($business_images_arr as $image) {
                    $businessImage                    = new BusinessImages();
                    $businessImage->vendor_details_id = $vendorDetails->id;
                    $businessImage->image_file        = $image;
                    $businessImage->status            = BusinessImages::STATUS_ACTIVE;

                    if (! $businessImage->save(false)) {
                        Yii::error("Failed to save business image for vendor ID: {$vendorDetails->id}. Image: {$image}");
                        throw new ServerErrorHttpException(Yii::t('app', 'Failed to save one or more business images.'));
                    }
                }

                //    $businessImage = new BusinessImages();
                //     $businessImage->vendor_details_id = $vendorDetails->id;
                //     $businessImage->image_file = $business_images;
                //     $businessImage->status = BusinessImages::STATUS_ACTIVE;

                //     if (!$businessImage->save(false)) {
                //         Yii::error("Failed to save business image for vendor ID: {$vendorDetails->id}. Image: {$business_images}");
                //         throw new ServerErrorHttpException(Yii::t('app', 'Failed to save one or more business images.'));
                //     }

            } else {
                throw new BadRequestHttpException(Yii::t('app', 'No business images provided.'));
            }

            // Successful response
            $data['status']  = self::API_OK;
            $data['details'] = $vendorDetails->asJson();
            $data['message'] = Yii::t('app', 'Business images uploaded successfully.');
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
            Yii::error($e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateAddress()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $latitude               = ! empty($post['latitude']) ? $post['latitude'] : null;
            $longitude              = ! empty($post['longitude']) ? $post['longitude'] : null;
            $address                = ! empty($post['address']) ? $post['address'] : null;
            $location_name          = ! empty($post['location_name']) ? $post['location_name'] : null;
            $street                 = ! empty($post['street']) ? $post['street'] : null;
            $iso_country_code       = ! empty($post['iso_country_code']) ? $post['iso_country_code'] : null;
            $country                = ! empty($post['country']) ? $post['country'] : null;
            $postal_code            = ! empty($post['postal_code']) ? $post['postal_code'] : null;
            $administrative_area    = ! empty($post['administrative_area']) ? $post['administrative_area'] : null;
            $subadministrative_area = ! empty($post['subadministrative_area']) ? $post['subadministrative_area'] : null;
            $locality               = ! empty($post['locality']) ? $post['locality'] : null;
            $sublocality            = ! empty($post['sublocality']) ? $post['sublocality'] : null;
            $thoroughfare           = ! empty($post['thoroughfare']) ? $post['thoroughfare'] : null;
            $subthoroughfare        = ! empty($post['subthoroughfare']) ? $post['subthoroughfare'] : null;

            if (empty($latitude) || empty($longitude) || empty($address)) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude, Longitude, and Address are required.'));
            }

            $vendor_details = VendorDetails::find()->where(['user_id' => $user_id])->one();

            if (empty($vendor_details)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $vendor_details->latitude  = $latitude;
            $vendor_details->longitude = $longitude;
            $vendor_details->address   = $address;

            $vendor_details->location_name          = $location_name;
            $vendor_details->street                 = $street;
            $vendor_details->iso_country_code       = $iso_country_code;
            $vendor_details->country                = $country;
            $vendor_details->postal_code            = $postal_code;
            $vendor_details->administrative_area    = $administrative_area;
            $vendor_details->subadministrative_area = $subadministrative_area;
            $vendor_details->locality               = $locality;
            $vendor_details->sublocality            = $sublocality;
            $vendor_details->thoroughfare           = $thoroughfare;
            $vendor_details->subthoroughfare        = $subthoroughfare;

            if ($vendor_details->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $vendor_details->asJson();
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to update vendor details. Please try again.'));
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
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    protected function saveOrUpdateDocuments($vendor_details_id, $file, $document_type)
    {
        // Always create new record instead of updating existing
        $business_documents = new BusinessDocuments();

        $business_documents->vendor_details_id = $vendor_details_id;
        $business_documents->file              = $file;
        $business_documents->document_type     = $document_type;
        $business_documents->status            = BusinessDocuments::STATUS_ACTIVE;

        if (!$business_documents->save(false)) {
            throw new ServerErrorHttpException(Yii::t('app', 'Failed to save document.'));
        }
    }

    public function actionVendorDetailsUploadDocuments()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        // Handle raw JSON body too
        if (empty($post)) {
            $rawBody = Yii::$app->request->getRawBody();
            $post = json_decode($rawBody, true);
        }

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t('app', 'No data posted.'));
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);

            if (!$vendor) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Medical council docs (single or multiple)
            if (!empty($post['medical_council_reg_certificate'])) {
                $medicalDocs = is_array($post['medical_council_reg_certificate'])
                    ? $post['medical_council_reg_certificate']
                    : [$post['medical_council_reg_certificate']];

                foreach ($medicalDocs as $file) {
                    if (!empty($file)) {
                        $this->saveOrUpdateDocuments(
                            $vendor->id,
                            $file,
                            BusinessDocuments::MEDICAL_COUNCIL_REG_CERTIFICATE
                        );
                    }
                }
            }

            // Clinic reg docs (single or multiple)
            if (!empty($post['clinic_reg_certificate'])) {
                $clinicDocs = is_array($post['clinic_reg_certificate'])
                    ? $post['clinic_reg_certificate']
                    : [$post['clinic_reg_certificate']];

                foreach ($clinicDocs as $file) {
                    if (!empty($file)) {
                        $this->saveOrUpdateDocuments(
                            $vendor->id,
                            $file,
                            BusinessDocuments::CLINIC_REG_CERTIFICATE
                        );
                    }
                }
            }

            $data['status']  = self::API_OK;
            $data['details'] = $vendor->asJson();
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
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }


    public function actionStoreTimings()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch store timings
            $vendor_details_id = $vendor->id;
            $store_timings     = StoreTimings::find()->where(['vendor_details_id' => $vendor_details_id])->all();

            if (! empty($store_timings)) {
                $list = [];
                foreach ($store_timings as $store_timings_data) {
                    $list[] = $store_timings_data->asJson();
                }
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No store timings found for the vendor.');
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred. ' . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }

public function actionSubmitStoreTimings()
{
    $data = [];

    // Get both sources
    $rawBody  = Yii::$app->request->getRawBody();
    $jsonPost = json_decode($rawBody, true);          // raw JSON body (may be array or object)
    $formPost = Yii::$app->request->post();          // form-data / x-www-form-urlencoded

    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth    = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    // try {
        // Fetch vendor details
        $vendor = VendorDetails::findOne(['user_id' => $user_id]);
        if (empty($vendor)) {
            throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
        }

        // Determine source of store_timing_data
        $store_timings_array = null;

        // 1) If form POST contains store_timing_data (common for form-data)
        if (!empty($formPost['store_timing_data'])) {
            $raw = $formPost['store_timing_data'];

            // If it's already an array (PHP will provide array if the field is sent as array)
            if (is_array($raw)) {
                $store_timings_array = $raw;
            } elseif (is_string($raw)) {
                // Try decode JSON string from form field
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $store_timings_array = $decoded;
                } else {
                    // attempt to clean control chars and decode again
                    $clean = trim($raw);
                    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);
                    $decoded2 = json_decode($clean, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                        $store_timings_array = $decoded2;
                    } else {
                        throw new BadRequestHttpException(Yii::t('app', 'Invalid store timing data format (form field JSON).'));
                    }
                }
            } else {
                throw new BadRequestHttpException(Yii::t('app', 'Invalid store timing data format (form field).'));
            }
        }
        // 2) Else if raw JSON body contained a top-level `store_timing_data`
        elseif (!empty($jsonPost) && isset($jsonPost['store_timing_data'])) {
            $raw = $jsonPost['store_timing_data'];
            if (is_array($raw)) {
                $store_timings_array = $raw;
            } elseif (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $store_timings_array = $decoded;
                } else {
                    throw new BadRequestHttpException(Yii::t('app', 'Invalid store timing data format (JSON body).'));
                }
            } else {
                throw new BadRequestHttpException(Yii::t('app', 'Invalid store timing data format (JSON body).'));
            }
        }
        // 3) Else, if the raw JSON body itself is an array of items (client submitted array directly)
        elseif (is_array($jsonPost) && array_values($jsonPost) === $jsonPost) {
            // numeric-indexed array => treat it as the store timings array
            $store_timings_array = $jsonPost;
        }

        // If still null or empty -> missing data
        if (empty($store_timings_array) || !is_array($store_timings_array)) {
            throw new BadRequestHttpException(Yii::t('app', 'Store timing data is required and must be a non-empty array.'));
        }

        // Validate each item and update inside a transaction
        $transaction = Yii::$app->db->beginTransaction();

        foreach ($store_timings_array as $store_timing_set) {
            if (!is_array($store_timing_set)) {
                throw new BadRequestHttpException(Yii::t('app', 'Each store timing entry must be an object/array.'));
            }

            // Required: store_timings_id, start_time, close_time
            if (!isset($store_timing_set['store_timings_id'], $store_timing_set['start_time'], $store_timing_set['close_time'])) {
                throw new BadRequestHttpException(Yii::t('app', 'Store timings ID, start time, and close time are required.'));
            }

            $store_timings = StoreTimings::findOne(['id' => $store_timing_set['store_timings_id']]);
            if (empty($store_timings)) {
                throw new NotFoundHttpException(Yii::t('app', 'Store timing record not found.'));
            }

         
            $store_timings->start_time = $store_timing_set['start_time'];
            $store_timings->close_time = $store_timing_set['close_time'];
            $store_timings->status     = isset($store_timing_set['status']) ? (int)$store_timing_set['status'] : StoreTimings::STATUS_ACTIVE;

            if (!$store_timings->save(false)) {
                Yii::error('Failed to save StoreTimings: ' . print_r($store_timings->errors, true));
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to save store timing record.'));
            }
        }

        $transaction->commit();

        // Notification (kept original behavior)
        $notification             = new Notification();
        $notification->user_id    = $user_id;
        $notification->title      = 'New vendor Onboarded Notification';
        $notification->mark_read  = 0;
        $notification->created_on = date('Y-m-d');
        if (! $notification->save(false)) {
            Yii::error("Notification Send Failed: " . print_r($notification->errors, true));
            // don't abort — main operation succeeded
        }

        $data['status']  = self::API_OK;
        $data['details'] = Yii::t('app', 'Store timings updated successfully.');

    // } catch (NotFoundHttpException $e) {
    //     if (isset($transaction) && $transaction->isActive) {
    //         $transaction->rollBack();
    //     }
    //     $data['status'] = self::API_NOK;
    //     $data['error']  = $e->getMessage();
    //     Yii::error($e->getMessage());
    // } catch (BadRequestHttpException $e) {
    //     if (isset($transaction) && $transaction->isActive) {
    //         $transaction->rollBack();
    //     }
    //     $data['status'] = self::API_NOK;
    //     $data['error']  = $e->getMessage();
    //     Yii::error($e->getMessage());
    // } catch (ServerErrorHttpException $e) {
    //     if (isset($transaction) && $transaction->isActive) {
    //         $transaction->rollBack();
    //     }
    //     $data['status'] = self::API_NOK;
    //     $data['error']  = $e->getMessage();
    //     Yii::error($e->getMessage());
    // } catch (\Exception $e) {
    //     if (isset($transaction) && $transaction->isActive) {
    //         $transaction->rollBack();
    //     }
    //     $data['status'] = self::API_NOK;
    //     $data['error']  = Yii::t('app', 'An unexpected error occurred.');
    //     Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
    // }

    return $this->sendJsonResponse($data);
}

    public function actionUpdateStoreTimings()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Validate store timing data 
            $store_timing_data = $post['store_timing_data'] ?? '';
            if (empty($store_timing_data)) {
                throw new BadRequestHttpException(Yii::t('app', 'Store timing data is required.'));
            }

            // Decode JSON data
            $store_timings_array = json_decode($store_timing_data, true);


            if (! is_array($store_timings_array)) {
                throw new BadRequestHttpException(Yii::t('app', 'Invalid store timing data format.'));
            }

            foreach ($store_timings_array as $store_timing_set) {
                // Ensure required fields are provided
                if (! isset($store_timing_set['day_id'], $store_timing_set['start_time'], $store_timing_set['close_time'])) {
                    throw new BadRequestHttpException(Yii::t('app', 'Day ID, start time, and close time are required.'));
                }

                // Fetch the StoreTimings record for the vendor and specific day
                $store_timings = StoreTimings::findOne([
                    'vendor_details_id' => $vendor->id,
                    'day_id'            => $store_timing_set['day_id'],
                ]);

                if (empty($store_timings)) {
                    // Create a new store timing entry if it doesn't exist
                    $store_timings                    = new StoreTimings();
                    $store_timings->vendor_details_id = $vendor->id;
                    $store_timings->day_id            = $store_timing_set['day_id'];
                    $store_timings->created_on        = date('Y-m-d H:i:s');
                    $store_timings->create_user_id    = $user_id;
                }

                // Update store timing fields
                $store_timings->start_time = $store_timing_set['start_time'];
                $store_timings->close_time = $store_timing_set['close_time'];
                $store_timings->status     = $store_timing_set['status'] ?? StoreTimings::STATUS_ACTIVE;
                $store_timings->updated_on = date('Y-m-d H:i:s');

                // Save the store timing record
                if (! $store_timings->save()) {
                    throw new ServerErrorHttpException(Yii::t('app', 'Failed to update store timing record.'));
                }
            }

            // Respond with success message
            $data['status']  = self::API_OK;
            $data['details'] = Yii::t('app', 'Store timings updated successfully.');
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetMainServices()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor = VendorDetails::find()
                ->select(['id', 'main_category_id'])
                ->where(['user_id' => $user_id])
                ->limit(1)
                ->one();

            if (! $vendor) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $main_categories = [];

            if (! empty($vendor->main_category_id)) {
                $main_categories[] = $vendor->main_category_id;
            }

            $additional_categories = VendorMainCategoryData::find()
                ->select('main_category_id')
                ->where([
                    'user_id'           => $user_id,
                    'vendor_details_id' => $vendor->id,
                    'status'            => VendorMainCategoryData::STATUS_ACTIVE,
                ])
                ->column();

            $main_categories = array_unique(array_merge($main_categories, $additional_categories));

            if (empty($main_categories)) {
                throw new BadRequestHttpException(Yii::t('app', 'No main categories associated with this vendor.'));
            }

            $subCategories = SubCategory::find()
                ->where(['main_category_id' => $main_categories, 'status' => SubCategory::STATUS_ACTIVE])
                ->andWhere([
                    'or',
                    ['vendor_details_id' => null],
                    ['vendor_details_id' => $vendor->id],
                ])
                ->all();

            $unique_sub_categories = [];

            foreach ($subCategories as $sub_category) {
                $unique_sub_categories[$sub_category->id] = $sub_category->asJson($vendor->id);
            }

            if (empty($unique_sub_categories)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'No services available for your associated categories.');
                return $this->sendJsonResponse($data);
            }

            $data['status']  = self::API_OK;
            $data['details'] = array_values($unique_sub_categories);
            $data['message'] = Yii::t('app', 'Services retrieved successfully.');
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteMainServices()
    {
        $data            = [];
        $post            = Yii::$app->request->post();
        $headers         = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth            = new AuthSettings();
        $user_id         = $auth->getAuthSession($headers);
        $sub_category_id = $post['sub_category_id'] ?? null;

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details could not be found.'));
            }

            $vendor_details_id = $vendor->id;

            // Fetch the subcategory to be marked as deleted
            $sub_category = SubCategory::findOne(['id' => $sub_category_id]);
            if (empty($sub_category)) {
                throw new NotFoundHttpException(Yii::t('app', 'The specified service type does not exist.'));
            }

            // Check if the subcategory belongs to the vendor and can be deleted
            if ($sub_category->vendor_details_id != $vendor_details_id) {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not authorized to delete this global service type.'));
            }

            // Fetch all related services
            $services = Services::findAll([
                'sub_category_id'   => $sub_category_id,
                'vendor_details_id' => $vendor_details_id,
            ]);

            // Mark services as deleted
            foreach ($services as $service) {
                $service->status = Services::STATUS_DELETE;
                $service->save(false); // Save without validation
            }

            // Mark the subcategory as deleted
            $sub_category->status = SubCategory::STATUS_DELETE;
            if (! $sub_category->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to delete the service type. Please try again later.'));
            }

            // Return success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t('app', 'Service type & services are deleted successfully.');
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (ForbiddenHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'An unexpected error occurred. Please contact support.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateServiceType()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $service_type_id  = isset($post['service_type_id']) ? $post['service_type_id'] : null;
            $main_category_id = isset($post['main_category_id']) ? $post['main_category_id'] : null;

            if (empty($service_type_id) || empty($main_category_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'main_category_id and service_type_id are required.'));
            }

            $vendor_details_id = $vendor->id;

            // Fetch service type
            $serviceType = ServiceType::find()->where(['id' => $service_type_id])->one();
            if (! $serviceType) {
                throw new BadRequestHttpException(Yii::t('app', 'Invalid service type.'));
            }

            // Check if combination already exists
            $existing = StoreServiceTypes::find()
                ->where(['store_id' => $vendor_details_id])
                ->andWhere(['service_type_id' => $service_type_id])
                ->andWhere(['main_category_id' => $main_category_id])
                ->andWhere(['type' => $serviceType->type])
                ->one();

            if (! empty($existing)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service type saved successfully.');
                $data['details'] = $existing->asJson();
                return $this->sendJsonResponse($data);
            }

            // Create new StoreServiceTypes entry
            $store_service_types                   = new StoreServiceTypes();
            $store_service_types->store_id         = $vendor_details_id;
            $store_service_types->service_type_id  = $service_type_id;
            $store_service_types->main_category_id = $main_category_id;
            $store_service_types->type             = $serviceType->type;
            $store_service_types->image            = $serviceType->image;
            $store_service_types->status           = StoreServiceTypes::STATUS_ACTIVE;

            if ($store_service_types->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service type saved successfully.');
                $data['details'] = $store_service_types->asJson();
            } else {
                throw new Exception(Yii::t('app', 'Failed to save service type.'));
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
        } catch (Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAvailableSlots()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Validate user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
            }

            // Validate required parameters
            if (empty($post['date'])) {
                throw new BadRequestHttpException(Yii::t("app", "Date is required."));
            }
            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $inputDate         = $post['date'];
            $vendor_details_id = $vendor->id;

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

            // Check if slots are available
            if (! empty($slots)) {
                $data['status']  = self::API_OK;
                $data['details'] = $slots;
            } else {
                $data['status']  = self::API_NOK;
                $data['details'] = Yii::t("app", "No slots available for the selected date.");
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

    public function actionAddOrUpdateService()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $get     = Yii::$app->request->get();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // try {
        // Ensure user is authenticated
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
        }

        // Fetch vendor details
        $vendor = VendorDetails::findOne(['user_id' => $user_id]);
        if (empty($vendor)) {
            throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
        }

        // Fetch required fields

        $services_id           = ! empty($post['id']) ? $post['id'] : null;
        $service_name          = ! empty($post['service_name']) ? $post['service_name'] : null;
        $image                 = ! empty($post['image']) ? $post['image'] : null;
        $price                 = ! empty($post['price']) ? $post['price'] : null;
        $sub_category_id       = ! empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
        $duration              = ! empty($post['duration']) ? $post['duration'] : null;
        $home_visit            = isset($post['home_visit']) ? $post['home_visit'] : null;
        $walk_in               = ! empty($post['walk_in']) ? $post['walk_in'] : null;
        $service_for           = ! empty($post['service_for']) ? $post['service_for'] : null;
        $description           = ! empty($post['description']) ? $post['description'] : null;
        $is_parent_service     = ! empty($post['is_parent_service']) ? $post['is_parent_service'] : 0;
        $store_service_type_id = ! empty($post['store_service_type_id']) ? $post['store_service_type_id'] : null;
        $is_price_range        = ! empty($post['is_price_range']) ? $post['is_price_range'] : 0;
        $from_price            = ! empty($post['from_price']) ? $post['from_price'] : 0;
        $to_price              = ! empty($post['to_price']) ? $post['to_price'] : 0;
        $multi_selection       = ! empty($post['multi_selection']) ? $post['multi_selection'] : false;
        $is_product_required = $post['is_product_required'] ?? 0;
        $product_ids = $post['product_ids'] ?? '';
        $sub_services_json = $post['sub_services_json'] ?? '';




        // Validate required fields
        if (empty($service_name)) {
            throw new BadRequestHttpException(Yii::t('app', 'Service name is required.'));
        }

        if (!empty($is_product_required)) {
            $product_ids_json_decode_parent = json_decode(stripslashes(trim($product_ids, "\"")));
        }

        if ($is_parent_service != 1) {
            if (empty($duration)) {
                throw new BadRequestHttpException(Yii::t('app', 'Duration is required.'));
            }

            if (is_null($home_visit) && is_null($walk_in)) {
                throw new BadRequestHttpException(Yii::t('app', 'Either Home Visit or Walk-in must be selected.'));
            }

            if (! empty($home_visit) && ! empty($walk_in)) {
                throw new BadRequestHttpException(Yii::t('app', 'Only one option allowed: either Home Visit or Walk-in.'));
            }

            if (empty($service_for)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service for is required.'));
            }

            if (empty($description)) {
                throw new BadRequestHttpException(Yii::t('app', 'Description is required.'));
            }
        }
        if ($is_price_range == 1) {
            // Both empty → throw error
            if (empty($from_price)) {
                throw new BadRequestHttpException(Yii::t('app', 'Either from_price  is required when price range is enabled.'));
            }

            // If both are provided → validate their relationship
            if (! empty($from_price) && ! empty($to_price) && $from_price >= $to_price) {
                throw new BadRequestHttpException(Yii::t('app', 'from_price must be less than to_price.'));
            }
        }



        // Generate unique slug
        $vendor_details_id = $vendor->id;
        $slug = User::generateUniqueSlug($service_name . $duration . $price . $service_for, $vendor_details_id);

        if (! empty($services_id)) {
            $services = Services::find()->where([
                'id' => $services_id,
            ])->one();
        }

        // If the service doesn't exist, create a new one
        if (empty($services)) {
            $services = new Services();
        }

        // Populate the service details
        $services->vendor_details_id     = $vendor_details_id;
        $services->sub_category_id       = $sub_category_id;
        $services->slug                  = $slug;
        $services->store_service_type_id = $store_service_type_id;
        $services->multi_selection       = $multi_selection;
        $services->service_name          = $service_name;
        $services->image                 = $image;
        $services->price                 = ! empty($from_price) ? $from_price : $price;
        $services->from_price            = $from_price;
        $services->to_price              = $to_price;
        $services->is_price_range        = $is_price_range;
        $services->duration              = $duration;
        $services->home_visit            = $home_visit;
        $services->walk_in               = $walk_in;
        $services->is_parent_service     = $is_parent_service;
        if (! empty($home_visit)) {
            $services->type = Services::TYPE_HOME_VISIT;
        } elseif (! empty($walk_in)) {
            $services->type = Services::TYPE_WALK_IN;
        }

        $services->description = $description;
        $services->service_for = $service_for;
        $services->status      = Services::STATUS_ADMIN_WAITING_FOR_APPROVAL;

        // Save the service details
        if ($services->save(false)) {

            if (!empty($product_ids_json_decode_parent)) {
                foreach ($product_ids_json_decode_parent as $productData) {

                    $product_services = ProductServices::findOne(['service_id' => $services->id, 'product_id' => $productData->product_id]);

                    if (empty($product_services)) {
                        $product_services = new ProductServices();
                    }

                    $product_services->service_id = $services->id;
                    $product_services->product_id = $productData->product_id;
                    $product_services->status = ProductServices::STATUS_ACTIVE;
                    $product_services->save(false);
                }
            }


            if ($services->is_parent_service == 1) {
                // ❌ Disallow sub-services when price range is used
                if (! empty($services->to_price)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Child services cannot be added when a price range is used (to_price must be empty).'));
                }


                if (empty($sub_services_json)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Sub services data is required for parent service.'));
                }

                $sub_services_json_decode = json_decode(stripslashes(trim($sub_services_json, "\"")));

                if (! is_array($sub_services_json_decode)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Invalid sub services format.'));
                }

                $servicesChildDeleteSTate = Services::find()->where([
                    'vendor_details_id' => $vendor_details_id,
                    'sub_category_id'   => $sub_category_id,
                    'parent_id'         => $services->id,
                ])->all();

                if (! empty($servicesChildDeleteSTate)) {
                    foreach ($servicesChildDeleteSTate as $servicesChildDeleteSTateData) {
                        $servicesChildDeleteSTateData->status = Services::STATUS_DELETE;
                        $servicesChildDeleteSTateData->save(false);
                    }
                }

                // return $sub_services_json_decode;

                foreach ($sub_services_json_decode as $index => $childData) {
                    // ✅ Validate required fields
                    if (empty($childData->service_name)) {
                        throw new BadRequestHttpException(Yii::t('app', "Sub service name is required at item {$index}."));
                    }

                    if (empty($childData->price)) {
                        throw new BadRequestHttpException(Yii::t('app', "Price is required at sub service {$childData->service_name}."));
                    }
                    $is_product_required_child = $childData->is_product_required_child ?? 0;
                    $child_service_product_ids_json_decode = $childData->child_service_product_ids ?? [];
                    if (! empty($is_product_required_child) && empty($child_service_product_ids_json_decode)) {
                        throw new BadRequestHttpException(Yii::t('app', "Product IDs are required for sub service {$childData->service_name}."));
                    }



                    if (! is_array($child_service_product_ids_json_decode)) {
                        throw new BadRequestHttpException(Yii::t('app', "Invalid product IDs format for sub service {$childData->service_name}."));
                    }

                    // if (!isset($childData->duration) || $childData->duration === '') {
                    //     throw new BadRequestHttpException(Yii::t('app', "Duration is required at sub service {$childData->service_name}."));
                    // }

                    // ✅ Generate slug
                    $slugChild = User::generateUniqueSlug($childData->service_name . $childData->price . ($childData->duration ?? 0) . $service_for, $vendor_details_id);

                    // ✅ Check if exists
                    $servicesChild = Services::find()->where([
                        'vendor_details_id' => $vendor_details_id,
                        'sub_category_id'   => $sub_category_id,
                        'slug'              => $slugChild,
                        'parent_id'         => $services->id,
                    ])->one();

                    if (empty($servicesChild)) {
                        $servicesChild = new Services();
                    }

                    // ✅ Populate child service
                    $servicesChild->vendor_details_id     = $vendor_details_id;
                    $servicesChild->sub_category_id       = $sub_category_id;
                    $servicesChild->slug                  = $slugChild;
                    $servicesChild->store_service_type_id = $store_service_type_id;

                    $servicesChild->service_name = $childData->service_name;
                    $servicesChild->price        = $childData->price;

                    $servicesChild->image       = $image;
                    $servicesChild->duration    = $duration ?? '';
                    $servicesChild->home_visit  = $home_visit;
                    $servicesChild->walk_in     = $walk_in;
                    $servicesChild->parent_id   = $services->id;
                    $servicesChild->type        = $services->type;
                    $servicesChild->description = $childData->description ?? null;
                    $servicesChild->service_for = $service_for;
                    $servicesChild->status      = Services::STATUS_ACTIVE;
                    $servicesChild->save(false);
                    if (!empty($child_service_product_ids_json_decode)) {
                        foreach ($child_service_product_ids_json_decode as $product_id_child) {
                            $product_services = ProductServices::findOne(['service_id' => $servicesChild->id, 'product_id' => $product_id_child->product_id_child]);
                            if (empty($product_services)) {
                                $product_services = new ProductServices();
                            }
                            $product_services->service_id = $servicesChild->id;
                            $product_services->product_id = $product_id_child->product_id_child;
                            $product_services->status = ProductServices::STATUS_ACTIVE;
                            $product_services->save(false);
                        }
                    }
                }
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t('app', 'Service added or updated successfully.');
            $data['details'] = $services->asJson();
        } else {
            throw new Exception(Yii::t('app', 'Failed to save the service.'));
        }
        // } catch (UnauthorizedHttpException $e) {
        //     $data['status']  = self::API_NOK;
        //     $data['message'] = $e->getMessage();
        // } catch (NotFoundHttpException $e) {
        //     $data['status']  = self::API_NOK;
        //     $data['message'] = $e->getMessage();
        // } catch (BadRequestHttpException $e) {
        //     $data['status']  = self::API_NOK;
        //     $data['message'] = $e->getMessage();
        // } catch (Exception $e) {
        //     $data['status']  = self::API_NOK;
        //     $data['message'] = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        // }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateService()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $get     = Yii::$app->request->get();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch required fields

            $services_id       = ! empty($post['services_id']) ? $post['services_id'] : null;
            $service_name      = ! empty($post['service_name']) ? $post['service_name'] : null;
            $image             = ! empty($post['image']) ? $post['image'] : null;
            $price             = ! empty($post['price']) ? $post['price'] : null;
            $sub_category_id   = ! empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
            $duration          = ! empty($post['duration']) ? $post['duration'] : null;
            $home_visit        = isset($post['home_visit']) ? $post['home_visit'] : null;
            $walk_in           = ! empty($post['walk_in']) ? $post['walk_in'] : null;
            $service_for       = ! empty($post['service_for']) ? $post['service_for'] : null;
            $description       = ! empty($post['description']) ? $post['description'] : null;
            $is_parent_service = ! empty($post['is_parent_service']) ? $post['is_parent_service'] : '';

            // Validate required fields
            if (empty($service_name)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service name is required.'));
            }

            if (empty($services_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'services_id is required.'));
            }

            if ($is_parent_service != 1) {
                if (empty($price)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Price is required.'));
                }
                if (empty($duration)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Duration is required.'));
                }
                if (is_null($home_visit) && is_null($walk_in)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Either home visit or walk-in must be specified.'));
                }
                if (empty($service_for)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Service for is required.'));
                }
                if (empty($description)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Description is required.'));
                }
            }

            // Generate unique slug
            $vendor_details_id = $vendor->id;
            $slug              = User::generateUniqueSlug($service_name . $duration . $price . $service_for, $vendor_details_id);

            if (! empty($services_id)) {
                $services = Services::find()->where([
                    'id' => $services_id,
                ])->one();
            } else {
                // Check if the service already exists
                $services = Services::find()->where([
                    'vendor_details_id' => $vendor_details_id,
                    'sub_category_id'   => $sub_category_id,
                    'slug'              => $slug,
                ])->one();
            }

            // If the service doesn't exist, create a new one
            if (empty($services)) {
                $services = new Services();
            }

            // Populate the service details
            $services->vendor_details_id = $vendor_details_id;
            $services->sub_category_id   = $sub_category_id;
            $services->slug              = $slug;
            $services->service_name      = $service_name;
            $services->image             = $image;
            $services->price             = $price;
            $services->duration          = $duration;
            $services->home_visit        = $home_visit;
            $services->walk_in           = $walk_in;
            $services->is_parent_service = $is_parent_service;
            if (! empty($home_visit) && $walk_in) {
                $services->type = Services::TYPE_HOME_VISIT;
            } else {
                $services->type = Services::TYPE_WALK_IN;
            }
            $services->description = $description;
            $services->service_for = $service_for;
            $services->status      = Services::STATUS_ACTIVE;

            // Save the service details
            if ($services->save(false)) {

                if ($is_parent_service == 1) {
                    $sub_services_json = $post['sub_services_json'] ?? '';

                    if (empty($sub_services_json)) {
                        throw new BadRequestHttpException(Yii::t('app', 'Sub services data is required for parent service.'));
                    }

                    $sub_services_json_decode = json_decode(stripslashes(trim($sub_services_json, "\"")));

                    if (! is_array($sub_services_json_decode)) {
                        throw new BadRequestHttpException(Yii::t('app', 'Invalid sub services format.'));
                    }

                    foreach ($sub_services_json_decode as $index => $childData) {
                        // ✅ Validate required fields
                        if (empty($childData->service_name)) {
                            throw new BadRequestHttpException(Yii::t('app', "Sub service name is required at item {$index}."));
                        }

                        if (! isset($childData->price) || $childData->price === '') {
                            throw new BadRequestHttpException(Yii::t('app', "Price is required at sub service {$childData->service_name}."));
                        }

                        if (! isset($childData->duration) || $childData->duration === '') {
                            throw new BadRequestHttpException(Yii::t('app', "Duration is required at sub service {$childData->service_name}."));
                        }

                        if (empty($childData->description)) {
                            throw new BadRequestHttpException(Yii::t('app', "Description is required at sub service {$childData->service_name}."));
                        }

                        // ✅ Generate slug
                        $slugClild = User::generateUniqueSlug($childData->service_name . $childData->price . $childData->duration . $service_for, $vendor_details_id);

                        // ✅ Check if exists
                        $servicesChild = Services::find()->where([
                            'vendor_details_id' => $vendor_details_id,
                            'sub_category_id'   => $sub_category_id,
                            'slug'              => $slugClild,
                            'parent_id'         => $services->id,
                        ])->one();

                        if (empty($servicesChild)) {
                            $servicesChild = new Services();
                        }

                        // ✅ Populate child service
                        $servicesChild->vendor_details_id = $vendor_details_id;
                        $servicesChild->sub_category_id   = $sub_category_id;
                        $servicesChild->slug              = $slugClild;
                        $servicesChild->service_name      = $childData->service_name;
                        $servicesChild->image             = $image;
                        $servicesChild->price             = $childData->price;
                        $servicesChild->duration          = $childData->duration ?? '';
                        $servicesChild->home_visit        = $home_visit;
                        $servicesChild->walk_in           = $walk_in;
                        $servicesChild->parent_id         = $services->id;
                        $servicesChild->type              = (! empty($home_visit) && $walk_in) ? Services::TYPE_HOME_VISIT : Services::TYPE_WALK_IN;
                        $servicesChild->description       = $childData->description;
                        $servicesChild->service_for       = $service_for;
                        $servicesChild->status            = Services::STATUS_ACTIVE;
                        $servicesChild->save(false);
                    }
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service added or updated successfully.');
                $data['details'] = $services->asJson();
            } else {
                throw new Exception(Yii::t('app', 'Failed to save the service.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateChildServices()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $get     = Yii::$app->request->get();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch required fields

            $services_id  = ! empty($post['services_id']) ? $post['services_id'] : null;
            $service_name = ! empty($post['service_name']) ? $post['service_name'] : null;

            $price      = ! empty($post['price']) ? $post['price'] : null;
            $duration   = ! empty($post['duration']) ? $post['duration'] : null;
            $home_visit = isset($post['home_visit']) ? $post['home_visit'] : null;
            $walk_in    = ! empty($post['walk_in']) ? $post['walk_in'] : null;

            // Validate required fields
            if (empty($service_name)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service name is required.'));
            }

            if (empty($services_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'services id name is required.'));
            }

            if (empty($price)) {
                throw new BadRequestHttpException(Yii::t('app', 'Price is required.'));
            }
            if (empty($duration)) {
                throw new BadRequestHttpException(Yii::t('app', 'Duration is required.'));
            }
            if (is_null($home_visit) && is_null($walk_in)) {
                throw new BadRequestHttpException(Yii::t('app', 'Either home visit or walk-in must be specified.'));
            }

            // Generate unique slug
            $vendor_details_id = $vendor->id;
            $slug              = User::generateUniqueSlug($service_name, $vendor_details_id);

            $services = Services::find()->where([
                'id' => $services_id,
            ])->one();

            // If the service doesn't exist, create a new one
            if (empty($services)) {

                $data['status']  = self::API_NOK;
                $data['message'] = 'services is empty';
                return $this->sendJsonResponse($data);
            }

            // Populate the service details
            $services->vendor_details_id = $vendor_details_id;
            $services->slug              = $slug;
            $services->sub_category_id   = $services->sub_category_id;
            $services->service_name      = $service_name;
            $services->price             = $price;
            $services->duration          = $duration;
            $services->home_visit        = $home_visit;
            $services->walk_in           = $walk_in;
            if (! empty($home_visit) && $walk_in) {
                $services->type = Services::TYPE_HOME_VISIT;
            } else {
                $services->type = Services::TYPE_WALK_IN;
            }

            $services->status = Services::STATUS_ACTIVE;

            // Save the service details
            if ($services->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service added or updated successfully.');
                $data['details'] = $services->asJson();
            } else {
                throw new Exception(Yii::t('app', 'Failed to save the service.'));
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
        } catch (Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionServicesList()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch required fields
            $sub_category_id = ! empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
            $home_visit      = ! empty($post['home_visit']) ? $post['home_visit'] : null;
            $walk_in         = ! empty($post['walk_in']) ? $post['walk_in'] : null;

            // Validate required fields
            if (empty($sub_category_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'Subcategory ID is required.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and subcategory
            $query = Services::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['sub_category_id' => $sub_category_id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE, Services::STATUS_ADMIN_WAITING_FOR_APPROVAL]])
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ]);

            // Apply filters only if values are provided
            if ($home_visit !== null) {
                $query->andWhere(['home_visit' => 1]);
            }

            if ($walk_in !== null) {
                $query->andWhere(['walk_in' => 1]);
            }

            $services = $query->all();
            // Check if services exist
            if (empty($services)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No services found for the selected subcategory.');
            } else {
                // Format service data
                $list = [];
                foreach ($services as $service) {
                    $list[] = $service->asJson();
                }

                // Prepare success response
                $data['status']   = self::API_OK;
                $data['message']  = Yii::t('app', 'Services retrieved successfully.');
                $data['services'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionServicesListForCombo()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and subcategory
            $services = Services::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE]]) // include both
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ])
                ->all();

            // Check if services exist
            if (empty($services)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No services found for the selected subcategory.');
            } else {
                // Format service data
                $list = [];
                foreach ($services as $service) {
                    $list[] = $service->asJsonForCombo();
                }

                // Prepare success response
                $data['status']   = self::API_OK;
                $data['message']  = Yii::t('app', 'Services retrieved successfully.');
                $data['services'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteService()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized. Please login to continue.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found. Please complete your vendor profile.'));
            }

            // Fetch required fields
            $services_id = ! empty($post['services_id']) ? $post['services_id'] : null;

            // Validate required fields
            if (empty($services_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service ID is required.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and service ID
            $services = Services::findOne(['id' => $services_id, 'vendor_details_id' => $vendor_details_id]);

            if (empty($services)) {
                throw new NotFoundHttpException(Yii::t('app', 'Service not found or you do not have permission to delete this service.'));
            }

            $services_child = Services::find()->where(['parent_id' => $services->id])->all();
            if (! empty($services_child)) {
                foreach ($services_child as $services_child_data) {
                    $services_child_data->status = Services::STATUS_DELETE;
                    $services_child_data->save(false);
                }
            }

            // Mark the service as deleted
            $services->status = Services::STATUS_DELETE;
            if ($services->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service deleted successfully.');
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to delete the service. Please try again later.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionChangeServiceStatus()
    {

        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized. Please login to continue.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found. Please complete your vendor profile.'));
            }

            // Fetch required fields
            $services_id = ! empty($post['services_id']) ? $post['services_id'] : null;
            $status      = ! empty($post['status']) ? $post['status'] : null;

            // Validate required fields
            if (empty($services_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service ID and status is required.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and service ID
            $services = Services::findOne(['id' => $services_id, 'vendor_details_id' => $vendor_details_id]);

            if (empty($services)) {
                throw new NotFoundHttpException(Yii::t('app', 'Service not found or you do not have permission to delete this service.'));
            }

            // Mark the service as deleted
            $services->status = $status;
            if ($services->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $services;
                $data['message'] = Yii::t('app', 'Service status changed successfully.');
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to delete the service. Please try again later.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
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

        $page         = ! empty($post['page']) ? (int) $post['page'] : 0;
        $service_type = $post['service_type'] ?? '';
        $search       = $post['search'] ?? '';
        $status       = $post['status'] ?? Orders::STATUS_NEW_ORDER;
        $start_date   = ! empty($post['start_date']) ? $post['start_date'] : date('Y-m-d');

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            $start_date = date('Y-m-d', strtotime($start_date));

            $query = Orders::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere([
                    'or',
                    ['payment_status' => Orders::PAYMENT_DONE],
                    ['is_next_visit' => 1]
                ]);


            if (! empty($start_date)) {
                $query->andWhere(['schedule_date' => $start_date]);
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
                    'current_page' => $pagination->page + 1, // 1-based index
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
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateStaff()
    {
        $data     = [];
        $post     = Yii::$app->request->post();
        $get      = Yii::$app->request->get();
        $staff_id = ! empty($get['staff_id']) ? $get['staff_id'] : '';
        $headers  = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Authentication check
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Vendor details check
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;
            $mobile_no         = $post['mobile_no'] ?? '';
            $email             = $post['email'] ?? '';
            $full_name         = $post['full_name'] ?? '';
            $gender            = $post['gender'] ?? '';
            $dob               = $post['dob'] ?? '';
            $role              = User::ROLE_STAFF; // Default role for staff
            $profile_image     = $post['profile_image'] ?? '';
            $experience        = $post['experience'] ?? '';
            $specialization    = $post['specialization'] ?? '';
            $aadhaar_number    = $post['aadhaar_number'] ?? '';

            if (empty($mobile_no) || empty($full_name) || empty($role)) {
                throw new \Exception(Yii::t("app", "Full name, mobile number, and role are required."));
            }

            // Format DOB
            $dobFormatted = ! empty($dob) ? date('Y-m-d', strtotime($dob)) : null;

            $user = null;

            // If staff_id is provided, update existing
            if (! empty($staff_id)) {
                $user = User::findOne(['id' => $staff_id]);
                if (! $user) {
                    throw new NotFoundHttpException(Yii::t("app", "No staff found with the given ID."));
                }
            } else {
                // Check if a staff already exists with this mobile
                $existingUser = User::find()
                    ->where(['contact_no' => $mobile_no])
                    ->andWhere(['IN', 'user_role', [User::ROLE_STAFF, User::ROLE_HOME_VISITOR]])
                    ->one();

                if ($existingUser) {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "Staff already exists with this mobile number.");
                    return $this->sendJsonResponse($data);
                }

                // Create new user
                $user = new User();
            }

            // Setup user
            $username            = $mobile_no . '@' . $role . '.com';
            $user->username      = $username;
            $user->email         = $email;
            $user->first_name    = $full_name;
            $user->contact_no    = $mobile_no;
            $user->date_of_birth = $dobFormatted;
            $user->gender        = $gender;
            $user->user_role     = $role;

            // For new user, assign UID
            if ($user->isNewRecord) {
                $user->unique_user_id = User::generateUniqueUserId('H');
            }

            if (! $user->save(false)) {
                $errors = json_encode($user->getErrors());
                throw new \Exception(Yii::t("app", "Failed to save user details. Errors: {errors}", ['errors' => $errors]));
            }

            // Find or create staff
            $staff = Staff::findOne(['user_id' => $user->id]);
            if (empty($staff)) {
                $staff = new Staff();
            }

            $staff->vendor_details_id = $vendor_details_id;
            $staff->user_id           = $user->id;
            $staff->mobile_no         = $mobile_no;
            $staff->email             = $email;
            $staff->full_name         = $full_name;
            $staff->gender            = $gender;
            $staff->dob               = $dobFormatted;
            $staff->profile_image     = $profile_image;
            $staff->role              = $role;
            $staff->experience        = $experience;
            $staff->aadhaar_number    = $aadhaar_number;
            $staff->specialization    = $specialization;
            $staff->status            = Staff::STATUS_ACTIVE;
            $staff->current_status    = Staff::CURRENT_STATUS_IDLE;

            if (! $staff->save(false)) {
                $errors = json_encode($staff->getErrors());
                throw new \Exception(Yii::t("app", "Failed to save staff details. Errors: {errors}", ['errors' => $errors]));
            }

            $transaction->commit();

            $data['status']  = self::API_OK;
            $data['details'] = $staff->asJson();
            $data['message'] = ! empty($staff_id)
                ? Yii::t("app", "Staff details updated successfully.")
                : Yii::t("app", "Staff added successfully.");
        } catch (UnauthorizedHttpException $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateStaff()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if the user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            // Validate input data
            $staff_id       = $post['staff_id'] ?? null;
            $mobile_no      = $post['mobile_no'] ?? null;
            $email          = $post['email'] ?? null;
            $full_name      = $post['full_name'] ?? null;
            $gender         = $post['gender'] ?? null;
            $dob            = $post['dob'] ?? null;
            $role           = User::ROLE_STAFF; // Default role for staff
            $profile_image  = $post['profile_image'] ?? '';
            $experience     = $post['experience'] ?? '';
            $specialization = $post['specialization'] ?? '';

            if (! $staff_id || ! $mobile_no || ! $email || ! $full_name || ! $gender || ! $dob || ! $role) {
                throw new BadRequestHttpException(Yii::t("app", "All required fields (staff_id, mobile_no, email, full_name, gender, dob, role) must be provided."));
            }

            // Format the date of birth
            $dobFormatted = date('Y-m-d', strtotime($dob));

            // Check if the mobile number or email is already in use by another staff
            $existingStaffWithMobile = Staff::find()
                ->where(['mobile_no' => $mobile_no])
                ->andWhere(['!=', 'id', $staff_id]) // Exclude the current staff ID
                ->one();

            if (! empty($existingStaffWithMobile)) {
                throw new BadRequestHttpException(Yii::t("app", "The mobile number is already in use by another staff member."));
            }

            $existingStaffWithEmail = Staff::find()
                ->where(['email' => $email])
                ->andWhere(['!=', 'id', $staff_id]) // Exclude the current staff ID
                ->one();

            if (! empty($existingStaffWithEmail)) {
                throw new BadRequestHttpException(Yii::t("app", "The email address is already in use by another staff member."));
            }

            // Find existing staff by ID
            $staff = Staff::findOne(['id' => $staff_id]);
            if (empty($staff)) {
                throw new NotFoundHttpException(Yii::t("app", "Staff not found."));
            }

            // Find and update the corresponding user account
            $user                = User::findOne(['id' => $staff->user_id]);
            $username            = $mobile_no . '@' . $role . '.com';
            $user->username      = $username;
            $user->email         = $email;
            $user->first_name    = $full_name;
            $user->contact_no    = $mobile_no;
            $user->date_of_birth = $dobFormatted;
            $user->gender        = $gender;
            $user->user_role     = $role;

            if (! $user->save(false)) {
                throw new \Exception(Yii::t("app", "Failed to update user details. Please check the input data."));
            }

            // Update staff details
            $staff->vendor_details_id = $vendor_details_id;
            $staff->mobile_no         = $mobile_no;
            $staff->email             = $email;
            $staff->full_name         = $full_name;
            $staff->gender            = $gender;
            $staff->dob               = $dobFormatted;
            $staff->profile_image     = $profile_image;
            $staff->role              = $role;
            $staff->experience        = $experience;
            $staff->specialization    = $specialization;
            $staff->status            = Staff::STATUS_ACTIVE;
            $staff->current_status    = Staff::CURRENT_STATUS_IDLE;

            // Save staff details and handle any validation errors
            if (! $staff->save()) {
                $errors = json_encode($staff->getErrors());
                throw new \Exception(Yii::t("app", "Failed to save staff details. Errors: {errors}", ['errors' => $errors]));
            }

            // Return success response
            $data['status']  = self::API_OK;
            $data['details'] = $staff->asJson();
            $data['message'] = Yii::t("app", "Staff details updated successfully.");
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateStaffStatus()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if the user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;
            $staff_id          = ! empty($post['staff_id']) ? $post['staff_id'] : null;
            $status            = ! empty($post['status']) ? $post['status'] : null;

            // Check if staff ID and status are provided
            if (! $staff_id || ! $status) {
                throw new BadRequestHttpException(Yii::t("app", "Staff ID and status are required."));
            }

            // Validate the status value
            if (! in_array($status, [Staff::STATUS_ACTIVE, Staff::STATUS_INACTIVE])) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid status value. Status must be 1 (active) or 2 (inactive)."));
            }

            // Find the staff member by ID
            $staff = Staff::findOne(['id' => $staff_id, 'vendor_details_id' => $vendor_details_id]);
            if (! $staff) {
                throw new NotFoundHttpException(Yii::t("app", "Staff member not found with the given ID."));
            }

            // Update the staff's status
            $staff->status = $status;

            // Save staff details and check for errors
            if (! $staff->save(false)) {
                $errors = json_encode($staff->getErrors());
                throw new \Exception(Yii::t("app", "Failed to save staff details. Errors: {errors}", ['errors' => $errors]));
            }

            // Success response
            $data['status']  = self::API_OK;
            $data['details'] = $staff->asJson();
            $data['message'] = Yii::t("app", "Staff status updated successfully.");
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error("Error updating staff status: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
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

    public function actionActiveStaffList()
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
            $staffList = Staff::find()->where(['vendor_details_id' => $vendor_details_id])->andwhere(['status' => Staff::STATUS_ACTIVE])->all();

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

    public function actionBusinessProfile()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }

            // Only select necessary fields for performance
            $VendorDetails = VendorDetails::find()

                ->where(['user_id' => $user_id])
                ->limit(1)
                ->one();

            if (! $VendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            // Handle update logic
            $about_store = Yii::$app->request->post('about_store');
            if (! empty($about_store)) {
                $VendorDetails->description = $about_store;

                // Save only the changed attribute
                if (! $VendorDetails->save(false, ['description'])) {
                    throw new \Exception(Yii::t("app", "Failed to update business profile."));
                }

                $data['message'] = Yii::t("app", "Business profile updated successfully.");
            } else {
                $data['message'] = Yii::t("app", "Business profile retrieved successfully.");
            }

            // Final response
            $data['status']  = self::API_OK;
            $data['details'] = $VendorDetails->asJsonVendor();
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteBusinessImage()
    {
        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $image_id = Yii::$app->request->post('image_id');

        try {
            // Validate image ID
            if (empty($image_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "Image ID is required."));
            }

            // Find the business image record
            $businessImage = BusinessImages::findOne(['id' => $image_id, 'create_user_id' => $user_id]);
            if (! $businessImage) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Image not found or access denied."));
            }

            // Delete the record from the database
            if (! $businessImage->delete()) {
                throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to delete the image record."));
            }

            // Prepare success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Image deleted successfully.");
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddReels()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Validate input data
            if (empty($post['video']) || empty($post['thumbnail']) || empty($post['title']) || empty($post['description'])) {
                throw new \yii\base\InvalidParamException(Yii::t("app", "All fields (video, thumbnail, title, description) are required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $reel_tags                = ! empty($post['reel_tags']) ? $post['reel_tags'] : '';
            $vendor_details_id        = $VendorDetails->id;
            $reels                    = new Reels();
            $reels->vendor_details_id = $vendor_details_id;
            $reels->video             = $post['video'];
            $reels->thumbnail         = $post['thumbnail'];
            $reels->title             = $post['title'];
            $reels->description       = $post['description'];
            $reels->status            = Reels::STATUS_ACTIVE;

            if (! $reels->save()) {
                throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to save reel. Please try again later."));
            }

            if (! empty($reel_tags)) {
                $reel_tags_arr = explode(',', $reel_tags);
                if (! empty($reel_tags_arr)) {
                    foreach ($reel_tags_arr as $reel_tag) {
                        $reel_tags          = new ReelTags();
                        $reel_tags->reel_id = $reels->id;
                        $reel_tags->tag     = $reel_tag;
                        $reel_tags->status  = ReelTags::STATUS_ACTIVE;
                        $reel_tags->save(false);
                    }
                }
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel added successfully.");
            $data['details'] = $reels->asJson($user_id);
        } catch (\yii\base\InvalidParamException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateReels()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Validate user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate input data
            if (empty($post['video']) || empty($post['thumbnail']) || empty($post['title']) || empty($post['description'])) {
                throw new BadRequestHttpException(Yii::t("app", "All fields (video, thumbnail, title, description) are required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $vendor_details_id = $VendorDetails->id;

            // Validate reel ID
            $id = $post['id'] ?? null;
            if (empty($id)) {
                throw new BadRequestHttpException(Yii::t("app", "Reel ID is required."));
            }

            // Find the reel by ID
            $reels = Reels::findOne(['id' => $id]);
            if (empty($reels)) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found."));
            }

            // Update reel details
            $reels->vendor_details_id = $vendor_details_id;
            $reels->video             = $post['video'];
            $reels->thumbnail         = $post['thumbnail'];
            $reels->title             = $post['title'];
            $reels->description       = $post['description'];
            $reels->status            = Reels::STATUS_ACTIVE;

            if (! $reels->save()) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save reel. Please try again later."));
            }

            // Handle reel tags if provided
            $reel_tags = $post['reel_tags'] ?? '';
            if (! empty($reel_tags)) {
                $reel_tags_arr = explode(',', $reel_tags);
                if (! empty($reel_tags_arr)) {
                    foreach ($reel_tags_arr as $reel_tag) {
                        $reel_tag_model          = new ReelTags();
                        $reel_tag_model->reel_id = $reels->id;
                        $reel_tag_model->tag     = $reel_tag;
                        $reel_tag_model->status  = ReelTags::STATUS_ACTIVE;
                        if (! $reel_tag_model->save(false)) {
                            throw new ServerErrorHttpException(Yii::t("app", "Failed to save reel tags. Please try again later."));
                        }
                    }
                }
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel updated successfully.");
            $data['details'] = $reels->asJson();
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionReelsList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();
        $page    = ! empty($post['page']) ? (int) $post['page'] : 1;
        $search  = ! empty($post['search']) ? trim($post['search']) : '';
        $status  = ! empty($post['status']) ? trim($post['status']) : '';

        $pageSize = 10; // Set the number of items per page

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            $query = Reels::find()
                ->where(['vendor_details_id' => $vendor_details_id]);
            if (! empty($status)) {
                $query->andWhere(['status' => $status]);
            } else {
                $query->andWhere(['in', 'status', [Reels::STATUS_ACTIVE, Reels::STATUS_INACTIVE]]);
            }
            if (! empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'LOWER(title)', strtolower($search)],
                    ['like', 'LOWER(description)', strtolower($search)],
                ]);
            }

            // Create ActiveDataProvider for reels
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page - 1,
                ],
                'sort'       => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);

            // Fetch and format the reels
            $list = [];
            foreach ($dataProvider->getModels() as $reel) {
                $list[] = $reel->asJson();
            }

            // Check if there are reels to return
            if (! empty($list)) {
                $data['status']     = self::API_OK;
                $data['details']    = $list;
                $data['pagination'] = [
                    'total_count'  => $dataProvider->getTotalCount(),
                    'page_count'   => $dataProvider->getPagination()->getPageCount(),
                    'current_page' => $dataProvider->getPagination()->getPage() + 1,
                    'per_page'     => $dataProvider->getPagination()->pageSize,
                ];
            } else {
                throw new NotFoundHttpException(Yii::t("app", "No reels found for the given criteria."));
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

    public function actionSubscriptionsList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            // Query for active subscriptions
            $subscriptions = Subscriptions::find()->where(['status' => Subscriptions::STATUS_ACTIVE])->all();

            // Prepare the list of active subscriptions
            $subscriptionList = [];
            foreach ($subscriptions as $subscription) {
                $subscriptionList[] = $subscription->asJson($vendorDetails->id); // Assuming the asJson() method exists in Subscriptions model
            }

            // Check if subscriptions are found
            if (! empty($subscriptionList)) {
                $data['status']  = self::API_OK;
                $data['details'] = $subscriptionList;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No active subscriptions found.");
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

    public function actionActiveSubscription()
    {
        $data            = [];
        $post            = Yii::$app->request->post();
        $headers         = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth            = new AuthSettings();
        $user_id         = $auth->getAuthSession($headers);
        $subscription_id = $post['subscription_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Check if subscription_id is provided
            if (empty($subscription_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Subscription ID is required."));
            }

            // Find the vendor's shop details
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            // Check if an existing subscription exists regardless of status
            $existingSubscription = VendorSubscriptions::find()
                ->where(['vendor_details_id' => $vendorDetails->id])
                ->one();

            // If an existing subscription is found
            if (! empty($existingSubscription)) {
                if ($existingSubscription->status == VendorSubscriptions::STATUS_ACTIVE && $existingSubscription->end_date >= date('Y-m-d')) {
                    // If the subscription is active and not expired, show an error
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "An active subscription already exists for this vendor.");
                } else {
                    // Update the existing subscription if it is inactive or expired
                    $subscription = Subscriptions::find()
                        ->where(['id' => $subscription_id, 'status' => Subscriptions::STATUS_ACTIVE])
                        ->one();

                    if (empty($subscription)) {
                        throw new NotFoundHttpException(Yii::t("app", "The selected subscription is not available or inactive."));
                    }

                    // Calculate new start and end dates
                    $start_date = date('Y-m-d');
                    $end_date   = date('Y-m-d', strtotime("+$subscription->validity_in_days days"));

                    // Update the existing subscription
                    $existingSubscription->subscription_id = $subscription->id;
                    $existingSubscription->total_w_tax     = ! empty($subscription->offer_price) ? $subscription->offer_price : $subscription->price;
                    $existingSubscription->start_date      = $start_date;
                    $existingSubscription->end_date        = $end_date;
                    $existingSubscription->status          = VendorSubscriptions::STATUS_PENDING;
                    $existingSubscription->payment_status  = VendorSubscriptions::PAYMENT_STATUS_PENDING;

                    if ($existingSubscription->save(false)) {

                        $Razorpay     = Razorpay::createAnOrderVendorSubscription($existingSubscription->id, $existingSubscription->total_w_tax);
                        $razorpay_res = json_decode($Razorpay);
                        if (empty($razorpay_res->error)) {
                            $existingSubscription->razorpay_order_id = $razorpay_res->id;
                            $existingSubscription->save(false);
                            $data['status']               = self::API_OK;
                            $data['message']              = Yii::t("app", "Subscription updated and activated successfully.");
                            $data['subscription_details'] = $existingSubscription->asJson();
                            $data['razorpay_res']         = $razorpay_res;
                        } else {
                            $data['status'] = self::API_NOK;
                            $data['error']  = ! empty($razorpay_res->error->description) ? $razorpay_res->error->description : 'Invalid request';
                        }
                    } else {
                        throw new ServerErrorHttpException(Yii::t("app", "Failed to update the subscription. Please try again."));
                    }
                }
            } else {
                // No existing subscription found, create a new one
                $subscription = Subscriptions::find()
                    ->where(['id' => $subscription_id, 'status' => Subscriptions::STATUS_ACTIVE])
                    ->one();

                if (empty($subscription)) {
                    throw new NotFoundHttpException(Yii::t("app", "The selected subscription is not available or inactive."));
                }

                // Calculate new start and end dates
                $start_date = date('Y-m-d');
                $end_date   = date('Y-m-d', strtotime("+$subscription->validity_in_days days"));

                // Create a new subscription for the vendor
                $vendor_subscription                    = new VendorSubscriptions();
                $vendor_subscription->vendor_details_id = $vendorDetails->id;
                $vendor_subscription->total_w_tax       = ! empty($subscription->offer_price) ? $subscription->offer_price : $subscription->price;
                $vendor_subscription->subscription_id   = $subscription->id;
                $vendor_subscription->start_date        = $start_date;
                $vendor_subscription->end_date          = $end_date;
                $vendor_subscription->status            = VendorSubscriptions::STATUS_PENDING;
                $vendor_subscription->payment_status    = VendorSubscriptions::PAYMENT_STATUS_PENDING;
                $Razorpay                               = Razorpay::createAnOrderVendorSubscription($vendor_subscription->id, $vendor_subscription->total_w_tax);
                $razorpay_res                           = json_decode($Razorpay);
                if (empty($razorpay_res->error)) {
                    $vendor_subscription->razorpay_order_id = $razorpay_res->id;
                    $vendor_subscription->save(false);

                    $data['status']               = self::API_OK;
                    $data['message']              = Yii::t("app", "Subscription activated successfully.");
                    $data['subscription_details'] = $vendor_subscription->asJson();
                    $data['razorpay_res']         = $razorpay_res;
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = ! empty($razorpay_res->error->description) ? $razorpay_res->error->description : 'Invalid request';
                }
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
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAcceptOrReject()
    {
        $data     = [];
        $post     = Yii::$app->request->post();
        $headers  = isset(Yii::$app->request->headers['auth_code']) ? Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);
        $order_id = $post['order_id'] ?? null;

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate order_id and status in the request
            if (empty($order_id) || empty($post['status'])) {
                throw new BadRequestHttpException(Yii::t("app", "Order ID and status are required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::find()->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])->one();
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No active shop found for this user."));
            }

            // Find the order in the shop  
            $order = Orders::find()
                ->where(['vendor_details_id' => $VendorDetails['id'], 'id' => $order_id, 'status' => Orders::STATUS_NEW_ORDER])
                ->one();

            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t("app", "No active order found for this shop."));
            }

            // Validate the status being passed
            if ($post['status'] != Orders::STATUS_ACCEPTED && $post['status'] != Orders::STATUS_CANCELLED_BY_OWNER) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid status passed."));
            }

            // Set cancellation reason if the order is being cancelled
            $cancel_reason      = ! empty($post['cancel_reason']) ? $post['cancel_reason'] : '';
            $cancel_description = ! empty($post['cancel_description']) ? $post['cancel_description'] : '';
            if ($post['status'] == Orders::STATUS_CANCELLED_BY_OWNER) {
                $order->cancel_reason      = $cancel_reason;
                $order->cancel_description = $cancel_description;
            }

            // Update the order status
            $order->status = $post['status'];

            if ($order->save(false)) {

                if ($order->status == Orders::STATUS_CANCELLED_BY_OWNER && ($order->payment_type == Orders::TYPE_ONLINE || $order->payment_type == Orders::TYPE_WALLET)) {
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

                    if (! $wallet->save(false)) {
                        throw new ServerErrorHttpException(Yii::t("app", "Failed to process wallet refund."));
                    }
                }

                $otp = $order->otp;

                $title = ($order->status == Orders::STATUS_ACCEPTED) ? Yii::t("app", "Your Order Update") : Yii::t("app", "Your Order Cancelled");
                $body  = ($order->status == Orders::STATUS_ACCEPTED) ? Yii::t("app", "Your order (#{$order_id}) has been accepted by the shop.Use otp {$otp} to verify and start the service.") : Yii::t("app", "Your order (#{$order_id}) has been cancelled by the shop.");

                // Push notification to the user
                Yii::$app->notification->PushNotification(
                    $order_id,
                    $order->user_id,
                    $title,
                    $body,
                    'redirect' // Order type based on service type
                );




                WhatsApp::sendTemplate($order->user->contact_no, 'estetica_order_accepted', [
                    'param_1'   => $order->vendorDetails->business_name,
                    'param_2'   => '' . $order->getOrderServicesAsString(),
                    'param_3'   => $order->schedule_time,
                    'param_4'   => $order->vendorDetails->address,
                ]);

                // Log the order status change  
                $orderStatus           = new OrderStatus();
                $orderStatus->order_id = $order_id;
                $orderStatus->status   = $order->status;
                $orderStatus->remarks  = Yii::t("app", "Order status updated to ") . $order->getStateOptionsBadges();
                $orderStatus->save(false);

                // Return success response 
                $data['status']       = self::API_OK;
                $data['order_status'] = $order->status;
                $data['message']      = Yii::t("app", "Order successfully updated.");
            } else {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update the order."));
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
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

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












            $scheduleDate = $order->schedule_date ?? null;
            $scheduleTime = $order->schedule_time ?? null;

            if (!empty($scheduleDate) || !empty($scheduleTime)) {
                // If date missing but time provided, use order date if available
                if (empty($scheduleDate) && !empty($scheduleTime) && !empty($order->schedule_date)) {
                    $scheduleDate = $order->schedule_date;
                }

                // If we still don't have a date, we cannot validate — return error
                if (empty($scheduleDate)) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Schedule date is required to validate schedule time."),
                    ]);
                }

                // Normalize date and time
                $dateStr = trim($scheduleDate);
                $timeStr = trim($scheduleTime ?? '00:00');

                // Use Asia/Kolkata timezone
                $tz = new \DateTimeZone('Asia/Kolkata');

                // Try multiple time formats for robust parsing
                $parsedTime = false;
                $timeFormats = ['h:i A', 'H:i', 'g:i A', 'h:iA', 'H:i:s'];
                foreach ($timeFormats as $fmt) {
                    $dt = \DateTime::createFromFormat($fmt, $timeStr, $tz);
                    if ($dt !== false) {
                        $parsedTime = $dt;
                        break;
                    }
                }

                // Fallback to strtotime if needed
                if ($parsedTime === false) {
                    $ts = strtotime($timeStr);
                    if ($ts !== false) {
                        $parsedTime = (new \DateTime())->setTimestamp($ts)->setTimezone($tz);
                    }
                }

                if ($parsedTime === false) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Invalid schedule time format. Provide time like '12:30 PM' or '14:30'."),
                    ]);
                }

                // Parse date (accept common formats via strtotime)
                $dateTs = strtotime($dateStr);
                if ($dateTs === false) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Invalid schedule date format. Provide date like '2025-08-06'."),
                    ]);
                }

                $datePart = (new \DateTime())->setTimestamp($dateTs)->setTimezone($tz)->format('Y-m-d');
                $timePart = $parsedTime->format('H:i:s');

                $scheduledDatetime = new \DateTime("{$datePart} {$timePart}", $tz);
                $now = new \DateTime('now', $tz);

                // If current time is before scheduled datetime, do NOT allow status change
                if ($now < $scheduledDatetime) {
                    $formattedSched = $scheduledDatetime->format('Y-m-d H:i:s');
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Cannot change order status before scheduled date & time ({$formattedSched})."),
                    ]);
                }
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

    public function actionStartAndVerifyOtpOfOrder()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user authentication is successful 
        if (! $user_id) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Check if POST data exists  
        if (empty($post)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No data provided.");
            return $this->sendJsonResponse($data);
        }

        // Check for required fields
        if (empty($post['orderId']) || empty($post['vendor_details_id']) || empty($post['otp'])) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Order ID, Vendor Details ID, and OTP are required.");
            return $this->sendJsonResponse($data);
        }

        $orderId           = $post['orderId'];
        $vendor_details_id = $post['vendor_details_id'];
        $otp               = $post['otp'];
$agentId = $post['agentId']?? null;


        try {
            // Fetch the order based on the provided criteria
            $order = Orders::find()
                ->where(['id' => $orderId, 'vendor_details_id' => $vendor_details_id])
                ->one();

            if (! $order) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "Order not found for the given details.");
                return $this->sendJsonResponse($data);
            }



           $scheduleDate = $order->schedule_date ?? null;
            $scheduleTime = $order->schedule_time ?? null;

            if (!empty($scheduleDate) || !empty($scheduleTime)) {
                // If date missing but time provided, use order date if available
                if (empty($scheduleDate) && !empty($scheduleTime) && !empty($order->schedule_date)) {
                    $scheduleDate = $order->schedule_date;
                }

                // If we still don't have a date, we cannot validate — return error
                if (empty($scheduleDate)) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Schedule date is required to validate schedule time."),
                    ]);
                }

                // Normalize date and time
                $dateStr = trim($scheduleDate);
                $timeStr = trim($scheduleTime ?? '00:00');

                // Use Asia/Kolkata timezone
                $tz = new \DateTimeZone('Asia/Kolkata');

                // Try multiple time formats for robust parsing
                $parsedTime = false;
                $timeFormats = ['h:i A', 'H:i', 'g:i A', 'h:iA', 'H:i:s'];
                foreach ($timeFormats as $fmt) {
                    $dt = \DateTime::createFromFormat($fmt, $timeStr, $tz);
                    if ($dt !== false) {
                        $parsedTime = $dt;
                        break;
                    }
                }

                // Fallback to strtotime if needed
                if ($parsedTime === false) {
                    $ts = strtotime($timeStr);
                    if ($ts !== false) {
                        $parsedTime = (new \DateTime())->setTimestamp($ts)->setTimezone($tz);
                    }
                }

                if ($parsedTime === false) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Invalid schedule time format. Provide time like '12:30 PM' or '14:30'."),
                    ]);
                }

                // Parse date (accept common formats via strtotime)
                $dateTs = strtotime($dateStr);
                if ($dateTs === false) {
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Invalid schedule date format. Provide date like '2025-08-06'."),
                    ]);
                }

                $datePart = (new \DateTime())->setTimestamp($dateTs)->setTimezone($tz)->format('Y-m-d');
                $timePart = $parsedTime->format('H:i:s');

                $scheduledDatetime = new \DateTime("{$datePart} {$timePart}", $tz);
                $now = new \DateTime('now', $tz);

                // If current time is before scheduled datetime, do NOT allow status change
                if ($now < $scheduledDatetime) {
                    $formattedSched = $scheduledDatetime->format('Y-m-d H:i:s');
                    return $this->sendJsonResponse([
                        'status' => self::API_NOK,
                        'error'  => Yii::t("app", "Cannot change order status before scheduled date & time ({$formattedSched})."),
                    ]);
                }
            }
            // Check if the service type is 'walkin'
            if ($order->service_type !== Orders::WALK_IN) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "OTP verification is only allowed for walk-in service orders.");
                return $this->sendJsonResponse($data);
            }

            // Check if the provided OTP matches the order's OTP
            if ($order->otp != $otp) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "OTP verification failed. Please check the OTP and try again.");
                return $this->sendJsonResponse($data);
            }

            // Update order's status and verification flag
            $order->status    = Orders::STATUS_SERVICE_STARTED; // ensure constant is defined
            $order->is_verify = Orders::OTP_VERIFIED;

            if ($order->save(false)) {
                // Send push notification
                $title = Yii::t("app", "Your Order OTP is Verified");
                $body  = Yii::t("app", "Your OTP for order ID #{$order->id} has been successfully verified.");

                Yii::$app->notification->PushNotification(
                    $orderId,
                    $order->user_id,
                    $title,
                    $body,
                    'redirect'
                );

                $data['status']       = self::API_OK;
                $data['details']      = Yii::t("app", "OTP successfully verified and service started.");
                $data['is_verify']    = $order->is_verify;
                $data['order_status'] = $order->status;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "Failed to update the order status.");
            }
        } catch (\Exception $e) {
            Yii::error("Error verifying OTP for order: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred while processing the request.");
        }

        return $this->sendJsonResponse($data);
    }
public function actionStartOrder()
{
    $data    = [];
    $post    = Yii::$app->request->post();
    $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
    $auth    = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    // Check if user authentication is successful 
    if (!$user_id) {
        $data['status'] = self::API_NOK;
        $data['error']  = Yii::t("app", "User authentication failed. Please log in.");
        return $this->sendJsonResponse($data);
    }  

    // Check if POST data exists  
    if (empty($post)) {
        $data['status'] = self::API_NOK;
        $data['error']  = Yii::t("app", "No data provided.");
        return $this->sendJsonResponse($data);
    }

    // Check for required fields
    if (empty($post['orderId'])) {
        $data['status'] = self::API_NOK;
        $data['error']  = Yii::t("app", "Order ID is required.");
        return $this->sendJsonResponse($data);
    }

    $orderId = $post['orderId']?? null;
    $agentId = $post['agentId']?? null;

    try {
        // Get vendor details ID for this user
        $vendor_details_id = User::getVendorIdByUserId($user_id);

        // Fetch the order
        $order = Orders::find()
            ->where(['id' => $orderId, 'vendor_details_id' => $vendor_details_id])
            ->one();

        if (!$order) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Order not found for the given details.");
            return $this->sendJsonResponse($data);
        }

  

        // Schedule validation
        $scheduleDate = $order->schedule_date ?? null;
        $scheduleTime = $order->schedule_time ?? null;

        if (!empty($scheduleDate) || !empty($scheduleTime)) {
            // If date missing but time provided, use order date if available
            if (empty($scheduleDate) && !empty($scheduleTime) && !empty($order->schedule_date)) {
                $scheduleDate = $order->schedule_date;
            }

            // If we still don't have a date, we cannot validate — return error
            if (empty($scheduleDate)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Schedule date is required to validate schedule time."),
                ]);
            }

if(empty($agentId)){

        $dateStr = trim($scheduleDate);
            $timeStr = trim($scheduleTime ?? '00:00');
            $tz      = new \DateTimeZone('Asia/Kolkata');

            // Try multiple time formats
            $parsedTime = false;
            $timeFormats = ['h:i A', 'H:i', 'g:i A', 'h:iA', 'H:i:s'];
            foreach ($timeFormats as $fmt) {
                $dt = \DateTime::createFromFormat($fmt, $timeStr, $tz);
                if ($dt !== false) {
                    $parsedTime = $dt;
                    break;
                }
            }

            // Fallback to strtotime
            if ($parsedTime === false) {
                $ts = strtotime($timeStr);
                if ($ts !== false) {
                    $parsedTime = (new \DateTime())->setTimestamp($ts)->setTimezone($tz);
                }
            }

            if ($parsedTime === false) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Invalid schedule time format. Provide time like '12:30 PM' or '14:30'."),
                ]);
            }

            $dateTs = strtotime($dateStr);
            if ($dateTs === false) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Invalid schedule date format. Provide date like '2025-08-06'."),
                ]);
            }

            $datePart = (new \DateTime())->setTimestamp($dateTs)->setTimezone($tz)->format('Y-m-d');
            $timePart = $parsedTime->format('H:i:s');

            $scheduledDatetime = new \DateTime("{$datePart} {$timePart}", $tz);
            $now = new \DateTime('now', $tz);

            if ($now < $scheduledDatetime) {
                $formattedSched = $scheduledDatetime->format('Y-m-d H:i:s');
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Cannot start order before scheduled date & time ({$formattedSched})."),
                ]);
            }


       
        }

         
        }

        $data['status']       = self::API_OK;
        $data['details']      = Yii::t("app", "Order successfully started.");
        $data['order_status'] = $order->status;

    } catch (\Exception $e) {
        Yii::error("Error starting order: " . $e->getMessage(), __METHOD__);
        $data['status'] = self::API_NOK;
        $data['error']  = Yii::t("app", "An unexpected error occurred while processing the request.");
    }

    return $this->sendJsonResponse($data);
}


    public function actionDashboard()
    {
        $data      = [];
        $dashboard = [];
        $post      = Yii::$app->request->post();
        $headers   = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        // Parse and validate start and end dates
        $start_date       = ! empty($post['start_date']) ? $this->convertToDate($post['start_date']) : null;
        $end_date         = ! empty($post['end_date']) ? $this->convertToDate($post['end_date']) : null;
        $start_date_today = date('Y-m-d');
        $end_date_today   = date('Y-m-d');

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed. Please log in."),
            ]);
        }

        try {
            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Vendor details not found."),
                ]);
            }

            $vendor_details_id = $vendorDetails->id;
            $main_category_id  = $vendorDetails->main_category_id;

            // Base query condition for orders with approved earnings
            $queryConditions = ['o.vendor_details_id' => $vendor_details_id, 'o.status' => Orders::STATUS_SERVICE_COMPLETED];

            if ($start_date && $end_date) {
                $queryConditions = [
                    'and',
                    $queryConditions,
                    ['between', 'o.completed', "$start_date 00:00:00", "$end_date 23:59:59"],
                ];
            } else {

                $queryConditions = [
                    'and',
                    $queryConditions,
                    ['between', 'DATE(o.completed)', $start_date_today, $end_date_today],
                ];
            }

            $bookings = Orders::find()
                ->alias('o')
                ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id')
                ->where([
                    'o.vendor_details_id' => $vendor_details_id,
                    'o.status'            => Orders::STATUS_SERVICE_COMPLETED,
                    've.status'           => VendorEarnings::STATUS_APPROVED,
                ])->where($queryConditions)
                ->count();

            // Calculate amount collected (sum only from orders with approved earnings)
            $amount_collected = Orders::find()
                ->alias('o')
                ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
                ->where($queryConditions)
                ->sum('ve.vendor_received_amount') ?? 0;

            // Count home visits with approved earnings
            $home_visits = Orders::find()
                ->alias('o')
                ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
                ->where(['o.service_type' => Orders::TRANS_TYPE_HOME_VISIT])
                ->andWhere($queryConditions)
                ->count();

            // Count store visits with approved earnings
            $store_visits = Orders::find()
                ->alias('o')
                ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
                ->where(['o.service_type' => Orders::TRANS_TYPE_WALK_IN])
                ->andWhere($queryConditions)
                ->count();

            // Fetch all active services of the vendor
            $services = Services::find()
                ->joinWith(['subCategory' => function ($query) {
                    $query->andWhere(['sub_category.status' => SubCategory::STATUS_ACTIVE]);
                }])
                ->where([
                    'services.vendor_details_id' => $vendor_details_id,
                    'services.status'            => Services::STATUS_ACTIVE,
                ])
                ->andWhere(['IS NOT', 'services.price', null]) // price is not NULL
                ->andWhere(['>', 'services.price', 0])         // price > 0
                ->limit(10)
                ->all();

            $ordersServicesCount = [];

            // Loop through each service to get order count and total amount (only approved earnings)
            foreach ($services as $service) {
                $serviceQueryConditions = [
                    'ods.service_id'      => $service->id,
                    'o.vendor_details_id' => $vendor_details_id,
                    'o.status'            => Orders::STATUS_SERVICE_COMPLETED,
                ];

                if ($start_date && $end_date) {
                    $serviceQueryConditions = [
                        'and',
                        ['ods.service_id' => $service->id],
                        ['o.vendor_details_id' => $vendor_details_id],
                        ['between', 'DATE(o.completed)', $start_date, $end_date],
                    ];
                }

                // Fetch count and sum in a single query (only approved earnings)
                $query = Orders::find()
                    ->alias('o')
                    ->select([
                        'service_count' => 'COUNT(o.id)',
                        'total_amount'  => 'SUM(ods.price)',
                    ])
                    ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
                    ->innerJoinWith(['orderDetails ods'])
                    ->where($serviceQueryConditions)
                    ->asArray()
                    ->one();

                $ordersServicesCount[] = [
                    'service_name' => $service->service_name,
                    'count'        => (int) ($query['service_count'] ?? 0),
                    'price'        => round($query['total_amount'] ?? 0, 2),
                ];
            }

            // Prepare the response data
            $dashboard = [
                'bookings'            => $bookings,
                'amount_collected'    => round($amount_collected ?? 0.00, 2),
                'home_visits'         => $home_visits,
                'store_visits'        => $store_visits,
                'ordersServicesCount' => $ordersServicesCount,
                'shop_name'           => $vendorDetails->business_name,
            ];

            return $this->sendJsonResponse(['status' => self::API_OK, 'details' => $dashboard]);
        } catch (\Exception $e) {
            // Log error with stack trace for better debugging
            Yii::error("Error processing dashboard data: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "An unexpected error occurred while processing the request."),
            ]);
        }
    }

    public function actionMyEarnings()
    {
        $data      = [];
        $dashboard = [];
        $post      = Yii::$app->request->post();
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (! $user_id) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        try {
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

            // Query for total bookings and amount collected
            $query = VendorEarnings::find()
                ->alias('ve')
                ->innerJoin('orders o', 've.order_id = o.id AND o.status = ' . Orders::STATUS_SERVICE_COMPLETED)
                ->where(['ve.vendor_details_id' => $vendor_details_id])
                ->andWhere(['ve.status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['o.payment_status' => Orders::PAYMENT_DONE]);

            $total_bookings   = $query->count();
            $amount_collected = $query->sum('vendor_received_amount');

            // Prepare dashboard data
            $dashboard = [
                'total_bookings'   => $total_bookings,
                'amount_collected' => round($amount_collected ?? 0.00, 2),
            ];

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['details'] = $dashboard;
        } catch (\Exception $e) {
            Yii::error("Error processing dashboard data: " . $e->getMessage(), __METHOD__);
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred while processing the request.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionMyEarningsHistory()
    {
        $data      = [];
        $dashboard = [];
        $post      = Yii::$app->request->post();
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        // Check if user authentication is successful
        if (! $user_id) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        // Get start and end dates from the post data
        try {
            $start_date = ! empty($post['start_date']) ? $this->convertToDate($post['start_date']) : null;
            $end_date   = ! empty($post['end_date']) ? $this->convertToDate($post['end_date']) : null;
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Invalid date format: {message}", ['message' => $e->getMessage()]);
            return $this->sendJsonResponse($data);
        }

        try {
            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new \Exception("Vendor details not found or inactive.");
            }

            $vendor_details_id = $vendorDetails->id;

            // Query for total bookings and amount collected (same as My Earnings)
            $query = VendorEarnings::find()
                ->alias('ve')
                ->innerJoin('orders o', 've.order_id = o.id AND o.status = ' . Orders::STATUS_SERVICE_COMPLETED)
                ->where(['ve.vendor_details_id' => $vendor_details_id])
                ->andWhere(['ve.status' => VendorEarnings::STATUS_APPROVED])
                ->andWhere(['o.payment_status' => Orders::PAYMENT_DONE]);

            if (! empty($start_date) && ! empty($end_date)) {
                $query->andWhere(['between', 'DATE(ve.updated_on)', $start_date, $end_date]);
            }

            $total_bookings   = $query->count();
            $amount_collected = $query->sum('vendor_received_amount') ?? 0.00;

            // Fetch earnings history (detailed breakdown)
            $earnings_history_query = Orders::find()
                ->alias('o')
                ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
                ->where([
                    'o.vendor_details_id' => $vendor_details_id,
                    'o.status'            => Orders::STATUS_SERVICE_COMPLETED,
                    'o.payment_status'    => Orders::PAYMENT_DONE, // ✅ Correct placement
                ]);

            if (! empty($start_date) && ! empty($end_date)) {
                $earnings_history_query->andWhere(['between', 'DATE(ve.updated_on)', $start_date, $end_date]);
            }

            $earnings_history      = $earnings_history_query->all();
            $earnings_history_data = [];

            foreach ($earnings_history as $order) {
                $earnings_history_data[] = $order->asJsonEarningsDetails();
            }

            // Prepare response
            $dashboard = [
                'total_bookings'   => $total_bookings,
                'amount_collected' => round($amount_collected, 2),
                'earnings_history' => $earnings_history_data,
            ];

            $data['status']  = self::API_OK;
            $data['details'] = $dashboard;
            $data['message'] = Yii::t("app", "Earnings history fetched successfully.");
        } catch (\Exception $e) {
            Yii::error("Error fetching earnings history: " . $e->getMessage(), __METHOD__);
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionHomeVisitorList()
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

            // Fetch home visitors associated with the vendor
            $home_visitors = Staff::find()
                ->where(['vendor_details_id' => $vendor_details_id])

                // ->andWHere(['role'=>Staff::ROLE_HOME_VISITOR])
                ->all();
            $list = [];

            if (! empty($home_visitors)) {
                foreach ($home_visitors as $home_visitor) {
                    $list[] = $home_visitor->asJson(); // Convert each visitor to JSON format
                }
            }

            // Prepare response
            if (! empty($list)) {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No home visitors found.");
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An error occurred while fetching home visitors: " . $e->getMessage());
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

    public function actionAssignOrderToStaffss()
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

            // Fetch staff details
            $staff = Staff::findOne(['id' => $staff_id]);
            if (! $staff) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Staff not found.");
                return $this->sendJsonResponse($data);
            }

            // Check if staff is active and idle
            if ($staff->status != Staff::STATUS_ACTIVE) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Selected staff is not active.");
                return $this->sendJsonResponse($data);
            }

            if ($staff->current_status != Staff::CURRENT_STATUS_IDLE) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Selected staff is currently busy and cannot be assigned another order.");
                return $this->sendJsonResponse($data);
            }

            // Check if the order is already assigned
            $existingAssignment = HomeVisitorsHasOrders::find()
                ->where(['order_id' => $order_id])
                ->andWhere(['!=', 'home_visitor_id', $staff_id])
                ->one();

            if ($existingAssignment) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "This order is already assigned to another home visitor.");
                return $this->sendJsonResponse($data);
            }

            // Assign order
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

            // Update staff status to busy
            $staff->current_status = Staff::CURRENT_STATUS_BUSY;
            $staff->save(false);

            // Send notifications
            try {
                $isHomeVisit = $order->service_type == Orders::TRANS_TYPE_HOME_VISIT;

                $titleUser = Yii::t("app", "Your Order Assigned to Staff");
                $bodyUser  = $isHomeVisit
                    ? Yii::t("app", "Your order (#{$order_id}) has been assigned to a home visitor. Service will begin shortly.")
                    : Yii::t("app", "Your order (#{$order_id}) has been assigned to a staff member. Please visit our location for service.");

                Yii::$app->notification->PushNotification(
                    $order_id,
                    $order->user_id,
                    $titleUser,
                    $bodyUser,
                    $isHomeVisit ? "home_visit" : "walk_in"
                );

                if ($isHomeVisit) {
                    $titleVisitor = Yii::t("app", "New Order Assignment");
                    $bodyVisitor  = Yii::t("app", "You have been assigned a new home visit order (#{$order_id}). Please proceed with the service.");

                    Yii::$app->notification->PushNotification(
                        $order_id,
                        $staff->user_id,
                        $titleVisitor,
                        $bodyVisitor,
                        "home_visit"
                    );
                }
            } catch (\Exception $e) {
                Yii::error("Notification error: " . $e->getMessage(), __METHOD__);
            }

            // Success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order successfully assigned to the home visitor.");
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Yii::error("Error processing order assignment: " . $e->getMessage(), __METHOD__);
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred while processing the request.");
            return $this->sendJsonResponse($data);
        }
    }

    public function actionBookingHistory()
    {
        $data             = [];
        $post             = Yii::$app->request->post();
        $headers          = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth             = new AuthSettings();
        $user_id          = $auth->getAuthSession($headers);
        $page             = ! empty($post['page']) ? $post['page'] : 0;
        $main_category_id = ! empty($post['main_category_id']) ? $post['main_category_id'] : '';
        $service_type     = ! empty($post['service_type']) ? $post['service_type'] : null;
        $start_date       = ! empty($post['start_date']) ? $this->formatDate($post['start_date']) : '';
        $end_date         = ! empty($post['end_date']) ? $this->formatDate($post['end_date']) : '';

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

            // // Prepare the query
            // $query = Orders::find()->where(['vendor_details_id' => $vendor_details_id]);

            // // Filter by status
            // $query->andWhere(['in', 'status', [Orders::STATUS_SERVICE_COMPLETED]]);

            $query = Orders::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['payment_status' => Orders::PAYMENT_DONE])
                // ->alias('o')
                // ->innerJoin('vendor_earnings ve', 've.order_id = o.id') // Join vendor_earnings table
                // ->where([
                //     'o.vendor_details_id' => $vendor_details_id,
                //     'o.payment_status' => Orders::PAYMENT_DONE,
                //     'o.status' => Orders::STATUS_SERVICE_COMPLETED,
                //     've.status' => VendorEarnings::STATUS_APPROVED 
                // ])

            ;

            // Filter by main_category_id if provided
            if (! empty($main_category_id)) {
                $query->andWhere(['main_category_id' => $main_category_id]);
            }

            // Filter by schedule_time if start_date and end_date are provided
            if (! empty($start_date) && ! empty($end_date)) {
                $query->andWhere(['between', 'schedule_date', $start_date, $end_date]);
            }

            // Filter by service_type
            if (! empty($service_type)) {
                if ($service_type == 'walkin') {
                    $query->andWhere(['service_type' => 1]);
                } elseif ($service_type == 'homevisit') {
                    $query->andWhere(['service_type' => 2]);
                }
            }

            // Create ActiveDataProvider for pagination
            $newOrders = new ActiveDataProvider([
                'query'      => $query,
                'sort'       => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],
                ],
                'pagination' => [
                    'pageSize' => 20,
                    'page'     => $page,
                ],
            ]);

            // Process the orders
            $list = [];
            foreach ($newOrders->models as $order) {
                $list[] = $order->asJson();
            }

            // Check if any orders are found
            if (! empty($list)) {
                $pagination         = $newOrders->getPagination();
                $data['status']     = self::API_OK;
                $data['details']    = $list;
                $data['pagination'] = [
                    'totalCount'  => $newOrders->getTotalCount(),
                    'pageSize'    => $pagination->getPageSize(),
                    'currentPage' => $pagination->getPage() + 1, // zero-based to 1-based
                    'pageCount'   => $pagination->getPageCount(),
                ];
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No orders found.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
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

    public function actionMyProfile()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the user profile
            $model = User::findOne($user_id);
            if (empty($model)) {
                throw new NotFoundHttpException(Yii::t("app", "User profile not found."));
            }

            // Prepare success response
            $data['status']  = self::API_OK;
            $data['details'] = $model->asJsonVendor();
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
            $order = Orders::findOne(['id' => $order_id]);
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

    public function actionAddOrUpdateCoupon()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Check authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE]);
            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Required fields
            if (empty($post['coupon_title']) || empty($post['coupon_code']) || empty($post['discount_value']) || empty($post['start_date'])) {
                throw new BadRequestHttpException(Yii::t("app", "Required fields missing."));
            }

            // Parse start & end date-times
            $start_datetime = strtotime($post['start_date'] . ' ' . ($post['start_time'] ?? '00:00'));
            $end_datetime   = ! empty($post['end_date']) ? strtotime($post['end_date'] . ' ' . ($post['end_time'] ?? '00:00')) : null;
            $set_end_date   = ! empty($post['set_end_date']) ? (bool) $post['set_end_date'] : false;
            if ($set_end_date) {
                if (! $end_datetime) {
                    throw new BadRequestHttpException(Yii::t("app", "End date is required when set_end_date is true."));
                }
                if ($start_datetime >= $end_datetime) {
                    throw new BadRequestHttpException(Yii::t("app", "End date must be greater than start date."));
                }
            }

            // Check for existing coupon
            $existingCoupon = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.code' => $post['coupon_code'], 'cv.vendor_details_id' => $vendor_details_id])
                ->one();

            $isUpdate    = $existingCoupon ? true : false;
            $coupon      = $isUpdate ? $existingCoupon : new Coupon();
            $description = ! empty($post['description']) ? $post['description'] : '';

            // Set coupon fields
            $coupon->name          = $post['coupon_title'];
            $coupon->description   = $description;
            $coupon->code          = $post['coupon_code'];
            $coupon->discount_type = $post['discount_type'] ?? null;

            $coupon->discount          = $post['discount_value'];
            $coupon->max_discount      = $post['max_discount_amount'] ?? null;
            $coupon->min_cart          = $post['min_order_amount'] ?? null;
            $coupon->max_use_of_coupon = $post['max_use_of_coupon'] ?? null;
            $coupon->start_date        = date('Y-m-d H:i:s', $start_datetime);
            $coupon->end_date          = $set_end_date ? date('Y-m-d H:i:s', $end_datetime) : null;
            $coupon->is_global         = Coupon::IS_GLOBAL_NO;
            $coupon->status            = Coupon::STATUS_ACTIVE;

            if (! $coupon->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save coupon."));
            }

            // Associate vendor if it's a new coupon
            if (! $isUpdate) {
                $coupon_vendor                    = new CouponVendor();
                $coupon_vendor->coupon_id         = $coupon->id;
                $coupon_vendor->vendor_details_id = $vendor_details_id;
                $coupon_vendor->status            = CouponVendor::STATUS_ACTIVE;

                if (! $coupon_vendor->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to associate coupon with vendor."));
                }
            }

            // Success response
            $data['status']  = self::API_OK;
            $data['message'] = $isUpdate
                ? Yii::t("app", "Coupon updated successfully.")
                : Yii::t("app", "Coupon added successfully.");
            $data['details'] = $coupon->toArray();
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Unexpected error: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateCoupon()
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
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Validate required fields
            if (empty($post['coupon_title']) || empty($post['coupon_code']) || empty($post['discount_value']) || empty($post['start_date'])) {
                throw new BadRequestHttpException(Yii::t("app", "Required fields missing."));
            }

            // Parse dates for validation
            $start_date = strtotime($post['start_date']);
            $end_date   = ! empty($post['end_date']) ? strtotime($post['end_date']) : null;
            $coupon_id  = ! empty($post['coupon_id']) ? $post['coupon_id'] : null;

            // Validate the end date if it's required
            $set_end_date = ! empty($post['set_end_date']) ? $post['set_end_date'] : false;
            if ($set_end_date) {
                if (! $end_date) {
                    throw new BadRequestHttpException(Yii::t("app", "End date is required when set_end_date is true."));
                }
                if ($start_date > $end_date) {
                    throw new BadRequestHttpException(Yii::t("app", "End date must be greater than or equal to the start date."));
                }
            }

            // Check if coupon code is unique for the same vendor (ignore the current coupon being updated)
            $existingCouponWithSameCode = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.code' => $post['coupon_code'], 'cv.vendor_details_id' => $vendor_details_id])
                ->andWhere(['!=', 'c.id', $coupon_id])
                ->one();

            if ($existingCouponWithSameCode) {
                throw new BadRequestHttpException(Yii::t("app", "Coupon code is already in use by another coupon for this vendor."));
            }

            // Find the coupon to update
            $coupon = Coupon::findOne(['id' => $coupon_id]);
            if (! $coupon) {
                throw new NotFoundHttpException(Yii::t("app", "Coupon not found."));
            }

            // Set coupon attributes
            $coupon->name              = $post['coupon_title'];
            $coupon->description       = $post['description'] ?? null;
            $coupon->code              = $post['coupon_code'];
            $coupon->discount          = $post['discount_value'];
            $coupon->max_discount      = $post['max_discount_amount'] ?? null;
            $coupon->min_cart          = $post['min_order_amount'] ?? null;
            $coupon->max_use           = isset($post['max_use']) ? $post['max_use'] : 1;
            $coupon->max_use_of_coupon = $post['max_use_of_coupon'] ?? null;
            $coupon->start_date        = date('Y-m-d', $start_date);
            $coupon->end_date          = $set_end_date ? date('Y-m-d', $end_date) : null;
            $coupon->is_global         = Coupon::IS_GLOBAL_NO;
            $coupon->status            = Coupon::STATUS_ACTIVE;

            if (! $coupon->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save coupon."));
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Coupon updated successfully.");
            $data['details'] = $coupon->toArray();
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

    public function actionListStoreReviews()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

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
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;
            // Pagination
            $page     = ! empty($post['page']) ? (int) $post['page'] : 1;
            $pageSize = ! empty($post['page_size']) ? (int) $post['page_size'] : 50;

            $query = ShopReview::find()->where(['vendor_details_id' => $vendor_details_id]);

            // Get total count for pagination
            $totalCount = $query->count();

            // Apply pagination
            $reviews = $query
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->all();

            if (empty($reviews)) {
                throw new NotFoundHttpException(Yii::t("app", "No reviews found for the given vendor."));
            }

            // Prepare the review data for the response
            $reviewsData = [];
            foreach ($reviews as $review) {
                $reviewsData[] = [
                    'review_id'   => $review->id,
                    'rating'      => $review->rating,
                    'review_text' => $review->review_text,
                    'created_at'  => date('Y-m-d H:i:s', strtotime($review->created_at)),
                    'user_name'   => $review->user->username, // Assuming there is a relation to User model for the review author
                ];
            }

            // Prepare pagination data
            $pagination = [
                'total_count' => $totalCount,
                'page'        => $page,
                'page_size'   => $pageSize,
                'total_pages' => ceil($totalCount / $pageSize),
            ];

            // Prepare the successful response
            $data['status']     = self::API_OK;
            $data['message']    = Yii::t("app", "Store reviews retrieved successfully.");
            $data['reviews']    = $reviewsData;
            $data['pagination'] = $pagination;
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
            Yii::error("Error retrieving store reviews: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }
public function actionCouponList()
{
    $data    = [];
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth    = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);
    $post    = Yii::$app->request->post();

    try {
        // Check user authentication
        if (empty($user_id)) {
            throw new UnauthorizedHttpException("User authentication failed. Please log in.");
        }

        // Fetch vendor details
        $vendorDetails = VendorDetails::find()
            ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
            ->one();

        if (!$vendorDetails) {
            throw new NotFoundHttpException("Vendor details not found.");
        }

        $vendor_details_id = $vendorDetails->id;

        // Filters
        $couponType = !empty($post['coupon_type']) ? (int)$post['coupon_type'] : null;
        $search     = !empty($post['search']) ? trim($post['search']) : null;

        // Build query with eager loading
        $query = Coupon::find()
            ->alias('c')
            ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
            ->where(['cv.vendor_details_id' => $vendor_details_id])
            ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE])
            ->with(['couponHasDays.couponHasTimeSlots']); // load days & time slots

        // Apply coupon type filter
        if ($couponType !== null) {
            $query->andWhere(['c.coupon_type' => $couponType]);
        }

        // Apply search filter
        if (!empty($search)) {
            $query->andWhere(['like', 'c.name', $search]);
        }

        // Pagination
        $pageSize = !empty($post['page_size']) ? (int)$post['page_size'] : 10;
        $page     = !empty($post['page']) ? (int)$post['page'] - 1 : 0;

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'page'     => $page,
            ],
            'sort'       => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $coupons    = $dataProvider->getModels();
        $totalCount = $dataProvider->getTotalCount();

        if (empty($coupons)) {
            throw new NotFoundHttpException("No coupons found for the given vendor.");
        }

        // Transform coupon data to include days & time slots
        $couponData = [];
        foreach ($coupons as $coupon) {
            $days = [];
            foreach ($coupon->couponHasDays as $day) {
                $timeSlots = [];
                foreach ($day->couponHasTimeSlots as $slot) {
                    $timeSlots[] = [
                        'start_time' => $slot->start_time,
                        'end_time'   => $slot->end_time,
                    ];
                }

                $days[] = [
                    'day'       => $day->day,
                    'timeSlots' => $timeSlots,
                ];
            }

            $couponData[] = [
                'id'          => $coupon->id,
                'name'        => $coupon->name,
                'coupon_type' => $coupon->coupon_type,
                'status'      => $coupon->status,
                'days'        => $days,
            ];
        }

        // Pagination info
        $pagination = [
            'total_count' => $totalCount,
            'page'        => $dataProvider->pagination->page + 1,
            'page_size'   => $pageSize,
            'total_pages' => ceil($totalCount / $pageSize),
        ];

        // Success response
        $data = [
            'status'     => self::API_OK,
            'message'    => "Coupons retrieved successfully.",
            'coupons'    => $couponData,
            'pagination' => $pagination,
        ];

    } catch (\Throwable $e) {
        $data = [
            'status' => self::API_NOK,
            'error'  => $e->getMessage(),
        ];
    }

    return $this->sendJsonResponse($data);
}




    public function actionReelDelete()
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
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;
            $id                = ! empty($post['id']) ? (int) $post['id'] : null;

            if (empty($id)) {
                throw new BadRequestHttpException(Yii::t("app", "Reel ID is required."));
            }

            // Find the reel
            $reels = Reels::findOne(['id' => $id, 'vendor_details_id' => $vendor_details_id]);

            if (! $reels) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found or does not belong to the vendor."));
            }

            // Update the status to 'Deleted'
            $reels->status = Reels::STATUS_DELETE;
            if (! $reels->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to delete the reel."));
            }

            // Success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel deleted successfully.");
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
            Yii::error("Error deleting reel: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionStaffInactiveOrActive()
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
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;
            $staff_id          = ! empty($post['staff_id']) ? (int) $post['staff_id'] : null;
            $status            = isset($post['status']) ? (int) $post['status'] : null;

            // Validate required fields
            if (empty($staff_id) || $status === null) {
                throw new BadRequestHttpException(Yii::t("app", "Staff ID and status are required."));
            }

            // Fetch the staff member
            $staff = Staff::findOne(['id' => $staff_id, 'vendor_details_id' => $vendor_details_id]);

            if (! $staff) {
                throw new NotFoundHttpException(Yii::t("app", "Staff member not found."));
            }

            // Update the staff status
            $staff->status = $status;
            if (! $staff->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update staff status."));
            }

            // Success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Staff status updated successfully.");
            $data['details'] = $staff->toArray();
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
            Yii::error("Error updating staff status: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionNotifications()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Authentication check
            if (empty($user_id)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'User authentication failed. Please log in.',
                ]);
            }

            // Vendor validation
            $vendor = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendor) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Vendor details not found.',
                ]);
            }

            // Fetch notifications and unread count in parallel
            $notifications = FcmNotification::find()
                ->where(['user_id' => $user_id])
                ->orderBy(['created_on' => SORT_DESC])
                ->all();

            $unreadCount = FcmNotification::find()
                ->where(['user_id' => $user_id, 'is_read' => FcmNotification::IS_READ_NO])
                ->count();

            // Prepare and return response
            return $this->sendJsonResponse([
                'status'               => self::API_OK,
                'message'              => 'Notifications retrieved successfully.',
                'details'              => array_map(function ($n) {
                    return $n->asJson();
                }, $notifications),
                'FcmNotificationCount' => $unreadCount,
            ]);
        } catch (\Throwable $e) {
            Yii::error("Error in actionNotifications: " . $e->getMessage(), __METHOD__);
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => 'An unexpected error occurred. Please try again later.',
            ]);
        }
    }

    public function actionClearNotifications()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication
            if (empty($user_id)) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'User authentication failed. Please log in.';
                return $this->sendJsonResponse($data);
            }

            // Convert form-data string to boolean correctly
            $clear_all       = filter_var(Yii::$app->request->post('clear_all', false), FILTER_VALIDATE_BOOLEAN);
            $notification_id = Yii::$app->request->post('notification_id', null);

            if ($clear_all) {
                // Delete all notifications
                FcmNotification::deleteAll(['user_id' => $user_id]);
                $data['status']  = self::API_OK;
                $data['message'] = 'All notifications cleared successfully.';
            } elseif (! empty($notification_id)) {
                // Delete a specific notification
                $notification = FcmNotification::find()->where(['id' => $notification_id, 'user_id' => $user_id])->one();
                if ($notification) {
                    $notification->delete();
                    $data['status']  = self::API_OK;
                    $data['message'] = 'Notification deleted successfully.';
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = 'Notification not found.';
                }
            } else {
                // No valid action provided
                $data['status'] = self::API_NOK;
                $data['error']  = 'Invalid request. Please provide either clear_all = true or a valid notification_id.';
            }
        } catch (\Exception $e) {
            Yii::error("Error clearing notifications: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred: ' . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionReadNotification()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            // Check user authentication
            if (empty($user_id)) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'User authentication failed. Please log in.';
                return $this->sendJsonResponse($data);
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Vendor details not found.';
                return $this->sendJsonResponse($data);
            }

            // Validate required fields
            $notification_id = ! empty($post['notification_id']) ? (int) $post['notification_id'] : null;
            if (! $notification_id) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Notification ID is required.';
                return $this->sendJsonResponse($data);
            }

            // Find notification by ID and mark as read
            $notification = FcmNotification::findOne(['id' => $notification_id, 'user_id' => $user_id]);

            if (! $notification) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Notification not found.';
                return $this->sendJsonResponse($data);
            }

            $notification->is_read = FcmNotification::IS_READ_YES; // Mark as read
            if (! $notification->save(false)) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Failed to update notification status.';
                return $this->sendJsonResponse($data);
            }

            // Prepare success response
            $data['status']       = self::API_OK;
            $data['message']      = 'Notification marked as read successfully.';
            $data['notification'] = [
                'id'      => $notification->id,
                'title'   => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'User authentication failed. Please log in.';
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Notification not found.';
        } catch (\Exception $e) {
            Yii::error("Error marking notification as read: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred: ' . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewStaffById()
    {
        $data     = [];
        $post     = Yii::$app->request->post();
        $staff_id = $post['staff_id'] ?? null; // Ensure staff_id is provided
        $headers  = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);

        try {
            // Validate if staff_id is provided
            if (empty($staff_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Staff ID is required."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            // Find the staff by ID and vendor details
            $staff = Staff::findOne(['id' => $staff_id, 'vendor_details_id' => $vendor_details_id]);
            if (! empty($staff)) {
                $data['status']  = self::API_OK;
                $data['details'] = $staff->asJson(); // Assuming asJson() formats the staff details appropriately
                $data['message'] = Yii::t("app", "Staff details retrieved successfully.");
            } else {
                throw new NotFoundHttpException(Yii::t("app", "Staff not found."));
            }
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Bad request: {message}", ['message' => $e->getMessage()]);
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Unauthorized access: {message}", ['message' => $e->getMessage()]);
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Resource not found: {message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewReelById()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $reel_id = $post['reel_id'] ?? null; // Ensure reel_id is provided
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Validate if reel_id is provided
            if (empty($reel_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Reel ID is required."));
            }

            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Find the vendor's shop details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $vendor_details_id = $VendorDetails->id;

            // Find the reel by ID and vendor details
            $reel = Reels::findOne(['id' => $reel_id, 'vendor_details_id' => $vendor_details_id, 'status' => Reels::STATUS_ACTIVE]);
            if (! empty($reel)) {
                $data['status']  = self::API_OK;
                $data['details'] = $reel->asJson(); // Assuming asJson() formats the reel details appropriately
                $data['message'] = Yii::t("app", "Reel details retrieved successfully.");
            } else {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found."));
            }
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Bad request: {message}", ['message' => $e->getMessage()]);
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Unauthorized access: {message}", ['message' => $e->getMessage()]);
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Resource not found: {message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewCouponById()
    {
        $data      = [];
        $post      = Yii::$app->request->post();
        $coupon_id = $post['coupon_id'] ?? null; // Ensure coupon_id is provided
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        try {
            // Validate if coupon_id is provided
            if (empty($coupon_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Coupon ID is required."));
            }

            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Fetch the coupon by ID and vendor details
            $coupon = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.id' => $coupon_id, 'cv.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE])
                ->one();

            if ($coupon) {
                $data['status']  = self::API_OK;
                $data['details'] = $coupon->asJson(); // Assuming asJson() formats the coupon details appropriately
                $data['message'] = Yii::t("app", "Coupon details retrieved successfully.");
            } else {
                throw new NotFoundHttpException(Yii::t("app", "Coupon not found."));
            }
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
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

    public function actionDeleteCoupon()
    {
        $data      = [];
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);
        $post      = Yii::$app->request->post();
        $coupon_id = $post['coupon_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate coupon_id
            if (empty($coupon_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Coupon ID is required."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            $vendor_details_id = $vendorDetails->id;

            // Fetch the coupon by ID and vendor details
            $coupon = Coupon::find()
                ->alias('c')
                ->innerJoin(CouponVendor::tableName() . ' cv', 'cv.coupon_id = c.id')
                ->where(['c.id' => $coupon_id, 'cv.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<>', 'c.status', Coupon::STATUS_DELETE])
                ->one();

            $coupon_vendor = CouponVendor::find()
                ->where(['coupon_id' => $coupon_id, 'vendor_details_id' => $vendor_details_id])
                ->one();
            if ($coupon_vendor) {
                $coupon_vendor->status = CouponVendor::STATUS_DELETE;
                if (! $coupon_vendor->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to delete the coupon vendor association."));
                }
            }

            if (! $coupon) {
                throw new NotFoundHttpException(Yii::t("app", "Coupon not found or already deleted."));
            }

            // Mark the coupon as deleted
            $coupon->status = Coupon::STATUS_DELETE;

            if (! $coupon->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to delete the coupon."));
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Coupon deleted successfully.");
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

    public function actionGetServiceListByOrderId()
    {
        $data     = [];
        $headers  = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);
        $post     = Yii::$app->request->post();
        $order_id = $post['order_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate order_id
            if (empty($order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Order Id is required."));
            }

            // Fetch vendor details
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (empty($vendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            // Fetch order details by order_id
            $order_details = OrderDetails::find()->where(['order_id' => $order_id])->all();
            if (empty($order_details)) {
                throw new NotFoundHttpException(Yii::t("app", "No order details found for the given order ID."));
            }

            // Extract service IDs from order details
            $service_id_data = [];
            foreach ($order_details as $order_detail) {
                $service_id_data[] = $order_detail->service_id;
            }

            // Fetch services based on the extracted service IDs
            $services = Services::find()
                ->joinWith(['subCategory.mainCategory sm'])
                ->where(['in', 'id', $service_id_data])
                ->andWhere(['sm.is_scheduled_next_visit' => 1])
                ->all();
            if (empty($services)) {
                throw new NotFoundHttpException(Yii::t("app", "No services found for the given order."));
            }

            // Prepare service data for response
            $list = [];
            foreach ($services as $service) {
                $list[] = $service->asJsonByOrder();
            }

            // Build the success response
            $data['status']  = self::API_OK;
            $data['details'] = $list;
            $data['message'] = Yii::t("app", "Services retrieved successfully.");
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "A server error occurred: {message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionSaveNextVisitDetails()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        $order_id                      = $post['order_id'] ?? null;
        $name                          = $post['name'] ?? null;
        $description                   = $post['description'] ?? null;
        $prescription_file             = $post['prescription_file'] ?? null;
        $next_visit_details_json       = $post['next_visit_details_json'] ?? null;
        $next_visit_order_payment_type = $post['next_visit_order_payment_type'] ?? '';

        try {
            if (empty($user_id)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "User authentication failed. Please log in.");
                return $this->sendJsonResponse($data);
            }

            $requiredFields = [
                'order_id'                      => $order_id,
                'name'                          => $name,
                'description'                   => $description,
                'prescription_file'             => $prescription_file,
                'next_visit_details_json'       => $next_visit_details_json,
                'next_visit_order_payment_type' => $next_visit_order_payment_type,
            ];

            $missingFields = [];
            foreach ($requiredFields as $field => $value) {
                if ($value === null || $value === '') {
                    $missingFields[] = $field;
                }
            }

            if (! empty($missingFields)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Missing required fields: {fields}", [
                    'fields' => implode(', ', $missingFields),
                ]);
                return $this->sendJsonResponse($data);
            }

            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (empty($vendorDetails)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found.");
                return $this->sendJsonResponse($data);
            }

            $next_visit_details = NextVisitDetails::find()->where(['order_id' => $order_id])->one();
            if (empty($next_visit_details)) {
                $next_visit_details = new NextVisitDetails();
            }

            $next_visit_details->order_id                = $order_id;
            $next_visit_details->next_visit_details_json = $next_visit_details_json;
            $next_visit_details->name                    = $name;
            $next_visit_details->description             = $description;
            $next_visit_details->prescription_file       = $prescription_file;

            if (! $next_visit_details->save(false)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to save next visit details.");
                return $this->sendJsonResponse($data);
            }

            $existing_order = Orders::find()
                ->where(['id' => $order_id, 'vendor_details_id' => $vendorDetails->id])
                ->one();

            $next_visit_details_json_decoded = json_decode($next_visit_details_json, true);

            // Step 1: Extract all visit dates & times to determine the latest
            $all_visits = [];
            foreach ($next_visit_details_json_decoded as $orderCreate) {
                $visit_details = $orderCreate['next_visit_details'] ?? [];
                foreach ($visit_details as $visit) {
                    $all_visits[] = [
                        'date' => $visit['date'],
                        'time' => $visit['time'],
                    ];
                }
            }

            usort($all_visits, function ($a, $b) {
                return strtotime($a['date'] . ' ' . $a['time']) <=> strtotime($b['date'] . ' ' . $b['time']);
            });
            $last_visit = end($all_visits);

            if (! empty($next_visit_details_json_decoded)) {
                $total_amount = $orderCreate['amount'];
                $visit_count  = count($visit_details);

                foreach ($next_visit_details_json_decoded as $orderCreate) {
                    $service_id    = $orderCreate['service_id'] ?? null;
                    $visit_details = $orderCreate['next_visit_details'] ?? [];
                    $payment_type  = $orderCreate['mode_of_amount_collection'] ?? 1;

                    if (empty($service_id) || empty($visit_details)) {
                        continue;
                    }

                    $main_amount = $orderCreate['amount'] / count($visit_details);
                    $amount      = $main_amount;

                    $settings       = new WebSetting();
                    $tax            = $settings->getSettingBykey('tax') ?? 0;
                    $cgst_order_tax = $tax / 2;
                    $sgst_order_tax = $tax / 2;

                    usort($visit_details, function ($a, $b) {
                        return strtotime($a['date']) <=> strtotime($b['date']);
                    });

                    foreach ($visit_details as $key => $visit) {

                        if ($next_visit_order_payment_type == Orders::NEXT_VISIT_ORDER_PAYMENT_TYPE_FULL) {
                            $amount = ($key === 0) ? $total_amount : 0;
                        } else {
                            $amount = $total_amount / $visit_count;
                        }

                        if (empty($amount)) {
                            $conv_fee = 0;
                        } else {
                            $conv_fee = $settings->getSettingBykey('conv_fee') ?? 0;
                        }

                        $visitDate = $visit['date'];
                        $visitTime = $visit['time'];
                        $dayOfWeek = date('w', strtotime($visitDate));
                        $days      = Days::find()
                            ->where(['id' => $dayOfWeek])
                            ->one();
                        $timing = StoreTimings::find()
                            ->where([
                                'vendor_details_id' => $vendorDetails->id,
                                'day_id'            => $days->id,
                            ])
                            ->one();

                        if (! $timing || $timing->status != 1) {
                            $data['status']  = self::API_NOK;
                            $data['message'] = "Store is closed or inactive on " . date('l', strtotime($visitDate)) . ". Please select another date.";
                            return $this->sendJsonResponse($data);
                        }
                        $visitTimestamp = strtotime($visitDate . ' ' . $visitTime);
                        $startTimestamp = strtotime($visitDate . ' ' . $timing->start_time);
                        $closeTimestamp = strtotime($visitDate . ' ' . $timing->close_time);
                        if ($visitTimestamp < $startTimestamp || $visitTimestamp > $closeTimestamp) {
                            $data['status']  = self::API_NOK;
                            $data['message'] = "Selected time ($visitTime) is outside store working hours (" .
                                date("h:i A", strtotime($timing->start_time)) . " - " .
                                date("h:i A", strtotime($timing->close_time)) . "). Please choose another time.";
                            return $this->sendJsonResponse($data);
                        }

                        $sp_order                    = new Orders();
                        $sp_order->user_id           = $existing_order->user_id;
                        $sp_order->vendor_details_id = $vendorDetails->id;
                        $sp_order->main_category_id  = $vendorDetails->main_category_id;
                        $sp_order->qty               = 1;
                        $sp_order->trans_type        = $existing_order->trans_type;
                        $sp_order->service_type      = $existing_order->service_type;
                        $sp_order->payment_type      = $payment_type;
                        $sp_order->sub_total         = $amount;
                        $sp_order->tip_amt           = 0;
                        $sp_order->tax               = $tax;
                        $sp_order->cgst              = $cgst_order_tax;
                        $sp_order->sgst              = $sgst_order_tax;
                        $sp_order->status_step       = $key + 1 . ' of ' . count($visit_details);

                        $service_charge                 = $conv_fee;
                        $service_charge_tax             = round(($service_charge * $tax) / 100, 2);
                        $sp_order->service_charge       = $service_charge;
                        $sp_order->service_charge_w_tax = round($service_charge + $service_charge_tax, 2);
                        $sp_order->processing_charges   = 0;
                        $sp_order->taxable_total        = number_format(
                            floatval($amount) + floatval($sp_order->service_charge_w_tax),
                            2,
                            '.',
                            ''
                        );
                        $sp_order->service_charge_tax_amt = $sp_order->service_charge_w_tax - $service_charge;

                        $taxOnSubTotal          = round(($amount * $tax) / 100, 2);
                        $sp_order->Subtotal_tax = $taxOnSubTotal;
                        $sp_order->total_w_tax  = round(
                            floatval($amount) + floatval($taxOnSubTotal) + floatval($sp_order->service_charge_w_tax),
                            2
                        );

                        $sp_order->payment_mode   = $existing_order->payment_mode;
                        $sp_order->payable_amount = $sp_order->total_w_tax;
                        $sp_order->balance_amount = $sp_order->total_w_tax;

                        $sp_order->status              = Orders::STATUS_NEW_ORDER;
                        $sp_order->schedule_date       = $visit['date'];
                        $sp_order->schedule_time       = date("h:i A", strtotime($visit['time']));
                        $sp_order->service_address     = $existing_order->service_address;
                        $sp_order->service_instruction = '';
                        $sp_order->voucher_code        = '';
                        $sp_order->voucher_amount      = 0;
                        $sp_order->voucher_type        = 0;
                        $sp_order->ip_ress             = $_SERVER['REMOTE_ADDR'];
                        $sp_order->otp                 = rand(1111, 9999);
                        $sp_order->parent_order_id     = $existing_order->id;
                        $sp_order->is_next_visit       = 1;

                        // ✅ Set next_visit_required only for the latest visit
                        $is_last_visit                 = ($visit['date'] === $last_visit['date'] && $visit['time'] === $last_visit['time']);
                        $sp_order->next_visit_required = $is_last_visit ? 1 : 0;

                        $sp_order->payment_status = Orders::PAYMENT_PENDING;

                        if ($sp_order->save(false)) {
                            $order_items                     = new OrderDetails();
                            $order_items->order_id           = $sp_order->id;
                            $order_items->service_id         = $service_id;
                            $order_items->qty                = 1;
                            $order_items->total_price        = $amount;
                            $order_items->price              = $amount;
                            $order_items->is_package_service = 0;
                            $order_items->status             = 1;
                            $order_items->save(false);
                        }
                    }
                }
            }

            // Update the original order with the next visit details
            $order_count = Orders::find()->where(['parent_order_id' => $existing_order->id])->count();
            $title       = "Sessions Created";
            $body        = "A new sessions has been created for your order count: {$order_count}";

            $vendor_title = "New Sessions are Created";
            $vendor_body  = "{$order_count} new session" . ($order_count == 1 ? '' : 's') . " ha" . ($order_count == 1 ? 's' : 've') . " been added under the order.";

            // Send to user
            Yii::$app->notification->PushNotification(
                $sp_order->id,
                $sp_order->user_id,
                $title,
                $body,
                'redirect'
            );

            Yii::$app->notification->PushNotification(
                $sp_order->id,
                $sp_order->vendorDetails->user_id,
                $vendor_title,
                $vendor_body,
                'redirect'
            );

            $data['status']  = self::API_OK;
            $data['details'] = $next_visit_details->asJson();
            $data['message'] = Yii::t("app", "Next visit details and orders saved successfully.");
        } catch (\Throwable $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An error occurred: {msg}", ['msg' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVerifySubscriptionPayment()
    {
        $data                    = [];
        $headers                 = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                    = new AuthSettings();
        $user_id                 = $auth->getAuthSession($headers);
        $post                    = Yii::$app->request->post();
        $vendor_subscriptions_id = $post['vendor_subscriptions_id'] ?? null;
        $payment_id              = $post['payment_id'] ?? null;
        $razorpay_order_id       = $post['razorpay_order_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate required fields
            if (empty($vendor_subscriptions_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor subscription ID is required."));
            }

            if (empty($payment_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Payment ID is required."));
            }

            if (empty($razorpay_order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Razorpay order ID is required."));
            }

            // Fetch payment details from Razorpay
            $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            if (! empty($fetchPaymentDetails->error)) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', "Error fetching payment details: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']);
                return $this->sendJsonResponse($data);
            }

            $vendor_subscriptions = VendorSubscriptions::findOne(['id' => $vendor_subscriptions_id, 'razorpay_order_id' => $razorpay_order_id]);
            if (! $vendor_subscriptions) {
                throw new NotFoundHttpException(Yii::t("app", "Vendor subscription not found."));
            }

            // Process payment capture if needed
            if ($fetchPaymentDetails->status !== 'captured') {
                Razorpay::capturePayment($fetchPaymentDetails->amount, $payment_id);
                $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));

                if ($fetchPaymentDetails->status !== 'captured') {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t('app', "Payment capture failed: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']);
                    return $this->sendJsonResponse($data);
                }
            }

            $vendor_subscriptions->payment_id = $payment_id;

            // Update order payment status
            if ($fetchPaymentDetails->status == 'captured') {
                $vendor_subscriptions->payment_status = Orders::PAYMENT_DONE;
            } else {
                $vendor_subscriptions->payment_status = Orders::PAYMENT_FAILED;
            }

            if (! $vendor_subscriptions->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', "Failed to update order payment status."));
            }

            // Set response for successful payment
            $data['status']                  = self::API_OK;
            $data['details']                 = $vendor_subscriptions->asJson();
            $data['vendor_subscriptions_id'] = $vendor_subscriptions->id;
            $data['razorpay_res']            = $fetchPaymentDetails;
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
            $data['error']  = Yii::t("app", "A server error occurred: {message}", ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionPaymentConformationWebhooks()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Get the incoming request body as a string
        $data = file_get_contents('php://input');

        try {
            // Decode the JSON payload
            $res_json_decode = json_decode($data);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \yii\web\BadRequestHttpException('Invalid JSON payload');
            }

            // Check if the event is 'payment.authorized' or 'payment.captured'
            $event = $res_json_decode->event ?? null;
            if ($event !== 'payment.authorized' && $event !== 'payment.captured') {
                return ['status' => 'error', 'message' => 'Invalid event type or missing event'];
            }

            // Extract payment ID from webhook
            $payment_id = $res_json_decode->payload->payment->entity->id ?? null;
            if (! $payment_id) {
                throw new \yii\web\BadRequestHttpException('Missing payment ID in the webhook');
            }

            // Fetch payment details from Razorpay API
            $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            if (isset($fetchPaymentDetails->error)) {
                throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Error fetching payment details: {message}', [
                    'message' => $fetchPaymentDetails->error->description ?? 'Unknown error',
                ]));
            }

            // If payment status is authorized, capture the payment
            if ($fetchPaymentDetails->status === 'authorized') {
                $captureResponse = json_decode(Razorpay::capturePayment($fetchPaymentDetails->amount, $payment_id));
                if (isset($captureResponse->error)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Payment capture failed: {message}', [
                        'message' => $captureResponse->error->description ?? 'Unknown error',
                    ]));
                }
            }

            // Continue with order processing if payment is captured
            if ($fetchPaymentDetails->status === 'captured') {
                $razorpay_order_id = $fetchPaymentDetails->order_id ?? null;

                if (! $razorpay_order_id) {
                    throw new \yii\web\BadRequestHttpException('Missing Razorpay order_id');
                }

                // Find the transaction details using the Razorpay order_id
                $order_transaction_details = OrderTransactionDetails::findOne(['razorpay_order_id' => $razorpay_order_id]);

                if (! $order_transaction_details) {
                    throw new \yii\web\NotFoundHttpException('Order transaction details not found');
                }

                // Find the related order using the transaction details
                $orders = VendorSubscriptions::findOne(['id' => $order_transaction_details->order_id]);

                if (! $orders) {
                    throw new \yii\web\NotFoundHttpException('Order not found');
                }

                // Update the payment status to 'PAYMENT_DONE' if the payment is captured
                $orders->payment_status = Orders::PAYMENT_DONE;
                if ($orders->save(false)) {

                    // Update the payment ID in the transaction details
                    $order_transaction_details->payment_id = $payment_id;
                    $order_transaction_details->status     = OrderTransactionDetails::STATUS_SUCCESS;
                    $order_transaction_details->save(false);

                    return ['status' => 'success', 'message' => 'Order payment status updated successfully'];
                } else {
                    throw new \yii\web\ServerErrorHttpException('Failed to update order payment status');
                }
            } else {
                // Payment status is not captured
                return ['status' => 'error', 'message' => 'Payment not captured or invalid status'];
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle bad request errors
            Yii::error('Bad Request: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\yii\web\NotFoundHttpException $e) {
            // Handle not found errors
            Yii::error('Not Found: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\yii\web\ServerErrorHttpException $e) {
            // Handle server errors
            Yii::error('Server Error: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            // Handle generic exceptions
            Yii::error('Error: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'An unexpected error occurred'];
        }
    }

    public function actionCreateSubscriptionOrder()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user authentication is successful
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // Get the subscription ID and amount from the POST request
            $subscription_id = Yii::$app->request->post('subscription_id');
            $amount          = Yii::$app->request->post('amount');

            // Get the subscription details from the database
            $subscription = Subscriptions::findOne($subscription_id);

            // If the subscription does not exist, return an error
            if (! $subscription) {
                throw new \yii\web\NotFoundHttpException('Subscription not found.');
            }

            // Razorpay expects the amount in paise (1 INR = 100 paise)
            $amount = $amount * 100;

            // Create the Razorpay order for the selected subscription
            $response = Razorpay::createAnOrderVendorSubscription($subscription_id, $amount);

            // Log the response to check what is returned
            Yii::info('Razorpay Order Response: ' . $response, __METHOD__);

            // Decode the response from Razorpay API
            $order = json_decode($response, true);

            // Check if the Razorpay order ID exists in the response
            if (isset($order['id'])) {
                $razorpay_order_id = $order['id'];

                // Prepare success response
                $data['status']            = self::API_OK;
                $data['razorpay_order_id'] = $razorpay_order_id;
            } else {
                throw new \yii\web\ServerErrorHttpException('Order creation failed. No order ID returned.');
            }
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Data not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ServerErrorHttpException $e) {
            Yii::error('Server error: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('An unexpected error occurred: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred. Please try again later.';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVerifyPayment()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user authentication is successful
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // Get payment_id and order_id from POST request
            $payment_id = Yii::$app->request->post('payment_id');
            $order_id   = Yii::$app->request->post('order_id');

            // If either payment_id or order_id is missing, return an error
            if (! $payment_id || ! $order_id) {
                throw new \yii\web\BadRequestHttpException('Invalid request. Missing parameters.');
            }

            // Fetch order details
            $order_details = json_decode(Razorpay::fetchOrderDetails($order_id), true);
            if (! $order_details || isset($order_details['error'])) {
                throw new \yii\web\NotFoundHttpException('Failed to fetch order details.');
            }

            // Fetch payment details
            $payment_details = json_decode(Razorpay::fetchPaymentDetails($payment_id), true);
            if (! $payment_details || isset($payment_details['error'])) {
                throw new \yii\web\NotFoundHttpException('Failed to fetch payment details.');
            }

            // Verify payment status
            if ($payment_details['status'] === 'authorized') {
                $amount           = $order_details['amount'] ?? 0;
                $capture_response = Razorpay::capturePayment($amount, $payment_id);

                $capture_result = json_decode($capture_response, true);
                if (! $capture_result || isset($capture_result['error'])) {
                    throw new \yii\web\ServerErrorHttpException('Failed to capture payment.');
                }

                if ($capture_result['status'] === 'captured') {
                    // Successfully captured payment, update the order status
                    $data['status']  = self::API_OK;
                    $data['message'] = 'Payment captured successfully.';
                } else {
                    throw new \yii\web\ServerErrorHttpException('Capture failed.');
                }
            } else {
                throw new \yii\web\BadRequestHttpException('Payment not authorized.');
            }
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Data not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::error('Bad request: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ServerErrorHttpException $e) {
            Yii::error('Server error: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('An unexpected error occurred: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred. Please try again later.';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetServiceTypes()
    {
        $data             = [];
        $post             = Yii::$app->request->post();
        $headers          = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth             = new AuthSettings();
        $user_id          = $auth->getAuthSession($headers);
        $main_category_id = $post['main_category_id'] ?? null;

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Get vendor details
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendorDetails) {
                throw new BadRequestHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch ServiceTypes
            $serviceTypes = ServiceType::find()
                ->where(['status' => ServiceType::STATUS_ACTIVE])
                ->andWhere(['main_category_id' => $main_category_id])
                ->all();

            if (empty($serviceTypes)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No service types found for the selected main categories.');
            } else {
                $list = [];
                foreach ($serviceTypes as $serviceType) {
                    $list[] = $serviceType->asJson(); // Assuming asJson() exists
                }

                $data['status']        = self::API_OK;
                $data['message']       = Yii::t('app', 'Service types retrieved successfully.');
                $data['service_types'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionShopReviews()
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
            // Fetch vendor details based on authenticated user
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found for the logged-in user.");
                return $this->sendJsonResponse($data);
            }

            $vendor_id = $vendorDetails->id;

            // Fetch reviews for the vendor
            $reviews = ShopReview::find()
                ->where(['vendor_details_id' => $vendor_id])
                ->all();

            $list = [];
            if (! empty($reviews)) {
                foreach ($reviews as $review) {
                    $list[] = $review->asJson();
                }
            }

            // Prepare response
            if (! empty($list)) {
                $data['status']  = self::API_OK;
                $data['reviews'] = $list;
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No reviews found for this vendor.");
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An error occurred while fetching reviews: " . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVendorQrPayHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Vendor authentication failed. Please log in.',
            ];
        }

        $vendordetails = VendorDetails::find()
            ->where([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,

            ])->one();

        if (empty($vendordetails)) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Vendor Details Not Found.',
            ];
        }
        // Fetch QR payments received by the vendor
        $vendorQrPayments = VendorEarnings::find()
            ->where([
                'vendor_details_id' => $vendordetails->id,
                'type'              => VendorEarnings::PAYMENT_TYPE_QR,
                'payment_status'    => VendorEarnings::PAYMENT_STATUS_SUCCESS,
            ])
            ->orderBy(['created_on' => SORT_DESC])
            ->all();

        $list = [];

        foreach ($vendorQrPayments as $payment) {
            $list[] = $payment->asJson(); // Assuming same function formats data
        }

        if (! empty($list)) {
            $data['status']  = self::API_OK;
            $data['details'] = $list;
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No QR payment history found.");
        }

        return $data;
    }

    public function actionCreateSubscriptionRequest()
    {
        $post = Yii::$app->request->post();

        // Get Authentication Header
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated
        if (empty($user_id)) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Vendor authentication failed. Please log in.',
            ];
        }

        // Fetch Vendor Details using User ID
        $vendor = VendorDetails::find()->where(['user_id' => $user_id])->one();

        if (! $vendor) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Vendor Details Not Found.',
            ];
        }

        // Validate Required Fields
        if (empty($post['subscription_id']) || empty($post['duration'])) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Subscription ID and Duration are required.',
            ];
        }

        $subscription_id = $post['subscription_id'];
        $duration        = (int) $post['duration'];

        // Validate Duration
        if ($duration <= 0) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Invalid subscription duration.',
            ];
        }

        // Check if subscription exists
        if (! Subscriptions::findOne($subscription_id)) {
            return [
                'status'  => self::API_NOK,
                'message' => 'Invalid subscription ID.',
            ];
        }
        $subscription = Subscriptions::find()
            ->where(['id' => $subscription_id, 'status' => Subscriptions::STATUS_ACTIVE])
            ->one();

        // Calculate new start and end dates
        $start_date = date('Y-m-d');
        $end_date   = date('Y-m-d', strtotime("+$subscription->validity_in_days days"));
        // Always create a new subscription request
        $model                    = new VendorSubscriptions();
        $model->vendor_details_id = $vendor->id;
        $model->subscription_id   = $subscription_id;
        $model->duration          = $duration;
        $model->start_date        = $start_date;
        $model->end_date          = $end_date;
        $model->status            = VendorSubscriptions::STATUS_INACTIVE;        // Admin needs to approve
        $model->payment_status    = VendorSubscriptions::PAYMENT_STATUS_PENDING; // Default as unpaid

        if ($model->save(false)) {
            return [
                'status'               => self::API_OK,
                'message'              => 'Subscription request submitted. Admin will verify and activate.',
                'subscription_details' => $model->asJson(),
            ];
        } else {
            return [
                'status'  => self::API_NOK,
                'message' => 'Subscription request failed.',
                'errors'  => $model->getErrors(),
            ];
        }
    }

    public function actionViewSubscriptionDetails()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated
        if (! $user_id) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Vendor authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        try {
            // Fetch Vendor Details using User ID
            $vendorDetails = VendorDetails::find()
                ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->one();

            if (! $vendorDetails) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Vendor details not found for the logged-in user.");
                return $this->sendJsonResponse($data);
            }

            $vendor_id = $vendorDetails->id;

            // Fetch Vendor Subscription Details
            $subscriptions = VendorSubscriptions::find()
                ->where(['vendor_details_id' => $vendor_id])
                ->all();

            $list = [];
            if (! empty($subscriptions)) {
                foreach ($subscriptions as $subscription) {
                    $list[] = $subscription->asJson();
                }
            }

            // Prepare response
            if (! empty($list)) {
                $data['status']               = self::API_OK;
                $data['subscription_details'] = $list;
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No subscription found for this vendor.");
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An error occurred while fetching subscription details: " . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCheck()
    {
        $data = [];

        // Get auth_code from headers or request
        $headers   = getallheaders();
        $auth_code = $headers['auth_code'] ?? Yii::$app->request->get('auth_code');

        if (! $auth_code) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'detail' => [
                    'error' => Yii::t("app", 'Auth code not found'),
                    'auth'  => '',
                ],
            ]);
        }

        // Fetch Auth Session
        $auth_session = AuthSession::find()->where(['auth_code' => $auth_code])->one();

        if (! $auth_session) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'detail' => ['error' => Yii::t("app", 'Session not found')],
            ]);
        }

        $user = User::find()->where(['id' => $auth_session->create_user_id])->andWhere(['status' => User::STATUS_ACTIVE])
            ->andWhere(['user_role' => User::ROLE_VENDOR])
            ->one();

        if (! $user) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'detail' => ['error' => Yii::t("app", 'Session not found')],
            ]);
        }

        $vendor = VendorDetails::find()->where(['user_id' => $user->id])->one();

        // Base response
        $data['status']               = self::API_OK;
        $data['detail']               = $user->asJsonVendor();
        $data['detail']['membership'] = false;
        $data['detail']['purchase']   = false;

        if ($vendor) {

            VendorSubscriptions::updateAll(
                ['status' => VendorSubscriptions::STATUS_INACTIVE], // Set expired to inactive
                [
                    'and',
                    ['vendor_details_id' => $vendor->id, 'status' => VendorSubscriptions::STATUS_ACTIVE],
                    ['<', 'end_date', date('Y-m-d H:i:s')], // Expired ones
                ]
            );

            // Fetch Active Membership
            $membership = VendorSubscriptions::find()
                ->where(['vendor_details_id' => $vendor->id, 'status' => VendorSubscriptions::STATUS_ACTIVE])
                ->andWhere(['>', 'end_date', date('Y-m-d H:i:s')]) // Ensures expired ones are ignored
                ->one();

            if ($membership) {
                $data['detail']['membership']      = true;
                $data['detail']['subscription_id'] = $membership->subscription_id;
                $data['detail']['start_date']      = $membership->start_date;
                $data['detail']['end_date']        = $membership->end_date;
            } else {
                $data['detail']['message'] = 'No active membership';
            }

            // Check if the vendor has made a purchase request
            $hasActiveOrInactiveSubscription = VendorSubscriptions::find()
                ->where(['vendor_details_id' => $vendor->id])
                ->andWhere(['status' => [VendorSubscriptions::STATUS_ACTIVE, VendorSubscriptions::STATUS_INACTIVE]]) // Active or pending request
                ->andWhere([
                    'or',
                    ['>', 'end_date', date('Y-m-d H:i:s')], // Active and not expired
                    ['end_date' => null],                   // Inactive request (no start & end date yet)
                ])
                ->exists();

            $data['detail']['purchase'] = $hasActiveOrInactiveSubscription;
        }

        // Update Device Token if provided
        if (! empty($_POST['AuthSession']['device_token'])) {
            $auth_session->device_token     = $_POST['AuthSession']['device_token'];
            $data['detail']['auth_session'] = $auth_session->save()
                ? Yii::t("app", 'Auth Session updated')
                : $auth_session->flattenErrors;
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCollectCash()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "User authentication failed."),
            ]);
        }

        if (empty($post['order_id'])) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order ID is required."),
            ]);
        }

        $order_id = $post['order_id'];

        $vendorDetails = VendorDetails::find()->where(['user_id' => $user_id])->one();
        if (! $vendorDetails) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Vendor not found."),
            ]);
        }

        $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $vendorDetails->id]);
        if (! $order) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Order not found"),
            ]);
        }

        $alreadyPaid = OrderTransactionDetails::find()
            ->where(['order_id' => $order_id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
            ->sum('amount');

        $remaining = $order->total_w_tax - $alreadyPaid;

        if ($remaining <= 0) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "This order has already been fully paid."),
            ]);
        }

        // Save transaction
        $transaction               = new OrderTransactionDetails();
        $transaction->order_id     = $order_id;
        $transaction->amount       = $remaining;
        $transaction->order_type   = Razorpay::ORDER_TYPE_SERVICE_ORDER;
        $transaction->payment_type = OrderTransactionDetails::PAYMENT_TYPE_COD;
        $transaction->status       = OrderTransactionDetails::STATUS_SUCCESS;

        if (! $transaction->save(false)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Failed to save cash collection."),
            ]);
        }

        // Approve payout if fully paid now
        $totalPaid = $alreadyPaid + $remaining;
        if ($totalPaid >= $order->total_w_tax) {
            $payout = VendorEarnings::find()->where(['order_id' => $order->id])->one();
            if ($payout) {
                $payout->status = VendorPayout::STATUS_APPROVED;
                $payout->save(false);
            }
        }

        return $this->sendJsonResponse([
            'status'           => self::API_OK,
            'message'          => Yii::t("app", "Cash collected and recorded successfully."),
            'collected_amount' => $remaining,
            'total_paid'       => $totalPaid,
            'remaining'        => max(0, $order->total_w_tax - $totalPaid),
            'transaction'      => $transaction->asJson(),
        ]);
    }

    public function actionEnableSubServices()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Vendor authentication failed. Please log in."),
            ]);
        }

        try {
            // Validate inputs
            $service_id        = $post['service_id'] ?? null;
            $enable_or_disable = $post['enable_or_deseable'] ?? null;

            if ($service_id === null || $enable_or_disable === null) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "Service ID and status flag are required."));
            }

            // Get vendor
            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            // Get the service owned by this vendor
            $service = Services::findOne([
                'id'                => $service_id,
                'vendor_details_id' => $vendorDetails->id,
            ]);

            if (! $service) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Service not found or does not belong to this vendor."));
            }

            // Update the parent service flag
            $service->is_parent_service = $enable_or_disable;
            if ($service->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Service status updated successfully.");
            } else {
                throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to update service status."));
            }
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Unexpected error: ") . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateBankDetails()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Vendor authentication failed. Please log in."),
            ]);
        }

        try {
            // Validate required fields
            if (empty($post['account_number']) || empty($post['account_holder_name']) || empty($post['ifsc_code'])) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "Account number, account holder name, and IFSC code are required."));
            }

            // Get vendor details
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Vendor details not found."));
            }

            // Check IFSC
            $ifscInfo = json_decode(VendorDetails::checkiFsc($post['ifsc_code']));

            if (! isset($ifscInfo->IFSC)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Invalid IFSC code. Please verify and try again."),
                ]);
            }

            // Update bank details
            $vendorDetails->account_number      = $post['account_number'];
            $vendorDetails->account_holder_name = $post['account_holder_name'];
            $vendorDetails->ifsc_code           = $post['ifsc_code'];
            $vendorDetails->bank_name           = $ifscInfo->BANK ?? null;
            $vendorDetails->bank_branch         = $ifscInfo->BRANCH ?? null;
            $vendorDetails->bank_state          = $ifscInfo->STATE ?? null;
            $vendorDetails->bank_city           = $ifscInfo->CITY ?? null;
            $vendorDetails->bank_address        = $ifscInfo->ADDRESS ?? null;

            if ($vendorDetails->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Bank details updated successfully.");
                $data['details'] = $vendorDetails->asJson();
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "Failed to update bank details.");
            }
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Unexpected error: ") . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetVendorServicies()
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

            $page     = isset($post['page']) ? max(1, (int) $post['page']) : 1;
            $pageSize = isset($post['page_size']) ? (int) $post['page_size'] : 10;
            $offset   = ($page - 1) * $pageSize;

            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Active vendor not found."));
            }

            $query = Services::find()
                ->where(['vendor_details_id' => $vendorDetails->id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE, Services::STATUS_ADMIN_WAITING_FOR_APPROVAL]])
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

            $totalCount = $query->count();
            $services   = $query->offset($offset)->limit($pageSize)->all();

            $list = [];
            foreach ($services as $service) {
                $list[] = $service->asJson();
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

            $data = [
                'status'           => self::API_OK,
                'page'             => $page,
                'page_size'        => $pageSize,
                'total'            => $totalCount,
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

    public function actionViewServicesById()
    {

        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $get     = Yii::$app->request->get();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed. Please log in."),
            ]);
        }

        try {
            $service_id = $get['service_id'] ?? null;

            if (empty($service_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "service id is required."));
            }

            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Active vendor not found."));
            }

            $services = Services::find()
                ->where([
                    'vendor_details_id' => $vendorDetails->id,
                    'id'                => $service_id,
                ])

                ->one();

            $data['status']  = self::API_OK;
            $data['details'] = $services->asJson();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Unexpected error: ") . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteChildServices()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'     => self::API_NOK,
                'message'    => Yii::t("app", "Vendor authentication failed. Please log in."),
                'error_code' => 'AUTH_FAILED',
            ]);
        }

        try {
            $service_id = $post['service_id'] ?? null;

            if (empty($service_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "Service ID is required."));
            }

            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id,
                'status'  => VendorDetails::STATUS_ACTIVE,
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Active vendor not found."));
            }

            $service = Services::find()
                ->where([
                    'vendor_details_id' => $vendorDetails->id,
                    'id'                => $service_id,
                ])
                ->one();

            if (! $service) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Service not found."));
            }

            // Mark service as deleted
            $service->status = Services::STATUS_DELETE;
            $service->save(false);

            $data['status']  = self::API_OK;
            $data['details'] = $service->asJson();
        } catch (\yii\web\BadRequestHttpException $e) {
            $data['status']     = self::API_NOK;
            $data['message']    = $e->getMessage();
            $data['error_code'] = 'BAD_REQUEST';
        } catch (\yii\web\NotFoundHttpException $e) {
            $data['status']     = self::API_NOK;
            $data['message']    = $e->getMessage();
            $data['error_code'] = 'NOT_FOUND';
        } catch (\yii\web\ServerErrorHttpException $e) {
            $data['status']     = self::API_NOK;
            $data['message']    = Yii::t("app", "Server error occurred.");
            $data['error_code'] = 'SERVER_ERROR';
        } catch (\Exception $e) {
            $data['status']     = self::API_NOK;
            $data['message']    = Yii::t("app", "Unexpected error: ") . $e->getMessage();
            $data['error_code'] = 'UNEXPECTED_ERROR';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionSelfUpdateVendorCategories()
    {
        $vendor_details = VendorDetails::find()->all();
        if (! empty($vendor_details)) {
            foreach ($vendor_details as $vendor_details_data) {
                $vendor_details_id = $vendor_details_data->id;
                $main_category_id  = $vendor_details_data->main_category_id;
                $user_id           = $vendor_details_data->user_id;

                $vendor_main_category_data = VendorMainCategoryData::findOne(['vendor_details_id' => $vendor_details_id, 'main_category_id' => $main_category_id]);
                if (empty($vendor_main_category_data)) {
                    $VendorMainCategoryData                    = new VendorMainCategoryData();
                    $VendorMainCategoryData->vendor_details_id = $vendor_details_id;
                    $VendorMainCategoryData->main_category_id  = $main_category_id;
                    $VendorMainCategoryData->user_id           = $user_id;

                    $VendorMainCategoryData->status = VendorMainCategoryData::STATUS_ACTIVE;
                    $VendorMainCategoryData->save(false);
                }
            }
        }
    }

    public function actionCalendar()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            $start = new \DateTime(); // today's date
            $dates = [];

            for ($i = 0; $i < 30; $i++) {
                $currentDate = $start->format('Y-m-d');


                $orderCount = Orders::find()
                    ->where([
                        'vendor_details_id' => $shop->id,
                        'schedule_date'     => $currentDate,
                    ])
                    ->andWhere(['in', 'status', [
                        Orders::STATUS_NEW_ORDER,
                        Orders::STATUS_ACCEPTED,
                        Orders::STATUS_SERVICE_STARTED,
                        Orders::STATUS_ASSIGNED_SERVICE_STAFF,
                        Orders::STATUS_START_TO_LOCATION_HOME_VISIT,
                        Orders::STATUS_ARRIVED_CUSTOMER_LOCATION
                    ]])
                    ->andWhere([
                        'or',
                        ['payment_status' => Orders::PAYMENT_DONE],
                        ['is_next_visit' => 1]
                    ])
                    ->count();

                $dates[] = [
                    'date'        => $currentDate,
                    'order_count' => $orderCount,
                ];

                $start->modify('+1 day');
            }

            $data['status']  = self::API_OK;
            $data['dates']   = $dates;
            $data['message'] = Yii::t("app", "Calendar data retrieved successfully.");
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVendorSelectedMainCategory()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            $vendor_main_category_data = VendorMainCategoryData::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['status' => VendorMainCategoryData::STATUS_ACTIVE])
                ->all();

            $list = [];
            if (! empty($vendor_main_category_data)) {
                foreach ($vendor_main_category_data as $index => $item) {
                    $jsonItem          = $item->asJson();
                    $jsonItem['index'] = $index;
                    $list[]            = $jsonItem;
                }
            }

            if (empty($list)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No main category found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVendorServiceTypes()
    {
        $data             = [];
        $headers          = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth             = new AuthSettings();
        $user_id          = $auth->getAuthSession($headers);
        $post             = Yii::$app->request->post();
        $main_category_id = $post['main_category_id'] ?? null;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }


            $vendor_service_types = StoreServiceTypes::find()
                ->where(['store_id' => $shop->id])
                ->andWhere(['main_category_id' => $main_category_id])
                ->andWhere(['in', 'status', [StoreServiceTypes::STATUS_ACTIVE, StoreServiceTypes::STATUS_INACTIVE]])
                ->all();

            $list = [];

            if (! empty($vendor_service_types)) {
                foreach ($vendor_service_types as $vendor_service_type) {

                    // Count related services (active + inactive only)
                    $serviceCount = Services::find()
                        ->where(['store_service_type_id' => $vendor_service_type->id])
                        ->andWhere(['in', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE, Services::STATUS_ADMIN_WAITING_FOR_APPROVAL]])
                        ->count();

                    // Only add categories that have at least 1 service
                    if ($serviceCount > 0) {
                        $list[] = $vendor_service_type->asJsonVendor();
                    }
                }
            }

            if (empty($list)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No service types found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateSubCategory()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            if (! $user_id) {
                throw new UnauthorizedHttpException("Invalid or expired authentication.");
            }

            $post                  = Yii::$app->request->post();
            $id                    = $post['id'] ?? null; // subcategory ID (optional for update)
            $main_category_id      = $post['main_category_id'] ?? null;
            $service_type_id       = $post['service_type_id'] ?? null;
            $store_service_type_id = $post['store_service_type_id'] ?? null;
            $title                 = trim($post['title'] ?? '');

            if (! $main_category_id || ! $service_type_id || ! $store_service_type_id || empty($title)) {
                throw new \Exception("Missing required parameters.");
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException("No shop details found for this user.");
            }

            $vendor_details_id = $shop->id;

            $serviceType = \app\modules\admin\models\ServiceType::findOne(['id' => $service_type_id]);
            if (! $serviceType) {
                throw new NotFoundHttpException("Invalid service_type_id provided.");
            }

            // Update by ID (if provided)
            if (! empty($id)) {
                $subCategory = SubCategory::findOne(['id' => $id, 'vendor_details_id' => $vendor_details_id]);
                if (! $subCategory) {
                    throw new NotFoundHttpException("Sub-category with ID not found or not yours.");
                }
            } else {
                // Try to find based on composite key
                $subCategory = SubCategory::findOne([
                    'main_category_id'      => $main_category_id,
                    'vendor_details_id'     => $vendor_details_id,
                    'service_type_id'       => $service_type_id,
                    'store_service_type_id' => $store_service_type_id,
                    'title'                 => $title,
                ]);

                if (! $subCategory) {
                    $subCategory = new SubCategory();
                }
            }

            $slug = User::generateUniqueSlug($title . $vendor_details_id, $vendor_details_id);

            $subCategory->main_category_id      = $main_category_id;
            $subCategory->vendor_details_id     = $vendor_details_id;
            $subCategory->store_service_type_id = $store_service_type_id;
            $subCategory->image                 = $serviceType->image ?? null;
            $subCategory->service_type_id       = $service_type_id;
            $subCategory->title                 = $title;
            $subCategory->slug                  = $slug;
            $subCategory->status                = SubCategory::STATUS_ACTIVE;

            if ($subCategory->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = $id ? "Sub-category updated successfully." : "Sub-category created successfully.";
                $data['details'] = $subCategory->asJsonVendorStoreService();
            } else {
                $data['message'] = "Failed to save sub-category.";
                $data['errors']  = $subCategory->getErrors();
            }
        } catch (Exception $e) {
            $data['message'] = $e->getMessage();
        } catch (Exception $e) {
            $data['message'] = "An unexpected error occurred: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateComboPackage()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            // Required fields
            $title          = $post['title'] ?? null;
            $price          = $post['price'] ?? null;
            $discount_price = $post['discount_price'] ?? null;
            $time           = $post['duration'] ?? null;

            if (! $title || $price === null || ! $time || $discount_price === null || $discount_price < 0) {
                throw new \yii\web\BadRequestHttpException("Title, price,discount_price and duration are required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $isUpdate     = ! empty($post['id']);
            $comboPackage = null;

            // ✅ Check for existing duplicate combo if creating (not updating)
            if (! $isUpdate) {
                $duplicate = ComboPackages::find()
                    ->where([
                        'vendor_details_id' => $vendor->id,
                        'title'             => $title,
                        'price'             => $price,
                        'time'              => $time,
                    ])
                    ->exists();

                if ($duplicate) {
                    throw new \yii\web\ConflictHttpException("A combo package with the same title, price, and time already exists.");
                }
            }

            // Load model if updating
            if ($isUpdate) {
                $comboPackage = ComboPackages::findOne([
                    'id'                => $post['id'],
                    'vendor_details_id' => $vendor->id,
                ]);

                if (! $comboPackage) {
                    throw new \yii\web\NotFoundHttpException("Combo package not found.");
                }
            } else {
                $comboPackage                 = new ComboPackages();
                $comboPackage->created_on     = date('Y-m-d H:i:s');
                $comboPackage->create_user_id = $user_id;
            }

            // Set/update fields
            $comboPackage->vendor_details_id = $vendor->id;
            $comboPackage->title             = $title;
            $comboPackage->price             = $price;
            $comboPackage->discount_price    = $discount_price;
            $comboPackage->time              = $time;
            $comboPackage->is_home_visit     = $post['is_home_visit'] ?? 0;
            $comboPackage->is_walk_in        = $post['is_walk_in'] ?? 0;
            $comboPackage->service_for       = $post['service_for'] ?? null;
            $comboPackage->description       = $post['description'] ?? null;
            $comboPackage->status            = $post['status'] ?? 1;
            $comboPackage->updated_on        = date('Y-m-d H:i:s');
            $comboPackage->update_user_id    = $user_id;

            if (! $comboPackage->save(false)) {
                throw new \yii\web\ServerErrorHttpException("Failed to save combo package.");
            }

            $data['status']  = self::API_OK;
            $data['message'] = $isUpdate ? "Combo package updated successfully." : "Combo package created successfully.";
            $data['details'] = $comboPackage->attributes;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateComboService()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            // Extract and validate required fields
            $title          = $post['title'] ?? null;
            $price          = $post['price'] ?? null;
            $time           = $post['duration'] ?? null;
            $services_ids   = $post['services_ids'] ?? null; // Array
            $isUpdate       = ! empty($post['id']);
            $discount_price = ! empty($post['discount_price']) ? $post['discount_price'] : 0;

            if (is_string($services_ids)) {
                // If string starts with [ and ends with ], treat it as JSON array
                $trimmed = trim($services_ids);
                if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                    $services_ids = json_decode($trimmed, true);
                } else {
                    // fallback: CSV string "6171,6174,6170"
                    $services_ids = array_filter(array_map('trim', explode(',', $trimmed)));
                }
            }

            if (! $title || $price === null || ! $time || empty($services_ids) || ! is_array($services_ids)) {
                throw new \yii\web\BadRequestHttpException("Title, price, duration, and services_ids (array) are required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Active vendor not found.");
            }

            $comboPackage = null;

            // Check duplicate if not updating
            if (! $isUpdate) {
                $duplicate = ComboPackages::find()
                    ->where([
                        'vendor_details_id' => $vendor->id,
                        'title'             => $title,
                        'price'             => $price,
                        'time'              => $time,
                    ])
                    ->exists();

                if ($duplicate) {
                    throw new \yii\web\ConflictHttpException("A combo package with the same title, price, and time already exists.");
                }

                $comboPackage                 = new ComboPackages();
                $comboPackage->created_on     = date('Y-m-d H:i:s');
                $comboPackage->create_user_id = $user_id;
            } else {
                $comboPackage = ComboPackages::findOne([
                    'id'                => $post['id'],
                    'vendor_details_id' => $vendor->id,
                ]);

                if (! $comboPackage) {
                    throw new \yii\web\NotFoundHttpException("Combo package not found.");
                }

                // Remove old combo services
                ComboServices::deleteAll([
                    'combo_package_id'  => $comboPackage->id,
                    'vendor_details_id' => $vendor->id,
                ]);
            }

            // Set/update combo package fields
            $comboPackage->vendor_details_id = $vendor->id;
            $comboPackage->title             = $title;
            $comboPackage->price             = $price;
            $comboPackage->discount_price    = $discount_price;
            $comboPackage->time              = $time;
            $comboPackage->is_home_visit     = $post['home_visit'] ?? 0;
            $comboPackage->is_walk_in        = $post['walk_in'] ?? 0;
            $comboPackage->service_for       = $post['service_for'] ?? null;
            $comboPackage->description       = $post['description'] ?? null;
            $comboPackage->status            = $post['status'] ?? 1;
            $comboPackage->updated_on        = date('Y-m-d H:i:s');
            $comboPackage->update_user_id    = $user_id;

            if (! $comboPackage->save(false)) {
                throw new \yii\web\ServerErrorHttpException("Failed to save combo package.");
            }

            // Save combo services
            foreach ($services_ids as $service_id) {
                $comboService                    = new ComboServices();
                $comboService->vendor_details_id = $vendor->id;
                $comboService->combo_package_id  = $comboPackage->id;
                $comboService->services_id       = $service_id;
                $comboService->status            = ComboServices::STATUS_ACTIVE;
                $comboService->created_on        = date('Y-m-d H:i:s');
                $comboService->create_user_id    = $user_id;

                if (! $comboService->save()) {
                    throw new \yii\web\ServerErrorHttpException("Failed to save service ID $service_id");
                }
            }

            $data['status']        = self::API_OK;
            $data['message']       = $isUpdate ? "Combo package and services updated successfully." : "Combo package and services created successfully.";
            $data['combo_package'] = $comboPackage->attributes;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewComboServices()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $get     = Yii::$app->request->get();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $combo_package_id = $get['combo_package_id'] ?? null;
            if (! $combo_package_id) {
                throw new \yii\web\BadRequestHttpException("combo_package_id is required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $comboServices = ComboServices::find()
                ->where([
                    'combo_package_id'  => $combo_package_id,
                    'vendor_details_id' => $vendor->id,
                    'status'            => ComboServices::STATUS_ACTIVE,
                ])
                ->with('service') // assumes relation getService() exists
                ->all();

            $list = [];
            foreach ($comboServices as $combo) {
                if ($combo->service) {
                    $list[] = $combo->service->asJson();
                }
            }

            $data['status']           = self::API_OK;
            $data['combo_package_id'] = $combo_package_id;
            $data['services']         = $list;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionChangeComboPackageStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $combo_package_id = $post['combo_package_id'] ?? null;
            $new_status       = $post['status'] ?? null;

            if ($combo_package_id === null || $new_status === null) {
                throw new \yii\web\BadRequestHttpException("combo_package_id and status are required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $combo = ComboPackages::findOne([
                'id'                => $combo_package_id,
                'vendor_details_id' => $vendor->id,
            ]);

            if (! $combo) {
                throw new \yii\web\NotFoundHttpException("Combo package not found.");
            }

            $combo->status         = $new_status;
            $combo->updated_on     = date('Y-m-d H:i:s');
            $combo->update_user_id = $user_id;

            if (! $combo->save(false)) {
                throw new \yii\web\ServerErrorHttpException("Failed to update combo package status.");
            }

            $data['status']  = self::API_OK;
            $data['message'] = "Combo package status updated.";
            $data['details'] = $combo->attributes;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetSubCategories()
    {
        $data = [];

        try {
            // Get authentication header
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (! $user_id) {
                return $this->sendJsonResponse([
                    'status'  => self::API_NOK,
                    'message' => Yii::t("app", "Vendor authentication failed."),
                ]);
            }

            // Fetch POST data
            $post                  = Yii::$app->request->post();
            $main_category_id      = $post['main_category_id'] ?? null;
            $service_type_id       = $post['service_type_id'] ?? null;
            $store_service_type_id = $post['store_service_type_id'] ?? null;

            // Validate required fields
            if (! $main_category_id || ! $service_type_id || ! $store_service_type_id) {
                throw new \yii\web\BadRequestHttpException("main_category_id, service_type_id and store_service_type_id are required.");
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor_details) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            // Get subcategories
            $sub_categories = SubCategory::find()
                ->where([
                    'main_category_id'      => $main_category_id,
                    'service_type_id'       => $service_type_id,
                    'store_service_type_id' => $store_service_type_id,
                    'vendor_details_id'     => $vendor_details->id,
                    'status'                => SubCategory::STATUS_ACTIVE,
                ])
                ->all();

            if (empty($sub_categories)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No sub-categories found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($item) {
                    return $item->asJsonVendorStoreService();
                }, $sub_categories);
            }
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionToggleVendorServiceTypeStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        $store_service_type_id = $post['store_service_type_id'] ?? null;
        $new_status            = $post['status'] ?? null; // Expecting 1 (active) or 2 (inactive)

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            if ($store_service_type_id === null || ! in_array($new_status, [2, 1, 3])) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid service type ID or status."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop found for this user."));
            }

            $serviceType = StoreServiceTypes::findOne([
                'id'       => $store_service_type_id,
                'store_id' => $shop->id,
            ]);

            if (! $serviceType) {
                throw new NotFoundHttpException(Yii::t("app", "Service type not found or doesn't belong to your store."));
            }

            $serviceType->status = $new_status;
            if ($serviceType->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Service type status updated successfully.");
                $data['details'] = $serviceType->asJsonVendor();
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Failed to update status.");
            }
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionChangeSubCategoryStatus()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (! $user_id) {
                throw new UnauthorizedHttpException("Invalid or expired authentication.");
            }

            $post            = Yii::$app->request->post();
            $sub_category_id = $post['sub_category_id'] ?? null;
            $status          = $post['status'] ?? null;

            if (! $sub_category_id || $status === null) {
                throw new \Exception("Sub-category ID and status are required.");
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException("No shop details found for this user.");
            }

            $subCategory = SubCategory::findOne([
                'id'                => $sub_category_id,
                'vendor_details_id' => $shop->id,
            ]);

            if (! $subCategory) {
                throw new NotFoundHttpException("Sub-category not found.");
            }

            $subCategory->status = $status;

            if ($subCategory->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = "Sub-category status updated successfully.";
                $data['details'] = $subCategory->asJsonVendorStoreService();
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = "Failed to update status.";
                $data['errors']  = $subCategory->getErrors();
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "An error occurred: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionListComboPackages()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $get     = Yii::$app->request->get();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $query = ComboPackages::find()
                ->where(['vendor_details_id' => $vendor->id]);

            // Optional filter by status
            if (isset($get['status'])) {
                $query->andWhere(['status' => $get['status']]);
            }

            $comboPackages = $query->orderBy(['id' => SORT_DESC])->all();

            $list = [];
            foreach ($comboPackages as $combo) {
                $list[] = $combo->asJsonVender();
            }

            $data['status']         = self::API_OK;
            $data['combo_packages'] = $list;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCallToUser()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $post     = Yii::$app->request->post();
            $order_id = $post['order_id'] ?? null;

            if (empty($order_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'order_id is required.'));
            }

            $order               = Orders::findOne(['id' => $order_id]);
            $user_conract        = $order->user->contact_no;
            $vendor_contact      = $order->vendorDetails->user->contact_no;
            $MyOperatorComponent = new MyOperatorComponent();
            $makeAnonymousCall   = $MyOperatorComponent->makeAnonymousCall($vendor_contact, $user_conract);

            $data['status']  = self::API_OK;
            $data['details'] = json_decode($makeAnonymousCall);
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionBannersList()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            $post    = Yii::$app->request->post();

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details    = VendorDetails::findOne(['user_id' => $user_id]);
            $vendor_details_id = $vendor_details->id ?? null;
            $search            = $post['search'] ?? null;
            $status            = $post['status'] ?? null;

            // Build query with filters
            $query = Banner::find()->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['in', 'status', [Banner::STATUS_ACTIVE, Banner::STATUS_INACTIVE, Banner::STATUS_PAUSED]]);

            // Filter by status if provided
            if ($status !== null && $status !== '') {
                $query->andWhere(['status' => $status]);
            }

            // Search by title or description (case-insensitive)
            if (! empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'title', $search],
                    ['like', 'description', $search],
                ]);
            }

            // You can add more (e.g. by date, type, etc.)

            $banners = $query->all();
            $list    = [];

            if (! empty($banners)) {
                foreach ($banners as $banner) {
                    $list[] = $banner->asJsonVendor();
                }
            }

            if (! empty($list)) {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            } else {
                $data = [
                    'status' => self::API_NOK,
                    'error'  => 'No banners found for the specified criteria.',
                ];
            }
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewBannerById()
    {
        $data = [];

        try {

            $headers    = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth       = new AuthSettings();
            $user_id    = $auth->getAuthSession($headers);
            $id         = Yii::$app->request->get('id');
            $post       = Yii::$app->request->post();
            $start_date = $post['start_date'] ?? null;
            $end_date   = $post['end_date'] ?? null;

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details    = VendorDetails::findOne(['user_id' => $user_id]);
            $vendor_details_id = $vendor_details->id ?? null;

            if (empty($vendor_details_id)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $banner = Banner::findOne([
                'id'                => $id,
                'vendor_details_id' => $vendor_details_id,
            ]);

            if ($banner === null) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Banner not found.'));
            }

            $data['status']  = self::API_OK;
            $data['details'] = $banner->asJsonVendorView($post);
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionChangeBannerStatus()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $banner_id = Yii::$app->request->post('banner_id') ?? Yii::$app->request->getBodyParam('banner_id');

            $status = Yii::$app->request->post('status') ?? Yii::$app->request->getBodyParam('status');

            if (empty($banner_id) || ! isset($status)) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => 'Missing banner_id or status.',
                ]);
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $banner = Banner::findOne([
                'id'                => $banner_id,
                'vendor_details_id' => $vendor->id,
            ]);

            if (! $banner) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => 'Banner not found or access denied.',
                ]);
            }

            $banner->status = $status;
            if ($banner->save(false)) {
                return $this->asJson([
                    'status'  => self::API_OK,
                    'message' => 'Banner status updated successfully.',
                    'details' => $banner->asJsonVendor(),
                ]);
            } else {
                return $this->asJson([
                    'status'            => self::API_NOK,
                    'error'             => 'Failed to update banner status.',
                    'validation_errors' => $banner->getErrors(),
                ]);
            }
        } catch (\yii\web\HttpException $e) {
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Yii::error("Error in actionChangeBannerStatus: " . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => 'An error occurred while updating banner status.',
            ]);
        }
    }

    public function actionSendEmailOtp()
    {
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User not found or unauthorized.'),
            ];
            return $this->sendJsonResponse($data);
        }

        $post    = Yii::$app->request->post();
        $email   = $post['email'] ?? null;
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) == true;

        if (! $isValid) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Invalid email address.'),
            ]);
        }

        // Check rate limit
        if ($this->isRateLimited($email)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Too many OTP requests. Please try again later.'),
            ]);
        }

        // Generate and hash OTP
        $otp                   = $this->generateOtp();
        $model                 = new EmailOtpVerifications();
        $model->email          = $email;
        $model->otp            = password_hash($otp, PASSWORD_BCRYPT);
        $model->is_verified    = 0;
        $model->status         = EmailOtpVerifications::STATUS_ACTIVE;
        $model->created_on     = date('Y-m-d H:i:s');
        $model->updated_on     = date('Y-m-d H:i:s');
        $model->create_user_id = Yii::$app->user->id ?? null;
        $model->update_user_id = Yii::$app->user->id ?? null;

        // Clean up expired OTPs
        $this->cleanupExpiredOtps($email);

        if (! $model->save(false)) {
            throw new ServerErrorHttpException('Failed to save OTP.');
        }

        // Render email template
        $html = Yii::$app->view->render('@app/modules/api/views/mail/otp', [
            'otp'    => $otp,
            'expiry' => self::OTP_EXPIRY_MINUTES,
        ]);

        // Create mailer message
        $mailer = Yii::$app->mailer->compose()
            ->setFrom(['support@esteticanow.com' => 'EsteticaNow'])
            ->setTo($email)
            ->setSubject('Your OTP Verification Code')
            ->setHtmlBody($html)
            ->setTextBody("Your OTP for verification is: $otp\nThis OTP will expire in " . self::OTP_EXPIRY_MINUTES . " minutes.");

        // Add List-Unsubscribe header for SwiftMailer (only if using SwiftMailer)
        if ($mailer instanceof \yii\swiftmailer\Message) {
            $mailer->getSwiftMessage()->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@esteticanow.com>');
        }

        // Send email
        $sent = $mailer->send();

        if (! $sent) {
            throw new ServerErrorHttpException('Failed to send OTP email.');
        }

        return $this->sendJsonResponse([
            'status'  => self::API_OK,
            'message' => 'OTP sent successfully.',
        ]);
    }

    /**
     * Verifies OTP
     */
    public function actionVerifyEmailOtp()
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

        $post  = Yii::$app->request->post();
        $email = trim($post['email'] ?? '');
        $otp   = trim($post['otp'] ?? '');

        if (empty($email) || empty($otp)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Email and OTP are required.'),
            ]);
        }

        // Validate email format
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Invalid email format.'),
            ]);
        }

        // Check if the email matches with the authenticated user ID
        $user = User::find()->where(['id' => $user_id, 'email' => $email])->one();
        if (! $user) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Email does not match the logged-in user.'),
            ]);
        }

        // Verify OTP
        $result = $this->verifyOtpEmail($email, $otp);
        if (! $result) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'Invalid or expired OTP.'),
            ]);
        }

        // Update verification status
        $user->email_is_verified = 1;
        $user->save(false);

        return $this->sendJsonResponse([
            'status'  => self::API_OK,
            'message' => 'OTP verified successfully.',
        ]);
    }

    /**
     * Private method to verify OTP
     * @param string $email
     * @param string $otp
     * @return bool
     */
    private function verifyOtpEmail($email, $otp)
    {

        $model = EmailOtpVerifications::find()
            ->where(['email' => $email, 'status' => 1])
            ->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', time() - self::OTP_EXPIRY_MINUTES * 60)])
            ->orderBy(['created_on' => SORT_DESC])
            ->one();

        if (! $model) {
            return false;
        }

        if (! password_verify($otp, $model->otp)) {
            return false;
        }

        $model->is_verified    = 1;
        $model->updated_on     = date('Y-m-d H:i:s');
        $model->update_user_id = Yii::$app->user->id ?? null;
        return $model->save(false);
    }

    /**
     * Generate random 6-digit OTP
     * @return string
     */
    private function generateOtp()
    {
        return sprintf("%06d", random_int(100000, 999999));
    }

    /**
     * Check if email has exceeded OTP request limit
     * @param string $email
     * @return bool
     */
    private function isRateLimited($email)
    {
        $count = EmailOtpVerifications::find()
            ->where(['email' => $email])
            ->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', time() - self::RATE_LIMIT_WINDOW)])
            ->count();

        return $count >= self::RATE_LIMIT_ATTEMPTS;
    }

    /**
     * Clean up expired OTPs for an email
     * @param string $email
     */
    private function cleanupExpiredOtps($email)
    {
        EmailOtpVerifications::deleteAll([
            'and',
            ['email' => $email],
            ['status' => 'active'],
            ['<', 'created_on', date('Y-m-d H:i:s', time() - self::OTP_EXPIRY_MINUTES * 60)],
        ]);
    }

    public function actionOrderStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "User authentication failed. Please log in.");
            return $this->sendJsonResponse($data);
        }

        $orderId = Yii::$app->request->post('orderId') ?? Yii::$app->request->get('orderId');

        if (empty($orderId)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Order ID is required.");
            return $this->sendJsonResponse($data);
        }

        try {
            $order = Orders::findOne(['id' => $orderId]);

            if (! $order) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "Order not found.");
                return $this->sendJsonResponse($data);
            }

            $data['status']              = self::API_OK;
            $data['order_id']            = $order->id;
            $data['order_status']        = $order->status;
            $data['payment_status']      = $order->payment_status;
            $data['full_payment_status'] = $order->fill_payment_status;
            $data['is_verify']           = $order->is_verify;
            $data['is_next_visit']       = $order->is_next_visit;
            $data['service_type']        = $order->service_type;
            $data['updated_on']          = $order->updated_on;
        } catch (\Exception $e) {
            Yii::error("Error fetching order status: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "Something went wrong while fetching the order status.");
        }

        return $this->sendJsonResponse($data);
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



    public function actionUpdateAllVendorAddresses()
    {
        $data = [
            'updated' => 0,
            'errors'  => [],
        ];

        try {
            // Load all vendors with lat/lng
            $vendors = VendorDetails::find()
                ->where(['not', ['latitude' => null]])
                ->andWhere(['not', ['longitude' => null]])
                ->all();

            if (empty($vendors)) {
                throw new \yii\web\NotFoundHttpException("No vendors found with latitude/longitude.");
            }

            $googleApiKey = 'AIzaSyD68kxLx285OInWNU7TuSg5QHda1Ih_E_U'; // Replace this with your key

            foreach ($vendors as $vendor) {
                $lat = $vendor->latitude;
                $lng = $vendor->longitude;

                $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key={$googleApiKey}";

                $response    = file_get_contents($url);
                $geocodeData = json_decode($response, true);

                if (! empty($geocodeData['results'][0])) {
                    $components       = $geocodeData['results'][0]['address_components'];
                    $formattedAddress = $geocodeData['results'][0]['formatted_address'];

                    $getComponent = function ($types) use ($components) {
                        foreach ($components as $component) {
                            if (array_intersect($types, $component['types'])) {
                                return $component['long_name'];
                            }
                        }
                        return null;
                    };

                    // Update only address-related fields
                    $vendor->address                = $formattedAddress;
                    $vendor->location_name          = $getComponent(['point_of_interest', 'premise']);
                    $vendor->street                 = $getComponent(['route']);
                    $vendor->iso_country_code       = $getComponent(['country']);
                    $vendor->country                = $getComponent(['country']);
                    $vendor->postal_code            = $getComponent(['postal_code']);
                    $vendor->administrative_area    = $getComponent(['administrative_area_level_1']);
                    $vendor->subadministrative_area = $getComponent(['administrative_area_level_2']);
                    $vendor->locality               = $getComponent(['locality']);
                    $vendor->sublocality            = $getComponent(['sublocality', 'sublocality_level_1']);
                    $vendor->thoroughfare           = $getComponent(['route']);
                    $vendor->subthoroughfare        = $getComponent(['street_number']);
                    $vendor->updated_on             = date('Y-m-d H:i:s');

                    if ($vendor->save(false)) {
                        $data['updated']++;
                    } else {
                        $data['errors'][] = "Failed to update vendor ID {$vendor->id}";
                    }
                } else {
                    $data['errors'][] = "No address found for vendor ID {$vendor->id} ({$lat}, {$lng})";
                }
            }

            $data['status'] = self::API_OK;
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionWhatsappWebhook()
    {
        $request = Yii::$app->request;

        $rawPayload = $request->getRawBody();
        if (is_array($rawPayload)) {
            $rawPayload = json_encode($rawPayload);
        }

        $webhookLog          = new WhatsappWebhookLogs();
        $webhookLog->payload = $rawPayload;
        $webhookLog->status  = 1;
        $webhookLog->save(false);

        try {
            $data = json_decode($rawPayload, true);
            if (! is_array($data) || ! isset($data['entry'][0]['changes'][0]['value'])) {
                throw new \Exception("Invalid or missing value field in webhook payload");
            }

            $entry = $data['entry'][0]['changes'][0]['value'];
            $field = $data['entry'][0]['changes'][0]['field'];

            if ($field === 'messages' && isset($entry['statuses'])) {
                return $this->asJson(['status' => 'status_received']);
            }

            if (! isset($entry['messages'][0])) {
                return $this->asJson(['status' => 'no_message']);
            }

            $msg  = $entry['messages'][0];
            $from = $msg['from'];
            $text = strtolower(trim($msg['text']['body'] ?? ''));
        } catch (\Throwable $e) {
            $this->logError($from ?? 'unknown', $e->getMessage(), $rawPayload);
            return $this->asJson(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function sendWhatsappTextMessage($to, $text)
    {
        try {
            $settings        = new WebSetting();
            $whatsapp_token  = $settings->getSettingBykey('whatsapp_token');
            $phone_number_id = '734023276451663'; // From Meta Business Suite

            $url = "https://graph.facebook.com/v18.0/{$phone_number_id}/messages";

            $client   = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->addHeaders([
                    'Authorization' => "Bearer {$whatsapp_token}",
                    'Content-Type'  => 'application/json',
                ])
                ->setContent(json_encode([
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'text'              => ['body' => $text],
                ]))
                ->send();

            // Log API response
            $apiLog               = new WhatsappApiLogs();
            $apiLog->phone_number = $to;
            $apiLog->payload      = json_encode(['to' => $to, 'text' => $text]);
            $apiLog->response     = $response->content;
            $apiLog->status       = $response->isOk ? 1 : 0;
            $apiLog->save(false);

            Yii::info("Sent WhatsApp text message to {$to}: {$text}", __METHOD__);
            return $response;
        } catch (\Throwable $e) {
            $errorMessage = "Error sending text message to {$to}: " . $e->getMessage();
            $this->logError($to, $errorMessage, null);
            throw $e;
        }
    }

    private function sendWhatsappInteractiveMessage($to, $interactiveData)
    {
        try {
            $settings        = new WebSetting();
            $whatsapp_token  = $settings->getSettingBykey('whatsapp_token');
            $phone_number_id = '734023276451663'; // From Meta Business Suite

            $url = "https://graph.facebook.com/v18.0/{$phone_number_id}/messages";

            $client   = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->addHeaders([
                    'Authorization' => "Bearer {$whatsapp_token}",
                    'Content-Type'  => 'application/json',
                ])
                ->setContent(json_encode([
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'interactive',
                    'interactive'       => $interactiveData,
                ]))
                ->send();

            // Log API response
            $apiLog               = new WhatsappApiLogs();
            $apiLog->phone_number = $to;
            $apiLog->payload      = json_encode(['to' => $to, 'interactive' => $interactiveData]);
            $apiLog->response     = $response->content;
            $apiLog->status       = $response->isOk ? 1 : 0;
            $apiLog->save(false);

            Yii::info("Sent WhatsApp interactive message to {$to}", __METHOD__);
            return $response;
        } catch (\Throwable $e) {
            $errorMessage = "Error sending interactive message to {$to}: " . $e->getMessage();
            $this->logError($to, $errorMessage, null);
            throw $e;
        }
    }

    private function logError($phoneNumber, $errorMessage, $rawPayload = null)
    {
        Yii::error($errorMessage, __METHOD__);
        $apiLog               = new WhatsappApiLogs();
        $apiLog->phone_number = $phoneNumber;
        $apiLog->payload      = json_encode([
            'error'            => $errorMessage,
            'incoming_payload' => $rawPayload,
        ]);
        $apiLog->response = null;
        $apiLog->status   = 0;
        $apiLog->save(false);
    }

    private function getAiSmartReply($text, $userState)
    {
        if (empty($text)) {
            return null;
        }

        // 1. Check predefined triggers
        if (stripos($text, 'facial') !== false) {
            return "We offer a variety of facials! Would you like home visit or walk-in?";
        }

        // 2. Use DB fallback for product/service match
        $matchedService = $this->searchServicesByText($text);
        if (! empty($matchedService)) {
            return "Here’s what we found: \n" . $matchedService;
        }

        // 3. Fallback to OpenAI if no DB match
        return $this->getOpenAiReply($text, $userState->language);
    }

    private function searchServicesByText($text)
    {
        $services = Services::find()
            ->where(['status' => 1])
            ->andWhere([
                'or',
                ['like', 'service_name', $text],
                ['like', 'description', $text],
                ['like', 'benefits', $text],
                ['like', 'why_choose_service', $text],
            ])
            ->limit(3)
            ->all();

        if (empty($services)) {
            return null;
        }

        $response = '';
        foreach ($services as $s) {
            $vendor = $s->vendorDetails;
            $response .= "👉 {$s->service_name} - ₹{$s->price}\n";
            $response .= "⏱ Duration: {$s->duration} mins\n";
            if ($vendor) {
                $response .= "📍 Provider: {$vendor->business_name}\n";
                $response .= "📍 Location: {$vendor->location_name}, {$vendor->city_id}\n";
            }
            $response .= "--------\n";
        }

        return trim($response);
    }

    private function getOpenAiReply($text, $lang = 'en')
    {
        $settings = new WebSetting();
        $apiKey   = $settings->getSettingBykey('openai_api_key');

        $client   = new \yii\httpclient\Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.openai.com/v1/chat/completions')
            ->addHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])
            ->setContent(json_encode([
                'model'    => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => "You are EsteticaNow's WhatsApp assistant. Help users find and book beauty/wellness services in short replies."],
                    ['role' => 'user', 'content' => $text],
                ],
            ]))
            ->send();

        if ($response->isOk) {
            $reply = json_decode($response->content, true);
            return $reply['choices'][0]['message']['content'] ?? null;
        }

        return null;
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

    public function actionStoreAvailableByDate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];
        $headers                    = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                       = new AuthSettings();
        $user_id                    = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $vendor_details_id = $vendor->id;

            // Get all timings for this vendor, key by day_id
            $storeTimings = StoreTimings::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->indexBy('day_id')
                ->all();

            // Get days mapping: id => title (e.g., 1 => 'Monday')
            $daysMap = [];
            $days    = (new \yii\db\Query())->from('days')->all();
            foreach ($days as $day) {
                $daysMap[$day['id']] = $day['title'];
            }

            $startDate = new \DateTime('today');
            $endDate   = (new \DateTime('today'))->modify('+29 days');
            $result    = [];

            for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
                $dayOfWeek = $date->format('N'); // 1 (Mon) ... 7 (Sun)
                $dayName   = $daysMap[$dayOfWeek];

                // Available if there is timing and status == 1
                $available = (isset($storeTimings[$dayOfWeek]) && $storeTimings[$dayOfWeek]->status == 1);

                $result[] = [
                    'date'      => $date->format('Y-m-d'),
                    'day'       => $dayName,
                    'available' => $available,
                ];
            }

            $data['status']  = self::API_OK;
            $data['details'] = $result;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred. ' . $e->getMessage());
        }

        return $this->sendJsonResponse($data);
    }

    public function actionReelsDashboard()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Get vendor details
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }
            $vendor_details_id = $VendorDetails->id;

            // Aggregate stats for ACTIVE reels
            $activeStats = (new \yii\db\Query())
                ->from('reels')
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => Reels::STATUS_ACTIVE])
                ->select([
                    'total_views'  => 'SUM(view_count)',
                    'total_likes'  => 'SUM(like_count)',
                    'total_shares' => 'SUM(share_count)',
                    'active_reels' => 'COUNT(*)',
                ])
                ->one();

            // Optional: total reels regardless of status
            $totalReels = (new \yii\db\Query())
                ->from('reels')
                ->where(['vendor_details_id' => $vendor_details_id])
                ->count();

            $data['status']    = self::API_OK;
            $data['dashboard'] = [
                'total_views'  => (int) ($activeStats['total_views'] ?? 0),
                'total_likes'  => (int) ($activeStats['total_likes'] ?? 0),
                'total_shares' => (int) ($activeStats['total_shares'] ?? 0),
                'active_reels' => (int) ($activeStats['active_reels'] ?? 0),
                'total_reels'  => (int) $totalReels,
            ];
            $data['message'] = Yii::t("app", "Reels dashboard stats loaded.");
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

    public function actionBannersDashboard()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details    = VendorDetails::findOne(['user_id' => $user_id]);
            $vendor_details_id = $vendor_details->id ?? null;

            if (! $vendor_details_id) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No vendor details found for this user.'));
            }

            // Banner status constants (change as per your model)
            $statusActive  = Banner::STATUS_ACTIVE ?? 1;
            $statusPending = Banner::STATUS_PENDING ?? 0;

            // 1. Banner counts
            $activeBanners  = Banner::find()->where(['vendor_details_id' => $vendor_details_id, 'status' => $statusActive])->count();
            $pendingBanners = Banner::find()->where(['vendor_details_id' => $vendor_details_id, 'status' => $statusPending])->count();
            $totalBanners   = Banner::find()->where(['vendor_details_id' => $vendor_details_id])->count();

            // 2. Banner IDs
            $bannerIds = Banner::find()->select('id')->where(['vendor_details_id' => $vendor_details_id])->column();

            // 3. Totals from banner_charge_logs
            $totalViews  = 0;
            $totalClicks = 0;

            if ($bannerIds) {
                $totalViews = (int) BannerChargeLogs::find()
                    ->where(['banner_id' => $bannerIds, 'action' => 'view'])
                    ->count();

                $totalClicks = (int) BannerChargeLogs::find()
                    ->where(['banner_id' => $bannerIds, 'action' => 'click'])
                    ->count();
            }

            // 4. CTR calculation
            $ctr = ($totalViews > 0) ? round(($totalClicks / $totalViews) * 100, 2) : 0.00;

            $data['status']    = self::API_OK;
            $data['dashboard'] = [
                'active_banners'  => (int) $activeBanners,
                'pending_banners' => (int) $pendingBanners,
                'total_banners'   => (int) $totalBanners,
                'total_views'     => (int) $totalViews,
                'total_clicks'    => (int) $totalClicks,
                'ctr_percent'     => $ctr, // e.g., 12.34 means 12.34%
            ];
            $data['message'] = Yii::t('app', 'Banners dashboard stats loaded.');
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCouponsDashboard()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No vendor details found for this user.'));
            }
            $vendor_details_id = $vendor->id;

            // 1. Active coupons count for this vendor
            $activeCoupons = (new \yii\db\Query())
                ->from('coupon_vendor')
                ->innerJoin('coupon', 'coupon_vendor.coupon_id = coupon.id')
                ->where([
                    'coupon_vendor.vendor_details_id' => $vendor_details_id,
                    'coupon.status'                   => 1, // Or your STATUS_ACTIVE constant
                    'coupon_vendor.status'            => 1,
                ])
                ->andWhere(['<=', 'coupon.start_date', date('Y-m-d H:i:s')])
                ->andWhere([
                    'or',
                    ['>=', 'coupon.end_date', date('Y-m-d H:i:s')],
                    ['coupon.end_date' => null],
                ])
                ->count();

            // 2. Get all coupon IDs for this vendor
            $couponIds = (new \yii\db\Query())
                ->select('coupon_id')
                ->from('coupon_vendor')
                ->where(['vendor_details_id' => $vendor_details_id])
                ->column();

            $redeemedCoupons    = 0;
            $totalDiscountGiven = 0;
            $averageDiscount    = 0;

            if (! empty($couponIds)) {
                // 3. Redeemed count from coupons_applied
                $redeemedCoupons = (new \yii\db\Query())
                    ->from('coupons_applied')
                    ->where(['coupon_id' => $couponIds, 'status' => 1])
                    ->count();

                // 4. Total discount given: join with coupon to get discount value
                $rows = (new \yii\db\Query())
                    ->select(['c.discount'])
                    ->from(['ca' => 'coupons_applied'])
                    ->leftJoin(['c' => 'coupon'], 'ca.coupon_id = c.id')
                    ->where(['ca.coupon_id' => $couponIds, 'ca.status' => 1])
                    ->all();

                $totalDiscountGiven = array_sum(array_column($rows, 'discount'));
                $averageDiscount    = $redeemedCoupons > 0 ? round($totalDiscountGiven / $redeemedCoupons, 2) : 0;
            }

            $data['status']    = self::API_OK;
            $data['dashboard'] = [
                'active_coupons'         => (int) $activeCoupons,
                'redeemed_coupons'       => (int) $redeemedCoupons,
                'total_discount_given'   => (float) $totalDiscountGiven,
                'average_discount_given' => (float) $averageDiscount,
            ];
            $data['message'] = Yii::t('app', 'Coupons dashboard stats loaded.');
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCouponChangeStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }
            $status    = $post['status'] ?? null;
            $coupon_id = $post['coupon_id'] ?? null;

            // Validate status
            if (! in_array($status, [Coupon::STATUS_ACTIVE, Coupon::STATUS_INACTIVE])) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Invalid status value.'));
            }
            if (empty($coupon_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Coupon ID is required.'));
            }

            $coupon = Coupon::findOne(['id' => $coupon_id]);
            if (! $coupon) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Coupon not found.'));
            }

            $coupon->status = $status;
            if (! $coupon->save(false)) {
                throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update coupon status.'));
            }

            // Update all vendor links for this coupon
            $coupon_vendors = CouponVendor::find()->where(['coupon_id' => $coupon_id])->all();
            if (empty($coupon_vendors)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Coupon vendor(s) not found.'));
            }
            foreach ($coupon_vendors as $coupon_vendor) {
                $coupon_vendor->status = $status;
                if (! $coupon_vendor->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update coupon vendor status.'));
                }
            }

            $data = [
                'status'     => self::API_OK,
                'message'    => Yii::t('app', 'Coupon status updated successfully.'),
                'coupon_id'  => $coupon_id,
                'new_status' => $status,
            ];
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionViewBusinessDetailsById()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $data['status']  = self::API_OK;
            $data['details'] = $vendorDetails->asJsonVendor();
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionChangeReelStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $reel_id = $post['reel_id'] ?? null;
            $status  = $post['status'] ?? null; // Should be 1 or 2

            // Validate
            if (empty($reel_id) || ! in_array($status, [Reels::STATUS_ACTIVE, Reels::STATUS_INACTIVE])) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Invalid reel ID or status.'));
            }

            // Find the vendor's reel and validate ownership
            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $VendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No vendor details found for this user.'));
            }

            $reel = Reels::findOne(['id' => $reel_id, 'vendor_details_id' => $VendorDetails->id]);
            if (! $reel) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Reel not found.'));
            }

            $reel->status = $status;
            if (! $reel->save(false)) {
                throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update reel status.'));
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t('app', 'Reel status updated successfully.');
            $data['details'] = [
                'reel_id'    => $reel_id,
                'new_status' => $status,
            ];
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', [
                    'message' => $e->getMessage(),
                ]),
            ];
        }

        return $this->sendJsonResponse($data);
    }
    public function actionCreateCatlog()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            $model          = new VendorDetails();
            $model->user_id = $user_id;

            $model->load(Yii::$app->request->post(), '');

            $uploadImage = UploadedFile::getInstance($model, 'catalog_file');

            if (! $model->validate(['user_id'])) {
                throw new \Exception('Validation failed for user_id.');
            }

            if ($uploadImage) {
                // Upload the image
                $image = Yii::$app->notification->imageKitUpload($uploadImage);
                if (! empty($image['catalog_file'])) {
                    $model->file_url = $image['catalog_file'];
                } else {
                    throw new \Exception('Failed to upload file to ImageKit.');
                }
            }

            // Save model without re-validation (already validated `user_id`)
            if ($model->save(false)) {
                $data['status']    = self::API_OK;
                $data['message']   = 'File uploaded successfully.';
                $data['upload_id'] = $model->id;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Failed to save upload to the database.';
                $data['errors'] = $model->getErrors();
            }
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('Error uploading file: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionApplyServiceDiscount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $post             = Yii::$app->request->post();
        $order_details_id = $post['order_details_id'] ?? null;
        $discount_type    = $post['discount_type'] ?? null;
        $discount         = $post['discount'] ?? null;

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            if (empty($order_details_id) || $discount_type === null || $discount === null) {
                throw new \yii\web\BadRequestHttpException('Missing required parameters.');
            }

            $order_details = OrderDetails::findOne($order_details_id);
            if (! $order_details) {
                throw new \yii\web\NotFoundHttpException('Order details not found.');
            }

            // Validate discount_type
            if (! in_array($discount_type, [OrderDetails::DISCOUNT_TYPE_PERCENTAGE, OrderDetails::DISCOUNT_TYPE_FIXED])) {
                throw new \yii\web\BadRequestHttpException('Invalid discount type.');
            }

            // Validate discount value
            if (! is_numeric($discount) || $discount < 0) {
                throw new \yii\web\BadRequestHttpException('Discount must be a positive number.');
            }

            // Calculate discount_amount
            $discount_amount = 0;
            if ($discount_type == OrderDetails::DISCOUNT_TYPE_PERCENTAGE) {
                if ($discount > 100) {
                    throw new \yii\web\BadRequestHttpException('Percentage discount cannot exceed 100%.');
                }
                $discount_amount = round(($order_details->price * $discount) / 100, 2);
            } elseif ($discount_type == OrderDetails::DISCOUNT_TYPE_FIXED) {
                $discount_amount = min($discount, $order_details->price);
            }

            // Calculate total price after discount, never below zero
            $total_price = max($order_details->price - $discount_amount, 0);

            $order_details->discount_type   = $discount_type;
            $order_details->discount        = $discount;
            $order_details->discount_amount = $discount_amount;
            $order_details->total_price     = $total_price;

            if (! $order_details->save(false)) {
                throw new \Exception('Failed to apply discount: ' . implode(', ', $order_details->getFirstErrors()));
            }

            Orders::recalculateOrderPrice($order_details->order_id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);

            $data['status']  = self::API_OK;
            $data['message'] = 'Discount applied successfully.';
            $data['details'] = $order_details->asJson();
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::error('Bad request: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('Error applying discount: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionMarkAsPaid()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];
        $headers                    = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                       = new AuthSettings();
        $user_id                    = $auth->getAuthSession($headers);
        $post                       = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // Validate required fields
            $order_id = $post['order_id'] ?? null;
            if (empty($order_id)) {
                throw new \yii\web\BadRequestHttpException('Order ID is required.');
            }

            // Find order with proper vendor validation
            $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $user_id]);
            if (! $order) {
                throw new \yii\web\NotFoundHttpException('Order not found or you do not have permission to access it.');
            }

            // Recalculate order price to ensure accuracy
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();

            // Validate payable amount
            if ($order->payable_amount <= 0) {
                throw new \yii\web\BadRequestHttpException('Cannot create transaction: Order payable amount must be greater than 0. Current amount: ' . $order->payable_amount);
            }

            // Create transaction details
            $order_transaction_details               = new OrderTransactionDetails();
            $order_transaction_details->order_id     = $order->id;
            $order_transaction_details->amount       = $order->payable_amount;
            $order_transaction_details->order_type   = 1;
            $order_transaction_details->payment_type = OrderTransactionDetails::PAYMENT_SOURCE_COD;
            $order_transaction_details->status       = OrderTransactionDetails::STATUS_SUCCESS;
            if (! $order_transaction_details->save(false)) {
                throw new \yii\web\ServerErrorHttpException('Failed to create transaction details: ' . implode(', ', $order_transaction_details->getFirstErrors()));
            }

            // Update order payment status
            $order->payment_status = Orders::PAYMENT_DONE;

            if (! $order->save(false)) {
                throw new \yii\web\ServerErrorHttpException('Failed to update order payment status.');
            }

            // Refresh order after update
            $order->refresh();

            // Final recalculation (if needed)
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);

            // Success response
            $data['status']         = self::API_OK;
            $data['message']        = 'Order marked as paid successfully.';
            $data['order_id']       = $order->id;
            $data['transaction_id'] = $order_transaction_details->id;
            $data['amount']         = $order->payable_amount;
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::error('Bad request: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ServerErrorHttpException $e) {
            Yii::error('Server error: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error('Error marking order as paid: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred while processing payment.';
        }

        return $this->sendJsonResponse($data);
    }

    //its not using this need to check 

    public function actionApplyOrderDiscount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = [];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // Validate required fields
            $order_id      = $post['order_id'] ?? null;
            $discount_type = $post['discount_type'] ?? null;
            $discount      = $post['discount'] ?? null;
            $discount_code = $post['discount_code'] ?? null;

            if (empty($order_id) || empty($discount_type) || $discount === null || $discount === '') {
                throw new \yii\web\BadRequestHttpException('Order ID, discount type, and discount amount are required.');
            }

            // Validate discount type
            if (!in_array($discount_type, [OrderDiscounts::DISCOUNT_TYPE_FIXED, OrderDiscounts::DISCOUNT_TYPE_PERCENTAGE])) {
                throw new \yii\web\BadRequestHttpException('Invalid discount type.');
            }

            // Find the order
            $vendor_details_id = User::getVendorIdByUserId($user_id);
            $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $vendor_details_id]);
            if (!$order) {
                throw new \yii\web\NotFoundHttpException('Order not found or you do not have permission to access it.');
            }

            // Get total paid so far
            $order_transaction_details = OrderTransactionDetails::find()
                ->where(['order_id' => $order_id])
                ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');
            $order_transaction_details = floatval($order_transaction_details);

            // Calculate remaining payable before discount
            $order_total = $order->sub_total;
            $final_payable = $order_total - $order_transaction_details;

            if ($final_payable <= 0) {
                throw new \yii\web\BadRequestHttpException('No outstanding amount. Discount cannot be applied.');
            }

            // Calculate discount amount
            if ($discount_type == OrderDiscounts::DISCOUNT_TYPE_PERCENTAGE) {
                if ($discount < 0 || $discount > 100) {
                    throw new \yii\web\BadRequestHttpException('Percentage discount must be between 0 and 100.');
                }
                $discount_amount = ($final_payable * $discount) / 100;
            } else { // Fixed
                if ($discount < 0 || $discount > $final_payable) {
                    throw new \yii\web\BadRequestHttpException('Fixed discount must be between 0 and the remaining payable amount.');
                }
                $discount_amount = $discount;
            }

            // Ensure the final payable amount after discount is not negative
            $final_payable_after_discount = $final_payable - $discount_amount;
            if ($final_payable_after_discount < 0) {
                throw new \yii\web\BadRequestHttpException('Discount not applicable: discount exceeds the remaining payable amount.');
            }

            // Create or update the order discount
            $order_discount = OrderDiscounts::findOne(['order_id' => $order_id]);
            if (!$order_discount) {
                $order_discount = new OrderDiscounts();
                $order_discount->order_id = $order_id;
            }

            $order_discount->discount_type   = $discount_type;
            $order_discount->discount_amount = $discount_amount;
            $order_discount->discount_code   = $discount_code;
            if (!$order_discount->save(false)) {
                throw new \yii\web\ServerErrorHttpException('Failed to save order discount.');
            }

            // Recalculate final price and refresh order object
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();

            $data['status'] = self::API_OK;
            $data['discount_amount'] = $discount_amount;
            $data['final_payable_before_discount'] = $final_payable;
            $data['final_payable_after_discount'] = $final_payable_after_discount;
            $data['already_paid'] = $order_transaction_details;
            $data['order_id'] = $order_id;
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
            $data['error_code'] = $e->statusCode ?? 400;
            Yii::error('Error applying order discount: ' . $e->getMessage(), __METHOD__);
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred while applying the discount.';
            $data['error_code'] = $e->getCode() ?: 500;
            Yii::error([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetAllServicesListForNextVisit()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = null;

        try {
            // ✅ Authenticate user
            $user_id = $auth->getAuthSession($headers);
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // ✅ Get vendor details
            $vendor_details_id = VendorDetails::getVendorDetailsByUserId($user_id);
            if (empty($vendor_details_id)) {
                throw new \yii\web\NotFoundHttpException('Vendor details not found for the user.');
            }

            // ✅ Fetch services
            $services = Services::find()
                ->alias('s')
                ->joinWith(['subCategory.mainCategory sm'])
                ->where(['s.vendor_details_id' => $vendor_details_id])
                ->andWhere(['s.status' => Services::STATUS_ACTIVE])
                ->andWhere([
                    'or',
                    ['s.is_parent_service' => null],
                    ['s.is_parent_service' => 0],
                    ['not', ['s.parent_id' => null]]
                ])
                ->andWhere(['sm.is_scheduled_next_visit' => 1])
                ->all();


            $data['status'] = self::API_OK;
            $data['data']   = $services;
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::warning('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::warning('Not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) { // catches all PHP7+ errors & exceptions
            Yii::error('Error fetching services list: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred while fetching the services list.';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionTestWhatsapp()
    {
        $id = 4654;
        $order = Orders::find()->where(['id' => $id])->one();
        return $order;

        return   WhatsApp::sendTemplate($order->user->contact_no, 'estetica_order_accepted', [
            'param_1'   => $order->vendorDetails->business_name,
            'param_2'   => '' . $order->getOrderServicesAsString(),
            'param_3'   => $order->schedule_time,
            'param_4'   => $order->vendorDetails->address,
        ]);
    }
}
