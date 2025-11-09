<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\components\MyOperatorComponent;
use app\components\Razorpay;
use app\components\WebsocketEmitter;
use app\components\WhatsApp;
use app\models\User;
use app\modules\admin\models\Auth;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\Banner;
use app\modules\admin\models\BannerChargeLogs;
use app\modules\admin\models\base\MainCategory;
use app\modules\admin\models\base\SubCategory;
use app\modules\admin\models\BusinessImages;
use app\modules\admin\models\BypassNumbers;
use app\modules\admin\models\Cart;
use app\modules\admin\models\CartItems;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\ComboPackagesCart;
use app\modules\admin\models\ComboServices;
use app\modules\admin\models\Coupon;
use app\modules\admin\models\CouponsApplied;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\Days;
use app\modules\admin\models\DeliveryAddress;
use app\modules\admin\models\EmailOtpVerifications;
use app\modules\admin\models\FcmNotification;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\ProductOrders;
use app\modules\admin\models\ProductServiceOrderMappings;
use app\modules\admin\models\QuizUserAnswers;
use app\modules\admin\models\Quizzes;
use app\modules\admin\models\ReelReports;
use app\modules\admin\models\Reels;
use app\modules\admin\models\ReelShareCounts;
use app\modules\admin\models\ReelsLikes;
use app\modules\admin\models\ReelsViewCounts;
use app\modules\admin\models\search\VendorDetailsSearch;
use app\modules\admin\models\ServiceHasCoupons;
use app\modules\admin\models\Services;
use app\modules\admin\models\ServiceType;
use app\modules\admin\models\ShopLikes;
use app\modules\admin\models\ShopReview;
use app\modules\admin\models\Staff;
use app\modules\admin\models\StoreServiceTypes;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\Subscriptions;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\Wallet;
use app\modules\admin\models\WebSetting;
use app\modules\api\controllers\BKController;
use Exception;
use Mpdf\Mpdf;
use yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UploadedFile;

class UserController extends BKController
{

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
                            'my-profile',
                            'update-profile',
                            'update-location',
                            'category',
                            'banners',
                            'sub-category',
                            'near-by-shops',
                            'recommended',
                            'offers',
                            'store-profile',
                            'get-services-by-vendor-id',
                            'add-to-cart',
                            'view-cart',
                            'delete-cart',
                            'delete-cart-item',
                            'delete-cart-item-by-service-id',
                            'check-out',
                            'update-cart-address',
                            'get-service-type-by-vendor-id',
                            'available-slots',
                            'list-coupons',
                            'apply-coupon',
                            'remove-coupon',
                            'check-cart',
                            'cash-mode',
                            'wallet-mode',
                            'online-mode',
                            'reel',
                            'like-reel',
                            'share-reel',
                            'my-orders',
                            'up-coming-my-orders',
                            'write-a-review',
                            'get-user-reviews',
                            'cancel-booking',
                            'book-again',
                            'get-booking-details-by-id',
                            'favorite-shop',
                            'my-favorite-shops',
                            'wallet-transactions',
                            'order-conformation',
                            'delivery-address-list',
                            'add-or-update-delivery-address',
                            'view-order-by-id',
                            'list-store-reviews',
                            'notifications',
                            'read-notification',
                            'payment-conformation-webhooks',
                            'get-available-balance',
                            'home-search',
                            'login',
                            'get-store-details',
                            'create-qr-order',
                            'create-subscription-order',
                            'verify-qr-payment',
                            'get-subscriptions-list',
                            'store-details',
                            'vendor-based-sub-category',
                            'store-services',
                            'premium-sub-category',
                            'auto-cancel-orders',
                            'test-notification',
                            'test-mode',
                            'scan-pay-history',
                            'delete-address',
                            'save-address',
                            'update-address',
                            'my-address',
                            'disable-expired-orders',
                            'create-wallet-payment-order',
                            'verify-wallet-payment',
                            'service-type',
                            'near-by-shops-with-filters',
                            'report-reel',
                            'clear-notifications',
                            'partial-payment-orders',
                            'pending-amount-online-mode',
                            'wallet-mode-pending-payment',
                            'run-skin-analysis',
                            'staff-by-store-id',
                            'store-gallery',
                            'store-services-types',
                            'combo-packs',
                            'add-to-cart-combo',
                            'remove-cart-combo',
                            'get-offers-by-store-id',
                            'make-call-to-vendor',
                            'download-invoice',
                            'banner-click-count',
                            'banner-view-count',
                            'update-order-rating-flag',
                            'quizzes',
                            'save-quizzes-response',
                            'delete-cart-combo-packages',
                            'apply-referral-code',
                            'skip-referral',
                            'add-to-cart-web',
                            'send-email-otp',
                            'verify-email-otp',
                            'test-template',
                            'reset-all-orders'

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
                            'my-profile',
                            'update-profile',
                            'update-location',
                            'category',
                            'banners',
                            'sub-category',
                            'near-by-shops',
                            'recommended',
                            'offers',
                            'store-profile',
                            'get-services-by-vendor-id',
                            'get-service-type-by-vendor-id',
                            'add-to-cart',
                            'view-cart',
                            'delete-cart',
                            'delete-cart-item',
                            'delete-cart-item-by-service-id',
                            'check-out',
                            'update-cart-address',
                            'available-slots',
                            'list-coupons',
                            'apply-coupon',
                            'remove-coupon',
                            'check-cart',
                            'cash-mode',
                            'wallet-mode',
                            'online-mode',
                            'reel',
                            'like-reel',
                            'share-reel',
                            'create-subscription-order',
                            'my-orders',
                            'up-coming-my-orders',
                            'write-a-review',
                            'get-user-reviews',
                            'cancel-booking',
                            'book-again',
                            'get-booking-details-by-id',
                            'favorite-shop',
                            'my-favorite-shops',
                            'wallet-transactions',
                            'order-conformation',
                            'delivery-address-list',
                            'add-or-update-delivery-address',
                            'view-order-by-id',
                            'list-store-reviews',
                            'notifications',
                            'read-notification',
                            'payment-conformation-webhooks',
                            'get-available-balance',
                            'home-search',
                            'login',
                            'get-store-details',
                            'create-qr-order',
                            'create-subscription-order',
                            'verify-qr-payment',
                            'get-subscriptions-list',
                            'store-details',
                            'vendor-based-sub-category',
                            'store-services',
                            'premium-sub-category',
                            'auto-cancel-orders',
                            'test-notification',
                            'test-mode',
                            'scan-pay-history',
                            'delete-address',
                            'save-address',
                            'update-address',
                            'my-address',
                            'disable-expired-orders',
                            'create-wallet-payment-order',
                            'verify-wallet-payment',
                            'service-type',
                            'near-by-shops-with-filters',
                            'report-reel',
                            'clear-notifications',
                            'partial-payment-orders',
                            'pending-amount-online-mode',
                            'wallet-mode-pending-payment',
                            'run-skin-analysis',
                            'staff-by-store-id',
                            'store-gallery',
                            'store-services-types',
                            'combo-packs',
                            'add-to-cart-combo',
                            'remove-cart-combo',
                            'get-offers-by-store-id',
                            'make-call-to-vendor',
                            'download-invoice',
                            'banner-click-count',
                            'banner-view-count',
                            'update-order-rating-flag',
                            'quizzes',
                            'save-quizzes-response',
                            'delete-cart-combo-packages',
                            'apply-referral-code',
                            'skip-referral',
                            'add-to-cart-web',
                            'send-email-otp',
                            'verify-email-otp',
                            'test-template',
                            'reset-all-orders'


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

        $data['details'] = ['Hi'];
        return $this->sendJsonResponse($data);
    }

    private function isRateLimited($email)
    {
        $count = EmailOtpVerifications::find()
            ->where(['email' => $email])
            ->andWhere(['>=', 'created_on', date('Y-m-d H:i:s', time() - EmailOtpVerifications::RATE_LIMIT_WINDOW)])
            ->count();

        return $count >= EmailOtpVerifications::RATE_LIMIT_ATTEMPTS;
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
                $user = User::find()->where(['id' => $auth_session->create_user_id])->andWhere(['status' => User::STATUS_ACTIVE])
                    ->andWhere(['user_role' => User::ROLE_USER])
                    ->one();
                if (! empty($user)) {
                    $user           = $auth_session->createUser;
                    $data['status'] = self::API_OK;
                    if (is_numeric($user->date_of_birth)) {
                        $data['detail']['date_of_birth'] = date('d-m-Y', $user->date_of_birth);
                    } else {
                        $data['detail']['date_of_birth'] = 'Invalid date';
                    }

                    $data['detail'] = $user->asJsonUser();
                    if (isset($_POST['AuthSession'])) {
                        $auth_session->device_token = $_POST['AuthSession']['device_token'];
                        if ($auth_session->save()) {
                            $data['auth_session'] = Yii::t("app", 'Auth Session updated');
                        } else {
                            $data['error'] = $auth_session->flattenErrors;
                        }
                    }
                } else {
                    $auth_session->delete();
                    if (Yii::$app->user->logout(false)) {
                        $data['status'] = self::API_NOK;
                        $data['error']  = Yii::t("app", 'Sorry your account has been blocked please contact to admin');
                    }
                }
            } else {
                $data['error'] = Yii::t("app", 'session not found');
            }
        } else {
            $data['error'] = Yii::t("app", 'Auth code not found');
            $data['auth']  = isset($auth_code) ? $auth_code : '';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionSendOtp()
    {
        $data = [];
        try {
            $post = Yii::$app->request->post();

            // Check if data is posted
            if (empty($post) || empty($post['contact_no'])) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'No data posted or contact number missing.'));
            }

            $contact_no = $post['contact_no'];

            // Call the OTP service to send OTP
            $send_otp_response = Yii::$app->notification->sendOtp($contact_no);
            $send_otp          = json_decode($send_otp_response, true);

            // Validate the OTP response
            if (isset($send_otp['Status']) && $send_otp['Status'] == 'Success') {
                $data['status']  = self::API_OK;
                $data['details'] = $send_otp;
            } else {
                $error_message  = $send_otp['Details'] ?? Yii::t('app', 'OTP failed.');
                $data['status'] = self::API_NOK;
                $data['error']  = $error_message;
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle validation errors
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            // Handle general exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionResendOtp()
    {
        $data = [];
        $post = Yii::$app->request->post();
        if (! empty($post)) {
            $contact_no = $post['contact_no'];
            $send_otp   = Yii::$app->notification->resendOtp($contact_no);
            $send_otp   = json_decode($send_otp, true);

            if ($send_otp['Status'] == 'Success') {
                $data['status']  = self::API_OK;
                $data['details'] = $send_otp;
            } else {

                Yii::error("OTP resend failed: " . json_encode($send_otp));

                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "OTP resend failed");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No data posted");
        }
        return $this->sendJsonResponse($data);
    }

    public function actionVerifyOtp()
    {
        $data = [];
        $post = Yii::$app->request->post();

        try {
            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t('app', 'No data posted.'));
            }

            $contact_no   = $post['contact_no'] ?? null;
            $session_code = $post['session_code'] ?? null;
            $otp_code     = $post['otp_code'] ?? null;
            $device_type  = $post['device_type'] ?? null;

            if (! $contact_no || ! $session_code || ! $otp_code) {
                throw new BadRequestHttpException(Yii::t('app', 'Missing required parameters.'));
            }

            // Verify OTP with external service
            $send_otp = Yii::$app->notification->verifyOtp($session_code, $otp_code);
            $send_otp = json_decode($send_otp, true);

            $bypass_numbers = BypassNumbers::find()->where(['mobile_number' => $contact_no])->one();

            // Bypass the OTP verification for specific numbers 
            if (! empty($bypass_numbers->mobile_number) && $bypass_numbers->mobile_number == $contact_no) {
                $send_otp['Status'] = 'Success';
            }

            if ($send_otp['Status'] == 'Success') {

                // User lookup or creation
                $providerId = User::ROLE_USER;
                $auth       = Auth::findOne(['source' => $providerId, 'source_id' => $contact_no]);

                if ($auth) {
                    $user = $auth->user;
                    if ($user->status === User::STATUS_ACTIVE) {
                        $user->device_token = $post['device_token'] ?? null;
                        $user->device_type  = $device_type;
                        $user->save(false);



                        if ($auth->save(false)) {
                            Yii::error("Failed to create auth record: " . json_encode($auth->getErrors()));
                        }
                        Yii::$app->user->login($user);
                        $data['status']    = self::API_OK;
                        $data['details']   = $user->asJsonUser();
                        $data['auth_code'] = AuthSession::newSession($user)->auth_code;
                    } else {
                        throw new ForbiddenHttpException(Yii::t('app', 'User account is inactive or blocked.'));
                    }
                } else {

                    $existingUser = User::findOne(['contact_no' => $contact_no, 'user_role' => User::ROLE_USER]);
                    if ($existingUser) {
                        // User exists but no auth record - create the missing auth record
                        $auth            = new Auth();
                        $auth->user_id   = $existingUser->id;
                        $auth->source    = $providerId;
                        $auth->source_id = $existingUser->id;
                        if ($auth->save(false)) {
                            Yii::$app->user->login($existingUser);
                            $data['status']    = self::API_OK;
                            $data['details']   = $existingUser->asJsonUser();
                            $data['auth_code'] = AuthSession::newSession($existingUser)->auth_code;
                        } else {
                            throw new ServerErrorHttpException(Yii::t('app', 'Failed to create auth session.'));
                        }
                    } else {
                        // Register a new user
                        $newUser    = new User();
                        $newUser->username       = $contact_no . '@' . User::ROLE_USER . '.com';
                        $newUser->contact_no     = $contact_no;
                        $newUser->unique_user_id = User::generateUniqueUserId();

                        $newUser->device_token  = $post['device_token'] ?? null;
                        $newUser->device_type   = $post['device_type'] ?? null;
                        $newUser->referral_code = User::generateUniqueReferralCode();
                        $newUser->user_role     = User::ROLE_USER;

                        if ($newUser->save(false)) {
                            $newAuth            = new Auth();
                            $newAuth->user_id   = $newUser->id;
                            $newAuth->source    = $providerId;
                            $newAuth->source_id = $contact_no;
                            if ($newAuth->save(false)) {
                                Yii::$app->user->login($newUser);

                                $data['status']    = self::API_OK;
                                $data['details']   = $newUser->asJsonUser();
                                $data['auth_code'] = AuthSession::newSession($newUser)->auth_code;
                            } else {
                                throw new ServerErrorHttpException(Yii::t('app', 'Failed to create auth session.'));
                            }
                        } else {
                            throw new ServerErrorHttpException(Yii::t('app', 'Failed to register new user.'));
                        }
                    }
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = $send_otp['Details'] ?? 'OTP verification failed';
                return $this->sendJsonResponse($data);
            }
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ForbiddenHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ConflictHttpException $e) {
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

    public function actionUpdateLocation()
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

            $model = User::findOne(['id' => $user_id, 'user_role' => User::ROLE_USER]);
            if (! $model) {
                throw new NotFoundHttpException(Yii::t('app', 'User not found.'));
            }

            // Update location based on latitude and longitude
            if (! empty($post['lat']) && ! empty($post['lng'])) {
                $model->lat = $post['lat'];
                $model->lng = $post['lng'];
            } else {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and longitude are required.'));
            }

            if ($model->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $model->asJsonUser();
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to update location. Please try again.'));
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
            $data['details'] = $model->asJsonUser();
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

    public function actionUpdateProfile()
    {
        $data      = [];
        $post      = Yii::$app->request->post();
        $auth_code = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($auth_code);

        if (empty($user_id)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User not found or unauthorized.');
            return $this->sendJsonResponse($data);
        }

        if (empty($post)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'No data posted.');
            return $this->sendJsonResponse($data);
        }

        $model = User::findOne(['id' => $user_id, 'user_role' => User::ROLE_USER]);
        if (! $model) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User not found.');
            return $this->sendJsonResponse($data);
        }

        $sendWelcomeMessage = false;

        // Update first name
        if (! empty($post['first_name'])) {
            $model->first_name = $post['first_name'];
            if ((int) $model->update_profile_count < 1) {
                $sendWelcomeMessage = true;
            }
        }

        // Email check
        if (! empty($post['email'])) {
            $existingUser = User::find()
                ->where(['email' => $post['email']])
                ->andWhere(['!=', 'id', $user_id])
                ->one();

            if ($existingUser) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'This email is already taken.';
                return $this->sendJsonResponse($data);
            }

            $model->email = $post['email'];
        }

        // Gender
        if (! empty($post['gender'])) {
            $model->gender = $post['gender'];
        }

        // DOB
        if (! empty($post['date_of_birth'])) {
            $model->date_of_birth = Yii::$app->formatter->asDate($post['date_of_birth'], 'php:Y-m-d');
        }

        // Profile image
        if (array_key_exists('profile_image', $post)) {
            $model->profile_image = ! empty($post['profile_image']) ? $post['profile_image'] : null;
        }

        $model->update_profile_count = (int) $model->update_profile_count + 1;

        if ($model->save(false)) {
            if ($sendWelcomeMessage && ! empty($model->first_name)) {
                WhatsApp::sendTemplate($model->contact_no, 'welcome_user', [
                    'image_url' => 'https://ik.imagekit.io/x2nh9ntpo/img_687dc3aa42c03_uE7bpI-E3',
                    'param_1'   => $model->first_name,
                ]);
            }

            $data['status']  = self::API_OK;
            $data['details'] = $model->asJsonUser();
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'Failed to update profile. Please try again.');
        }

        return $this->sendJsonResponse($data);
    }

    //Category

    public function actionCategory()
    {
        $data = [];

        try {
            // Fetch active categories
            $categories = MainCategory::find()->where(['status' => MainCategory::STATUS_ACTIVE])
                ->orderBy(['sortOrder' => SORT_ASC])
                ->all();

            if (! empty($categories)) {
                // Convert categories to JSON
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($category) {
                    return $category->asJson();
                }, $categories);
            } else {
                // Handle no categories found 
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Category Found');
            }
        } catch (\Exception $e) {
            // Handle exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred while fetching categories: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    // Sub Category
    public function actionSubCategory()
    {
        $data   = [];
        $cat_id = Yii::$app->request->get('cat_id');
        $search = Yii::$app->request->get('search');

        try {
            // Ensure the category ID is provided
            if (empty($cat_id)) {
                throw new \yii\web\BadRequestHttpException('Category ID is required.');
            }

            // Fetch active subcategories for the given category ID
            $subCategoryQuery = SubCategory::find()
                ->where(['status' => SubCategory::STATUS_ACTIVE])
                ->andWhere(['main_category_id' => (int) $cat_id])
                ->orderBy(['sortOrder' => SORT_ASC]);

            // Apply search filter if provided
            if (! empty($search)) {
                $subCategoryQuery->andWhere(['like', 'title', $search]); // Apply search on subcategory title
            }

            // Execute the query
            $subCategories = $subCategoryQuery->all();

            if (! empty($subCategories)) {
                // Convert subcategories to JSON format
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($subCategory) {
                    return $subCategory->asJson();
                }, $subCategories);
            } else {
                // Handle no subcategories found
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Sub Category Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle validation errors
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            // Handle general exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionServiceType()
    {
        $data   = [];
        $cat_id = Yii::$app->request->get('cat_id');
        $search = Yii::$app->request->get('search');

        try {
            // Ensure the category ID is provided
            if (empty($cat_id)) {
                throw new \yii\web\BadRequestHttpException('Category ID is required.');
            }

            // Fetch active service types for the given category ID
            $serviceTypeQuery = ServiceType::find()
                ->where(['status' => ServiceType::STATUS_ACTIVE])
                ->andWhere(['main_category_id' => (int) $cat_id])
                ->orderBy(['id' => SORT_ASC]);

            // Apply search filter if provided
            if (! empty($search)) {
                $serviceTypeQuery->andWhere(['like', 'type', $search]); // Apply search on service type title
            }

            // Execute the query
            $serviceTypes = $serviceTypeQuery->all();

            if (! empty($serviceTypes)) {
                // Convert service types to JSON format 
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($serviceType) {
                    return $serviceType->asJson();
                }, $serviceTypes);
            } else {
                // Handle no service types found
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Service Type Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle validation errors
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            // Handle general exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionStoreServices()
    {
        $data    = [];
        $ven_id  = Yii::$app->request->get('vendor_id') ?? Yii::$app->request->post('vendor_id');
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure the vendor ID is provided
            if (empty($ven_id)) {
                throw new \yii\web\BadRequestHttpException('Vendor ID is required.');
            }

            // Fetch active subcategories for the given vendor ID
            $subCategoryQuery = SubCategory::find()
                ->where(['status' => SubCategory::STATUS_ACTIVE])
                ->andWhere(['vendor_details_id' => (int) $ven_id])
                ->orderBy(['sortOrder' => SORT_ASC]);

            // Execute the query
            $subCategories = $subCategoryQuery->all();

            if (! empty($subCategories)) {
                // Convert subcategories to JSON format
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($subCategory) use ($user_id) {
                    return $subCategory->customJson($user_id);
                }, $subCategories);
            } else {
                // Handle no subcategories found
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Sub Category Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle validation errors
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            // Handle general exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionStoreServicesTypes()
    {
        $data             = [];
        $ven_id           = Yii::$app->request->post('vendor_id');
        $main_category_id = Yii::$app->request->post('main_category_id');
        $service_type_id  = Yii::$app->request->post('service_type_id');

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($ven_id)) {
                throw new \yii\web\BadRequestHttpException('Vendor ID is required. and main_category_id(optional) and service_type_id(optional)');
            }

            $query = StoreServiceTypes::find()
                ->where(['status' => StoreServiceTypes::STATUS_ACTIVE])
                ->andWhere(['store_id' => (int) $ven_id]);

            if (! empty($main_category_id)) {
                $query->andWhere(['main_category_id' => (int) $main_category_id]);
            }

            if (! empty($service_type_id)) {
                $query->andWhere(['service_type_id' => (int) $service_type_id]);
            } elseif (! empty($main_category_id)) {
                // Fallback: find all service_type_ids for this category
                $service_type_ids = ServiceType::find()
                    ->select('id')
                    ->where(['status' => ServiceType::STATUS_ACTIVE])
                    ->andWhere(['main_category_id' => (int) $main_category_id])
                    ->column();

                $query->andWhere(['IN', 'service_type_id', $service_type_ids]);
            }

            $store_service_types = $query->all();

            $list = [];
            foreach ($store_service_types as $store_service_types_data) {
                try {
                    $list[] = $store_service_types_data->asJson();
                } catch (\Exception $innerEx) {
                    Yii::error("Failed to format service type ID {$store_service_types_data->id}: " . $innerEx->getMessage(), __METHOD__);
                }
            }

            $data['status']  = self::API_OK;
            $data['details'] = $list;
        } catch (\yii\web\BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\db\Exception $dbEx) {
            Yii::error("DB error in StoreServicesTypes: " . $dbEx->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = "Database error occurred. Please contact support.";
        } catch (\Exception $e) {
            Yii::error("General error in StoreServicesTypes: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = "An unexpected error occurred.";
        }

        return $this->sendJsonResponse($data);
    }

    public function actionNearByShops()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $latitude  = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude = isset($post['longitude']) ? $post['longitude'] : null;
            $page      = ! empty($post['page']) ? $post['page'] : 1;

            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            $params = Yii::$app->request->queryParams;

            $searchModel    = new VendorDetailsSearch();
            $getNearByShops = $searchModel->getNearByShopsBasedOnServiceTypes($params, $post);

            if (! empty($getNearByShops->models)) {
                $result = [];
                foreach ($getNearByShops->models as $model) {
                    $result[] = $model->asJsonNearByShops($user_id, $latitude, $longitude);
                }

                $data['status']       = self::API_OK;
                $data['details']      = $result;
                $data['total_count']  = $getNearByShops->getTotalCount();
                $pageSize             = $getNearByShops->pagination->pageSize ?: 1;
                $data['total_pages']  = ceil($data['total_count'] / $pageSize);
                $data['current_page'] = $page + 1;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No nearby shops found.');
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionRecommended()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $latitude           = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude          = isset($post['longitude']) ? $post['longitude'] : null;
            $page               = ! empty($post['page']) ? $post['page'] : 1;
            $post['is_popular'] = 1;

            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            $params = Yii::$app->request->queryParams;

            $searchModel    = new VendorDetailsSearch();
            $getNearByShops = $searchModel->getNearByShopsBasedOnServiceTypes($params, $post);

            if (! empty($getNearByShops->models)) {
                $result = [];
                foreach ($getNearByShops->models as $model) {
                    $result[] = $model->asJsonNearByShops($user_id, $latitude, $longitude);
                }

                $data['status']       = self::API_OK;
                $data['details']      = $result;
                $data['total_count']  = $getNearByShops->getTotalCount();
                $pageSize             = $getNearByShops->pagination->pageSize ?: 1;
                $data['total_pages']  = ceil($data['total_count'] / $pageSize);
                $data['current_page'] = $page + 1;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No nearby shops found.');
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionOffers()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $latitude         = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude        = isset($post['longitude']) ? $post['longitude'] : null;
            $page             = ! empty($post['page']) ? $post['page'] : 1;
            $post['discount'] = 1;

            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            $params = Yii::$app->request->queryParams;

            $searchModel    = new VendorDetailsSearch();
            $getNearByShops = $searchModel->getNearByShopsWithActiveOffers($params, $post);

            if (! empty($getNearByShops->models)) {
                $result = [];
                foreach ($getNearByShops->models as $model) {
                    $result[] = $model->asJsonNearByShops($user_id, $latitude, $longitude);
                }

                $data['status']       = self::API_OK;
                $data['details']      = $result;
                $data['total_count']  = $getNearByShops->getTotalCount();
                $pageSize             = $getNearByShops->pagination->pageSize ?: 1;
                $data['total_pages']  = ceil($data['total_count'] / $pageSize);
                $data['current_page'] = $page + 1;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No nearby shops found.');
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionNearByShopsWithFilters()
    {

        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $latitude  = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude = isset($post['longitude']) ? $post['longitude'] : null;
            $page      = ! empty($post['page']) ? $post['page'] : 1;

            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            $params = Yii::$app->request->queryParams;

            $searchModel = new VendorDetailsSearch();
            // $getNearByShops = $searchModel->getNearByShops($params, $post); 
            $getNearByShops = $searchModel->getNearByShops($params, $post);

            if (! empty($getNearByShops->models)) {
                $result = [];
                foreach ($getNearByShops->models as $model) {
                    $result[] = $model->asJson($user_id, $latitude, $longitude);
                }

                $data['status']      = self::API_OK;
                $data['details']     = $result;
                $data['total_count'] = $getNearByShops->getTotalCount();
                // $pageSize = $getNearByShops->pagination->pageSize;
                $pageSize             = $getNearByShops->pagination->pageSize ?: 1;
                $data['total_pages']  = ceil($data['total_count'] / $pageSize);
                $data['current_page'] = $page + 1;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No nearby shops found.');
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionStoreProfile()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            // Check if user is authenticated

            // Get the required fields from POST data
            $latitude          = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude         = isset($post['longitude']) ? $post['longitude'] : null;
            $vendor_details_id = isset($post['vendor_details_id']) ? $post['vendor_details_id'] : null;

            // Validate the required fields
            if (empty($vendor_details_id) || empty($latitude) || empty($longitude)) {
                throw new BadRequestHttpException(Yii::t('app', 'vendor_details_id, latitude, and longitude are required.'));
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['id' => $vendor_details_id]);

            // Check if vendor details exist
            if (empty($vendor_details)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // If vendor details are found, format and return the response
            $data['status']  = self::API_OK;
            $data['details'] = $vendor_details->asJsonStoreProfileUserSide($user_id, $latitude, $longitude);
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
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        // Return the response in JSON format
        return $this->sendJsonResponse($data);
    }

    public function actionStoreDetails()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post    = Yii::$app->request->post();
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Get the required fields from POST data
            $latitude          = isset($post['latitude']) ? $post['latitude'] : null;
            $longitude         = isset($post['longitude']) ? $post['longitude'] : null;
            $vendor_details_id = isset($post['vendor_details_id']) ? $post['vendor_details_id'] : null;

            // Validate the required fields
            if (empty($vendor_details_id) || empty($latitude) || empty($longitude)) {
                throw new BadRequestHttpException(Yii::t('app', 'vendor_details_id, latitude, and longitude are required.'));
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['id' => $vendor_details_id]);

            // Check if vendor details exist
            if (empty($vendor_details)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // If vendor details are found, format and return the response
            $data['status']  = self::API_OK;
            $data['details'] = $vendor_details->asCustomJson($user_id, $latitude, $longitude);
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
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        // Return the response in JSON format
        return $this->sendJsonResponse($data);
    }

    public function actionGetServiceTypeByVendorId()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post = Yii::$app->request->post();

            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $vendor_details_id = ! empty($post['vendor_details_id']) ? $post['vendor_details_id'] : null;
            $service_type      = ! empty($post['service_type']) ? $post['service_type'] : null;
            $sort              = ! empty($post['sort']) ? strtolower($post['sort']) : null;
            $search            = ! empty($post['search']) ? strtolower($post['search']) : null;

            // Validate the required fields
            if (empty($vendor_details_id) && empty($service_type)) {
                throw new BadRequestHttpException(Yii::t('app', 'vendor_details_id and service_type are required.'));
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['id' => $vendor_details_id]);

            // Check if vendor details exist
            if (empty($vendor_details)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Set up pagination
            $get      = Yii::$app->request->get();
            $page     = ! empty($get['page']) ? $get['page'] : 1;          // Default page is 1
            $pageSize = ! empty($get['pageSize']) ? $get['pageSize'] : 10; // Default pageSize is 10 

            $pageSize = min(max($pageSize, 1), 500); // Ensure page size is between 1 and 500

            // Query to fetch services
            $query = SubCategory::find()->where(['vendor_details_id' => $vendor_details_id])->andWHere(['status' => Services::STATUS_ACTIVE]);

            // Filter by service type
            if (! empty($service_type)) {
                // if ($service_type == Services::TYPE_WALK_IN) {
                //     $query->andWhere(['type' => $service_type]);
                // } elseif ($service_type == Services::TYPE_HOME_VISIT) {
                //     $query->andWhere(['home_visit' => 1]);
                // }
                $query->andWhere(['type' => $service_type]);
            }

            // Apply sorting based on 'service_for'
            if (! empty($sort) && in_array($sort, ['asc', 'desc'])) {
                $query->orderBy(['service_for' => ($sort === 'asc') ? SORT_ASC : SORT_DESC]);
            }
            if (! empty($search)) {

                $query->andWhere(['like', 'title', $search]);
            }

            // Set up pagination for the query
            $pagination = new \yii\data\Pagination([
                'totalCount' => $query->count(),
                'pageSize'   => $pageSize,
                'page'       => $page - 1,
            ]);

            // Fetch the services with pagination
            $services = $query->offset($pagination->offset)->limit($pagination->limit)->all();

            // Fetch the services with pagination
            $services = $query->offset($pagination->offset)->limit($pagination->limit)->all();

            // Check if services are found
            if (empty($services)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'No services found for this vendor.');
                return $this->sendJsonResponse($data);
            }

            // Prepare the response data
            $list = [];
            foreach ($services as $service) {
                $list[] = $service->asJson('', $user_id); // Assuming the `asJson` method exists in the `Services` model
            }

            // Success response with services and pagination info
            $data['status']     = self::API_OK;
            $data['details']    = $list;
            $data['pagination'] = [
                'totalCount'  => $pagination->totalCount,
                'pageCount'   => $pagination->getPageCount(),
                'currentPage' => $pagination->page + 1, // Display 1-based index
                'pageSize'    => $pagination->pageSize,
            ];
            $data['message'] = Yii::t('app', 'Services retrieved successfully.');
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
            $data['error']  = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        }

        // Return the response in JSON format
        return $this->sendJsonResponse($data);
    }

    // Add to Cart  

    public function actionAddToCartWeb()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(Yii::$app->request->headers['auth_code']) ? Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            $data['status']  = self::API_NOK;
            $data['error']   = Yii::t('app', 'User not authenticated. Please log in.');
            $data['relpace'] = false;
            return $this->sendJsonResponse($data);
        }
        $settings       = new WebSetting();
        $tax            = $settings->getSettingBykey('tax') ?? 0;
        $cgst_order_tax = $tax / 2;
        $sgst_order_tax = $tax / 2;

        $service_id   = ! empty($post['service_id']) ? $post['service_id'] : '';
        $service_type = isset($post['service_type']) ? $post['service_type'] : "";
        $qty          = 1;

        if (empty($service_id) || empty($service_type)) {
            $data['status']  = self::API_NOK;
            $data['error']   = Yii::t('app', 'Service ID  and Service Type are required.');
            $data['relpace'] = false;
            return $this->sendJsonResponse($data);
        }

        try {

            $ServicesCheck = Services::find()
                ->where([
                    'id'     => $service_id,
                    'status' => Services::STATUS_ACTIVE,
                ])
                ->one();

            if (empty($ServicesCheck) || empty($ServicesCheck->price) || empty($ServicesCheck->duration)) {
                $data['status']  = self::API_NOK;
                $data['error']   = Yii::t('app', 'Invalid service: Price or duration is missing.');
                $data['replace'] = false;
                return $this->sendJsonResponse($data);
            }

            // Fetch services based on type
            $servicesQuery = Services::find()->where(['id' => $service_id, 'status' => Services::STATUS_ACTIVE]);

            if (! empty($service_type) && $service_type == Services::TYPE_WALK_IN) {
                $servicesQuery->andWhere(['walk_in' => 1]);
            } elseif (! empty($service_type) && $service_type == Services::TYPE_HOME_VISIT) {
                $servicesQuery->andWhere(['home_visit' => 1]);
            }


            $services          = $servicesQuery->one();
            $vendor_details_id = $services->vendor_details_id;

            if (empty($services)) {

                $data['status']  = self::API_NOK;
                $data['error']   = 'The selected service is not available in this shop. Please try another service.';
                $data['relpace'] = false;
                return $this->sendJsonResponse($data);
            }

            // Check for any cart with zero quantity and remove it
            $this->cleanupCart($user_id);

            // Check if there is already a cart with different vendor
            $cartAlreadyExist = Cart::find()->where(['user_id' => $user_id])->andWhere(['!=', 'quantity', 0])->one();
            if (! empty($cartAlreadyExist) && $vendor_details_id != $cartAlreadyExist->vendor_details_id) {

                $cartAlreadyExistVendorDetails = VendorDetails::findOne(['id' => $cartAlreadyExist->vendor_details_id]);
                $existingStoreName             = $cartAlreadyExistVendorDetails->business_name;

                $currentStoreDetails = VendorDetails::findOne(['id' => $vendor_details_id]);
                $currentStoreName    = $currentStoreDetails->business_name;
                $data['status']      = self::API_NOK;
                $data['error']       =
                    'Your cart already includes services from "' . $existingStoreName . '". ' .
                    'You are trying to add services from "' . $currentStoreName . '". ' .
                    'You can only order from one shop at a time.';

                $data['relpace'] = true;
                return $this->sendJsonResponse($data);
            }

            // Add or update cart
            $cart        = Cart::findOne(['user_id' => $user_id, 'vendor_details_id' => $vendor_details_id]);
            $amount      = ! empty($services->discount_price) ? $services->discount_price : $services->price;
            $shopDetails = VendorDetails::findOne(['id' => $vendor_details_id]);
            $settings    = new WebSetting();
            $conv_fee    = $settings->getSettingBykey('conv_fee');

            if (empty($cart)) {
                $cart                    = new Cart();
                $cart->user_id           = $user_id;
                $cart->vendor_details_id = $vendor_details_id;
                $cart->amount            = $qty * $amount;
                $cart->quantity          = $qty;
                $cart->type_id           = $service_type;
                $cart->cgst              = $cgst_order_tax;
                $cart->sgst              = $sgst_order_tax;
                $cart->tax               = $cgst_order_tax + $sgst_order_tax;
                $cart->service_fees      = ! empty($shopDetails->min_service_fee) ? $shopDetails->min_service_fee : $conv_fee;

                // Calculate tax on service fee
                $referral_discount_amount           = Orders::calculateReferralDiscount($user_id, $cart->amount);
                $cart->referral_discount_percentage = $settings->getSettingBykey('referral_discount_percentage');
                $cart->referral_discount_amount     = $referral_discount_amount;

                $serviceFeeTax               = number_format(($cart->service_fees * $tax) / 100, 2, '.', '');
                $serviceFeeWithTax           = number_format($cart->service_fees + $serviceFeeTax, 2, '.', '');
                $cart->service_fees_with_tax = $serviceFeeWithTax;

                if (! $cart->save(false)) {
                    $data['status']  = self::API_NOK;
                    $data['error']   = 'Unable to save the cart. Please try again.';
                    $data['relpace'] = false;
                    return $this->sendJsonResponse($data);
                }
            } elseif ($cart->type_id == $service_type) {
                $cart->amount += $qty * $amount;
                $cart->quantity += $qty;
                $cart->user_id = $user_id;

                if (! $cart->save(false)) {

                    $data['status']  = self::API_NOK;
                    $data['error']   = 'Unable to save the cart. Please try again.';
                    $data['relpace'] = false;
                    return $this->sendJsonResponse($data);
                }
            } else {

                $data['status']  = self::API_NOK;
                $data['error']   = 'You cannot add a different service type to the cart. Would you like to clear the cart and add this service?';
                $data['relpace'] = true;
                return $this->sendJsonResponse($data);
            }

            $cart_item = $this->addOrUpdateCartItem($cart->id, $service_id, $qty, $amount, $user_id, 0);

            $data['status']       = self::API_OK;
            $data['details']      = $cart->asJsonAddToCart();
            $data['items']        = $cart_item;
            $data['cartQuantity'] = $cart->quantity;
            $data['message']      = Yii::t('app', 'Added to Cart successfully.');
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddToCart()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(Yii::$app->request->headers['auth_code']) ? Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            $data['status']  = self::API_NOK;
            $data['error']   = Yii::t('app', 'User not authenticated. Please log in.');
            $data['replace'] = false;
            return $this->sendJsonResponse($data);
        }

        // Decode JSON input for service_id
        $jsonInput = Yii::$app->request->getRawBody();
        $postData  = !empty($jsonInput) ? json_decode($jsonInput, true) : $post;

        // Check if JSON decoding failed
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data['status']  = self::API_NOK;
            $data['error']   = Yii::t('app', 'Invalid JSON input.');
            $data['replace'] = false;
            return $this->sendJsonResponse($data);
        }

        // Handle service_id as an array or single value
        $service_ids = [];
        if (isset($postData['service_id']) && is_array($postData['service_id'])) {
            foreach ($postData['service_id'] as $item) {
                if (isset($item['id']) && !empty($item['id'])) {
                    $service_ids[] = $item['id'];
                }
            }
        } elseif (!empty($post['service_id'])) {
            $service_ids[] = $post['service_id']; // Fallback to single service_id from POST
        }

        $service_type = isset($postData['service_type']) ? $postData['service_type'] : (isset($post['service_type']) ? $post['service_type'] : '');
        $qty          = 1; // Fixed as per original code

        if (empty($service_ids) || empty($service_type)) {
            $data['status']  = self::API_NOK;
            $data['error']   = Yii::t('app', 'Service ID and Service Type are required.');
            $data['replace'] = false;
            return $this->sendJsonResponse($data);
        }

        try {
            $cartItems         = [];
            $cartQuantity      = 0;
            $vendor_details_id = null;

            foreach ($service_ids as $service_id) {
                // Validate service
                $servicesCheck = Services::find()
                    ->where([
                        'id'     => $service_id,
                        'status' => Services::STATUS_ACTIVE,
                    ])
                    ->one();

                if (empty($servicesCheck) || (empty($servicesCheck->price) && empty($servicesCheck->from_price)) || empty($servicesCheck->duration)) {
                    $data['status']  = self::API_NOK;
                    $data['error']   = Yii::t(
                        'app',
                        'Invalid service: Price or From Price is required, and Duration must not be empty for service ID ' . $service_id . '.'
                    );
                    $data['replace'] = false;
                    return $this->sendJsonResponse($data);
                }

                // Derive vendor_details_id from the service
                $vendor_details_id_current = $servicesCheck->vendor_details_id;

                // Ensure all services belong to the same vendor
                if ($vendor_details_id === null) {
                    $vendor_details_id = $vendor_details_id_current;
                } elseif ($vendor_details_id !== $vendor_details_id_current) {
                    $data['status']  = self::API_NOK;
                    $data['error']   = Yii::t('app', 'All services must belong to the same vendor.');
                    $data['replace'] = true;
                    return $this->sendJsonResponse($data);
                }

                // Fetch services based on type
                $servicesQuery = Services::find()->where([
                    'id'                => $service_id,
                    'vendor_details_id' => $vendor_details_id,
                    'status'            => Services::STATUS_ACTIVE,
                ]);

                if ($service_type == Services::TYPE_WALK_IN) {
                    $servicesQuery->andWhere(['walk_in' => 1]);
                } elseif ($service_type == Services::TYPE_HOME_VISIT) {
                    $servicesQuery->andWhere(['home_visit' => 1]);
                }

                $services = $servicesQuery->one();

                if (empty($services)) {
                    $data['status']  = self::API_NOK;
                    $data['error']   = Yii::t('app', 'The selected service (ID: ' . $service_id . ') is not available in this shop.');
                    $data['replace'] = false;
                    return $this->sendJsonResponse($data);
                }

                // Clean up cart with zero quantity
                $this->cleanupCart($user_id);

                // Check if there is already a cart with different vendor
                $cartAlreadyExist = Cart::find()->where(['user_id' => $user_id])->andWhere(['!=', 'quantity', 0])->one();
                if (!empty($cartAlreadyExist) && $vendor_details_id != $cartAlreadyExist->vendor_details_id) {
                    $cartAlreadyExistVendorDetails = VendorDetails::findOne(['id' => $cartAlreadyExist->vendor_details_id]);
                    $existingStoreName             = $cartAlreadyExistVendorDetails->business_name;
                    $currentStoreDetails           = VendorDetails::findOne(['id' => $vendor_details_id]);
                    $currentStoreName              = $currentStoreDetails->business_name;

                    $data['status']  = self::API_NOK;
                    $data['error']   = "Your cart already includes services from '$existingStoreName'. You are trying to add services from '$currentStoreName'. You can only order from one shop at a time.";
                    $data['replace'] = true;
                    return $this->sendJsonResponse($data);
                }

                // Add or update cart
                $cart   = Cart::findOne(['user_id' => $user_id, 'vendor_details_id' => $vendor_details_id]);
                $amount = !empty($services->discount_price) ? $services->discount_price : $services->price;

                if (empty($cart)) {
                    $cart                    = new Cart();
                    $cart->user_id           = $user_id;
                    $cart->vendor_details_id = $vendor_details_id;
                    $cart->quantity          = $qty;
                    $cart->type_id           = $service_type;
                    if (!$cart->save(false)) {
                        $data['status']  = self::API_NOK;
                        $data['error']   = 'Unable to save the cart for service ID ' . $service_id . '.';
                        $data['replace'] = false;
                        return $this->sendJsonResponse($data);
                    }
                } elseif ($cart->type_id == $service_type) {
                    $cart->user_id = $user_id;
                    if (!$cart->save(false)) {
                        $data['status']  = self::API_NOK;
                        $data['error']   = 'Unable to update the cart for service ID ' . $service_id . '.';
                        $data['replace'] = false;
                        return $this->sendJsonResponse($data);
                    }
                } else {
                    $data['status']  = self::API_NOK;
                    $data['error']   = 'You cannot add a different service type to the cart for service ID ' . $service_id . '. Would you like to clear the cart and add this service?';
                    $data['replace'] = true;
                    return $this->sendJsonResponse($data);
                }

                // Add or update cart item
                $this->addOrUpdateCartItem($cart->id, $service_id, $qty, $amount, $user_id, 0);
            }

            Cart::updateCartTotalsByUser($user_id);
            $cart      = Cart::findOne(['user_id' => $user_id]);
            $cartItems = CartItems::find()->where(['cart_id' => $cart->id])->all();
            $cartQuantity = $cart->quantity;

            // ==== Auto-Apply Coupons Logic ====
            $appliedCoupons = CouponsApplied::find()->where(['cart_id' => $cart->id])->select('coupon_id')->column();
            // Fetch all auto-apply coupons
            $coupons = Coupon::find()
                ->where(['is_auto_apply_offer' => 1, 'status' => 1])
                ->andWhere(['not in', 'id', $appliedCoupons])
                ->all();

            foreach ($coupons as $coupon) {
                // Check if coupon applies to this service
                // Replace 'service_id' below with the correct column in Coupons table
                if (!isset($coupon->service_id) || $coupon->service_id == $service_id) {
                    $applied = new CouponsApplied();
                    $applied->coupon_id      = $coupon->id;
                    $applied->cart_id        = $cart->id;
                    $applied->service_item_id = $service_id;
                    $applied->status         = User::STATUS_ACTIVE; // active
                    $applied->created_on     = date('Y-m-d H:i:s');
                    $applied->save(false);
                }
            }


            // Success response
            $data['status']       = self::API_OK;
            $data['details']      = $cart->asJsonAddToCart();
            $data['items']        = $cartItems;
            $data['cartQuantity'] = $cartQuantity;
            $data['message']      = Yii::t('app', 'Added to Cart successfully.');
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['error']   = 'Error processing service ID ' . $service_id . ': ' . $e->getMessage();
            $data['replace'] = false;
        }

        return $this->sendJsonResponse($data);
    }


    private function cleanupCart($user_id)
    {
        $cartZero = Cart::find()->where(['user_id' => $user_id])->andWhere(['quantity' => 0])->one();
        if (! empty($cartZero)) {
            $cartItems = CartItems::find()->Where(['cart_id' => $cartZero->id])->all();
            if (! empty($cartItems)) {
                foreach ($cartItems as $items) {
                    $items->delete();
                }
            }
            $cartZero->delete();
        }
    }

    private function addOrUpdateCartItem($cart_id, $service_id, $qty, $amount, $user_id, $is_package_service = 0)
    {
        if ($is_package_service == 0) {
            $cart_item = CartItems::findOne(['service_item_id' => $service_id, 'cart_id' => $cart_id, 'is_package_service' => 0]);
        } else {
            $cart_item = CartItems::findOne(['service_item_id' => $service_id, 'cart_id' => $cart_id, 'is_package_service' => 1]);
        }
        $qty = 1;

        if (! empty($cart_item)) {
            $cart_item->quantity = $qty;
            $cart_item->amount   = $qty * $amount;
        } else {
            $cart_item = new CartItems();
        }
        $cart_item->cart_id            = $cart_id;
        $cart_item->service_item_id    = $service_id;
        $cart_item->quantity           = $qty;
        $cart_item->amount             = $is_package_service == 1 ? 0 : $qty * $amount;
        $cart_item->user_id            = $user_id;
        $cart_item->is_package_service = $is_package_service;

        $cart_item->save(false);
    }

    // View Cart

public function actionViewCart()
{
    $data = [];
    $post = Yii::$app->request->post();
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    if (!empty($user_id)) {
        // Fetch the user's cart with related data
        $ViewCart = Cart::find()
            ->where(['user_id' => $user_id])
            ->with(['comboPackage.comboServices.services', 'cartItems'])
            ->one();

        if (!empty($ViewCart)) {
            // Cart found, return its details
            $data['status'] = self::API_OK;
            $data['details'] = $ViewCart->asJson();
        } else {
            // No cart found for the user
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "Your cart is currently empty.");
        }
    } else {
        // User authentication failed
        $data['status'] = self::API_NOK;
        $data['error'] = Yii::t("app", "User not authenticated. Please log in.");
    }

    return $this->sendJsonResponse($data);
}
    public function actionDeleteCart()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User authentication failed. Please log in.');
            return $this->sendJsonResponse($data);
        }

        try {
            // Fetch user's cart
            $cart = Cart::findOne(['user_id' => $user_id]);

            // Delete combo packages
            $comboPackages = CombopackagesCart::find()->where(['user_id' => $user_id])->all();
            foreach ($comboPackages as $package) {
                $package->delete();
            }

            // Delete cart + cart items
            if ($cart) {
                CartItems::deleteAll(['cart_id' => $cart->id]);
                $cart->delete();

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Your cart has been deleted successfully.');
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'There is no cart to delete.');
            }
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'Something went wrong: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteCartItem($cart_item_id = '')
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            if (! empty($cart_item_id)) {
                // Find the cart item for the given cart item ID and user ID
                $cart_item = CartItems::find()->where(['id' => $cart_item_id, 'user_id' => $user_id, 'is_package_service' => 0])->one();

                if (! empty($cart_item)) {
                    $cart_id = $cart_item->cart_id;

                    // Delete the cart item
                    if ($cart_item->delete()) {
                        // Check if there are any remaining items in the cart
                        $check_cart_items = CartItems::find()->where(['cart_id' => $cart_id])->all();
                        cart::updateCartTotalsByUser($user_id);

                        // Update or delete the cart based on the remaining items
                        $cart = Cart::find()->where(['id' => $cart_id])->one();
                        if (! empty($cart)) {

                            if (empty($check_cart_items)) {
                                $cart->delete();
                                $data['message'] = Yii::t('app', 'Cart is now empty.');
                                $cartQty         = 0;
                                $cartAmount      = 0;
                            } else {
                                $data['message'] = Yii::t('app', 'Cart item deleted successfully.');
                                $cartQty         = $cart->quantity;
                                $cartAmount      = $cart->amount;
                            }

                            $data['status']     = self::API_OK;
                            $data['cartQty']    = $cartQty;
                            $data['cartAmount'] = $cartAmount;
                        } else {
                            $data['status'] = self::API_NOK;
                            $data['error']  = Yii::t('app', 'Cart not found.');
                        }
                    } else {
                        $data['status'] = self::API_NOK;
                        $data['error']  = Yii::t('app', 'Unable to delete the cart item. Please try again later.');
                    }
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t('app', 'No cart item found with the provided ID.');
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'Cart item ID is required.');
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User authentication failed. Please log in.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteCartItemByServiceId($service_item_id = '')
    {

        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            if (! empty($service_item_id)) {
                // Find the cart item for the given cart item ID and user ID
                $cart_item = CartItems::find()->where(['service_item_id' => $service_item_id, 'user_id' => $user_id])->one();

                if (! empty($cart_item)) {
                    $cart_id = $cart_item->cart_id;

                    // Delete the cart item
                    if ($cart_item->delete()) {
                        // Check if there are any remaining items in the cart
                        $check_cart_items       = CartItems::find()->where(['cart_id' => $cart_id])->all();
                        $check_cart_itemsCount  = CartItems::find()->where(['cart_id' => $cart_id])->sum('quantity');
                        $check_cart_itemsAmount = CartItems::find()->where(['cart_id' => $cart_id])->sum('amount');

                        // Update or delete the cart based on the remaining items
                        $cart = Cart::find()->where(['id' => $cart_id])->one();
                        if (! empty($cart)) {
                            $cart->quantity = $check_cart_itemsCount;
                            $cart->amount   = $check_cart_itemsAmount;

                            if ($cart->save(false)) {
                                if (empty($check_cart_items)) {
                                    $cart->delete();
                                    $data['message'] = Yii::t('app', 'Cart is now empty.');
                                    $cartQty         = 0;
                                    $cartAmount      = 0;
                                } else {
                                    $data['message'] = Yii::t('app', 'Cart item deleted successfully.');
                                    $cartQty         = $cart->quantity;
                                    $cartAmount      = $cart->amount;
                                }

                                $data['status']     = self::API_OK;
                                $data['cartQty']    = $cartQty;
                                $data['cartAmount'] = $cartAmount;
                            } else {
                                $data['status'] = self::API_NOK;
                                $data['error']  = Yii::t('app', 'Failed to update cart. Please try again.');
                            }
                        } else {
                            $data['status'] = self::API_NOK;
                            $data['error']  = Yii::t('app', 'Cart not found.');
                        }
                    } else {
                        $data['status'] = self::API_NOK;
                        $data['error']  = Yii::t('app', 'Unable to delete the cart item. Please try again later.');
                    }
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t('app', 'No cart item found with the provided ID.');
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'Cart item ID is required.');
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User authentication failed. Please log in.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetStoreDetails()
    {
        $data = [];

        try {
            // Get the POST data and headers
            $post = Yii::$app->request->post();

            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Validate the required field: vendor_id
            $vendor_id = $post['vendor_id'];
            if (empty($vendor_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'vendor_id is required.'));
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['id' => $vendor_id, 'status' => VendorDetails::STATUS_ACTIVE]);
            if (empty($vendor_details)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Prepare the response data
            $data = [
                'status'  => self::API_OK,
                'details' => $vendor_details->storeAddressAsJson(),

            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        }

        // Return the response in JSON format
        return $this->sendJsonResponse($data);
    }

    // CheckOut
    public function actionCheckOut()
    {
        $data = [];
        // Retrieve the auth_code from headers or query parameters
        $headers     = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth        = new AuthSettings();
        $user_id     = $auth->getAuthSession($headers);
        $settings    = new WebSetting();
        $advance_pay = $settings->getSettingBykey('advance_pay');

        try {
            // Check if the user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
            }

            // Fetch the user's cart
            $myCart = Cart::find()->where(['user_id' => $user_id])->one();

            // Check if the cart exists
            if (empty($myCart)) {
                throw new NotFoundHttpException(Yii::t("app", "No cart found for the user."));
            }

            $post = Yii::$app->request->post();

            // Check if post data is provided
            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t("app", "No data provided."));
            }

            // Retrieve service details from the post data
            $service_address = isset($post['service_address']) ? $post['service_address'] : '';
            $service_time    = isset($post['service_time']) ? $post['service_time'] : '';
            $service_date    = isset($post['service_date']) ? $post['service_date'] : '';
            $payment_mode    = ! empty($post['payment_mode']) ? $post['payment_mode'] : Cart::PAYMENT_MODE_FULL;

            // Validate that both service time and date are provided
            if (empty($service_time) || empty($service_date)) {
                throw new BadRequestHttpException(Yii::t("app", "Service time and service date are required."));
            }

            // Update cart details
            $myCart->service_address = $service_address;
            $myCart->service_time    = $service_time;
            $myCart->service_date    = $service_date;
            $myCart->payment_mode    = $payment_mode;
            if ($payment_mode == Cart::PAYMENT_MODE_PARTIAL) {
                $myCart->advance_pay_in_percentage = $advance_pay;
            }

            // Save the cart and check for errors
            if ($myCart->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $myCart->asJson();
                $data['message'] = Yii::t("app", "Cart updated successfully.");
            } else {
                Yii::error("Cart update failed for user_id: {$user_id}", __METHOD__);
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update cart. Please try again later."));
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
        } catch (\Exception $e) {
            Yii::error("Unexpected error occurred: {$e->getMessage()}", __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred. Please try again later.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpdateCartAddress()
    {
        $data = [];

        // Retrieve the auth_code from headers or query parameters
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if the user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
            }

            // Fetch the user's cart
            $myCart = Cart::find()->where(['user_id' => $user_id])->one();
            if (empty($myCart)) {
                throw new NotFoundHttpException(Yii::t("app", "No cart found for the user."));
            }

            // Retrieve the post data
            $post = Yii::$app->request->post();
            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t("app", "No data was provided."));
            }

            // Validate address_id
            $address_id = isset($post['address_id']) ? $post['address_id'] : null;
            if (empty($address_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Address ID is required."));
            }

            // Fetch the user address
            $user_address = DeliveryAddress::find()->where(['id' => $address_id])->one();
            if (empty($user_address)) {
                throw new NotFoundHttpException(Yii::t("app", "Delivery address not found."));
            }

            // Update cart with the service address
            $myCart->service_address = $user_address->id;

            if ($myCart->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $myCart->asJson();
                $data['message'] = Yii::t("app", "Cart address updated successfully.");
            } else {
                Yii::error("Failed to update cart address for user_id: {$user_id}", __METHOD__);
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update cart. Please try again later."));
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
        } catch (\Exception $e) {
            Yii::error("Unexpected error occurred: {$e->getMessage()}", __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred. Please try again later.");
        }

        return $this->sendJsonResponse($data);
    }
public function actionAvailableSlots()
{
    $data = [];
    $post = Yii::$app->request->post();
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);

    try {
        // Validate user authentication
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed."));
        }

        // Validate required parameters
        if (empty($post['vendor_details_id']) || empty($post['date'])) {
            throw new BadRequestHttpException(Yii::t("app", "vendor_details_id and date are required."));
        }

        $vendor_details_id = $post['vendor_details_id'];
        $inputDate = $post['date'];

        // Parse and validate date (Y-m-d format)
        $timestamp = strtotime($inputDate);
        if (!$timestamp) {
            throw new BadRequestHttpException(Yii::t("app", "Invalid date format. Use YYYY-MM-DD."));
        }
        $date = date('Y-m-d', $timestamp);

        // Prevent past dates
        $currentDate = strtotime(date('Y-m-d'));
        if ($timestamp < $currentDate) {
            throw new BadRequestHttpException(Yii::t("app", "You cannot select a past date."));
        }

        // Get the day of the week
        $day = date('l', $timestamp);
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
        $selectedDate = strtotime($date);
        $daysDifference = floor(($selectedDate - $currentDate) / (60 * 60 * 24));

        if ($daysDifference == 0) {
            // If the selected date is today, restrict past time slots
            $currentTime = date('H:i');
            $roundedTime = date("H:i", ceil(strtotime($currentTime) / (60 * 30)) * (60 * 30)); // Round to the nearest 30 minutes
            $storeStartTime = date('H:i', strtotime($storeTimings->start_time));
            $storeEndTime = date('H:i', strtotime($storeTimings->close_time));
            $startSlotTime = max($storeStartTime, $roundedTime);

            // Generate slots from the adjusted start time to the store's close time
            $slots = VendorDetails::getServiceScheduleSlots(30, 0, $startSlotTime, $storeEndTime);
        } else {
            // For future dates, show all available slots
            $slots = VendorDetails::getServiceScheduleSlots(30, 0, $storeTimings->start_time, $storeTimings->close_time);
        }

        // Categorize slots into morning, afternoon, and evening with coupon details
        $categorizedSlots = [
            'morning' => [],   // 00:00 - 11:59
            'afternoon' => [], // 12:00 - 16:59
            'evening' => [],   // 17:00 - 23:59
        ];

        foreach ($slots as $slot) {
            $slotTime = strtotime($slot);
            $hour = (int)date('H', $slotTime);
            $slotCategory = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');

            // Check for coupons for this slot
            $couponData = Coupon::getCouponOffersByDays($slot, $day, $vendor_details_id);
            $slotDetails = [
                'time' => $slot,
                'has_coupon' => $couponData['exists'],
                'coupons' => $couponData['exists'] ? $couponData['details'] : [],
            ];

            $categorizedSlots[$slotCategory][] = $slotDetails;
        }

        // Check if any slots are available
        if (!empty($slots)) {
            $data['status'] = self::API_OK;
            $data['details'] = [
                'date' => $date,
                'day' => $day,
                'slots' => $categorizedSlots,
            ];
        } else {
            $data['status'] = self::API_NOK;
            $data['details'] = Yii::t("app", "No slots available for the selected date.");
        }
    } catch (UnauthorizedHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
    } catch (BadRequestHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
    } catch (NotFoundHttpException $e) {
        $data['status'] = self::API_NOK;
        $data['error'] = $e->getMessage();
    } catch (\Exception $e) {
        // Log the error for debugging
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $post,
        ], __METHOD__);
        $data['status'] = self::API_NOK;
        $data['error'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
    }

    return $this->sendJsonResponse($data);
}

    //Check Cart exist or not
    public function actionCheckCart()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(Yii::$app->request->headers['auth_code']) ? Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        if (! empty($user_id)) {
            $cartData = Cart::find()->where(['user_id' => $user_id])->one();
            if (empty($cartData)) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "cart User found");
                return $this->sendJsonResponse($data);
            }
            $cart_id           = $cartData->id;
            $cart_items        = CartItems::find()->where(['cart_id' => $cart_id])->andWHere(['is_package_service' => 1])->count();
            $CombocartQuantity = ComboPackagesCart::find()->where(['user_id' => $user_id])->count();
            if (! empty($cartData)) {
                $cartExist                 = true;
                $cartQuantity              = $cartData['quantity'] - $cart_items;
                $data['status']            = self::API_OK;
                $data['cartExist']         = $cartExist;
                $data['cartQuantity']      = $cartQuantity > 0 ? $cartQuantity : 0;
                $data['ComboCartQuantity'] = $CombocartQuantity ?? 0;
                $data['cartAmount']        = $cartData['amount'];
            } else {
                $data['status']            = self::API_OK;
                $data['cartExist']         = false;
                $data['cartQuantity']      = 0;
                $data['cartAmount']        = 0;
                $data['CombocartQuantity'] = 0;
            }

            if ($data['cartQuantity'] < 1 && $data['ComboCartQuantity'] < 1) {
                $data['status'] = self::API_NOK;
                $data['error']  = "No items found";
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No User found");
        }
        return $this->sendJsonResponse($data);
    }

    public function actionListCoupons()
    {
        $data      = [];
        $list      = [];
        $listStore = [];
        $post      = Yii::$app->request->post();
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);

        try {
            // Check if the user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch vendor details ID from the request
            $vendor_details_id = ! empty(($post['vendor_details_id'])) ? $post['vendor_details_id'] : null;
            if (empty($vendor_details_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor details ID is required."));
            }

            $currentDate = date('Y-m-d H:i:s'); // Store only date part

            $globalCoupons = Coupon::find()
                ->where(['coupon.status' => Coupon::STATUS_ACTIVE])
                ->andWhere(['coupon.is_global' => 1])
                ->andWhere(['<=', 'coupon.start_date', $currentDate])
                ->andWhere([
                    'or',
                    ['>=', 'coupon.end_date', $currentDate],
                    ['coupon.end_date' => null],
                ])
                ->all();

            $storeCoupons = Coupon::find()
                ->joinWith('couponVendors as cs')
                ->where(['cs.status' => CouponVendor::STATUS_ACTIVE])
                ->andWhere(['cs.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<=', 'coupon.start_date', $currentDate])
                ->andWhere([
                    'or',
                    ['>=', 'coupon.end_date', $currentDate],
                    ['coupon.end_date' => null],
                ])
                ->orderBy(['coupon.id' => SORT_DESC])
                ->all();

            // Process global coupons
            if (! empty($globalCoupons)) {
                foreach ($globalCoupons as $globalCoupon) {
                    $list[] = $globalCoupon->asJson();
                }
            }

            // Process store-specific coupons
            if (! empty($storeCoupons)) {
                foreach ($storeCoupons as $storeCoupon) {
                    $listStore[] = $storeCoupon->asJson();
                }
            }

            // Merge global and store-specific coupons
            $finalCoupons = array_merge($list, $listStore);

            // Check if there are any coupons to return
            if (! empty($finalCoupons)) {
                $data['status']  = self::API_OK;
                $data['details'] = $finalCoupons;
            } else {
                throw new NotFoundHttpException(Yii::t("app", "No valid coupons found for this vendor."));
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

    public function actionApplyCoupon()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (!empty($user_id)) {
            try {
                $cart = Cart::findOne(['user_id' => $user_id]);
                if (empty($cart)) {
                    throw new \yii\web\BadRequestHttpException('Cart not found.');
                }

                $coupon = Coupon::find()
                    ->where(['status' => Coupon::STATUS_ACTIVE, 'code' => $post['coupon_code']])
                    ->one();

                if (empty($coupon)) {
                    throw new \yii\web\NotFoundHttpException('Coupon does not exist.');
                }

                $fnl_amount = $cart->amount - $cart->package_amount;

                if ($coupon->min_cart > $fnl_amount) {
                    throw new \yii\web\BadRequestHttpException('Your cart value must be at least ₹' . $coupon->min_cart . ' to apply this coupon.');
                }

                // Check if coupon is global or vendor-specific
                if ($coupon->is_global == 0) {
                    $coupon = Coupon::find()->joinWith('couponVendors as cs')
                        ->where(['coupon.status' => Coupon::STATUS_ACTIVE, 'code' => $post['coupon_code']])
                        ->andWhere(['cs.vendor_details_id' => $cart->vendor_details_id])
                        ->one();

                    if (empty($coupon)) {
                        throw new \yii\web\BadRequestHttpException('This coupon is not valid for this store.');
                    }
                }

                // ✅ Check if coupon already applied
                $existingCoupon = CouponsApplied::find()
                    ->where(['cart_id' => $cart->id])
                    ->andWhere(['in', 'status', [CouponsApplied::STATUS_ACTIVE, CouponsApplied::STATUS_INACTIVE]])
                    ->one();

                if ($existingCoupon && $existingCoupon->status == CouponsApplied::STATUS_ACTIVE) {
                    throw new \yii\web\BadRequestHttpException('Coupon has already been applied to this cart.');
                }
                // ✅ Check usage limits - FIXED: Count both ACTIVE and INACTIVE status
                $userCouponUsageCount = CouponsApplied::find()
                    ->where([
                        'coupon_id'      => $coupon->id,
                        'create_user_id' => $cart->create_user_id,
                    ])
                    ->andWhere(['IN', 'status', [CouponsApplied::STATUS_ACTIVE, CouponsApplied::STATUS_INACTIVE]])
                    ->count();

                if (!empty($coupon->max_use) && $userCouponUsageCount >= $coupon->max_use) {
                    throw new \yii\web\BadRequestHttpException('You have already used this coupon. Limit is ' . $coupon->max_use . ' per user.');
                }
                $alreadyUsed = CouponsApplied::find()
                    ->where([
                        'coupon_id'      => $coupon->id,
                        'create_user_id' => $user_id,
                    ])
                    ->andWhere(['IN', 'status', [CouponsApplied::STATUS_ACTIVE, CouponsApplied::STATUS_INACTIVE]])
                    ->exists();
                if ($alreadyUsed) {
                    throw new \yii\web\BadRequestHttpException(
                        'You have already used this coupon. The usage limit for this coupon has been reached.'
                    );
                }

                // ✅ Apply the coupon for the cart
                $couponApplied            = new CouponsApplied();
                $couponApplied->cart_id   = $cart->id;
                $couponApplied->coupon_id = $coupon->id;
                $couponApplied->status    = CouponsApplied::STATUS_INACTIVE;

                if ($couponApplied->save(false)) {

                    // ✅ Get services in cart eligible for discount
                    $eligibleServiceIds = ServiceHasCoupons::find()
                        ->select('service_id')
                        ->where(['coupon_id' => $coupon->id])
                        ->column();

                    $eligibleAmount = 0;
                    $eligibleServices = [];

                    if (!empty($eligibleServiceIds)) {
                        $eligibleServices = CartItems::find()
                            ->where(['cart_id' => $cart->id])
                            ->andWhere(['service_item_id' => $eligibleServiceIds])
                            ->all();

                        foreach ($eligibleServices as $item) {
                            $eligibleAmount += $item->amount * $item->quantity;
                        }
                    }

                    // ✅ If no service mapping found, fall back to entire cart
                    if ($eligibleAmount == 0) {
                        $eligibleAmount = $cart->amount;
                        $eligibleServices = CartItems::find()->where(['cart_id' => $cart->id])->all();
                    }

                    // ✅ Calculate discount
                    if ($coupon->discount_type == 1) { // percentage
                        $discountAmount = ($coupon->discount / 100) * $eligibleAmount;
                    } else { // fixed
                        $discountAmount = min($coupon->discount, $eligibleAmount);
                    }

                    if (!empty($coupon->max_discount) && $discountAmount > $coupon->max_discount) {
                        $discountAmount = $coupon->max_discount;
                    }

                    // ✅ Update cart with coupon applied
                    $cart->coupon_applied_id = $couponApplied->id;
                    $cart->coupon_code       = $coupon->code;
                    $cart->coupon_discount   = $discountAmount;
                    $cart->save(false);

                    // ✅ Update order details for each eligible service
                    foreach ($eligibleServices as $item) {
                        $orderDetail = OrderDetails::findOne(['service_id' => $item->service_item_id]);
                        if ($orderDetail) {
                            $lineDiscountAmount = 0;

                            if ($coupon->discount_type == 1) { // percentage
                                $lineDiscountAmount = ($coupon->discount / 100) * ($item->amount * $item->quantity);
                            } else { // fixed
                                // distribute proportionally if multiple items
                                $proportion = ($item->amount * $item->quantity) / $eligibleAmount;
                                $lineDiscountAmount = $proportion * $discountAmount;
                            }

                            if (!empty($coupon->max_discount) && $lineDiscountAmount > $coupon->max_discount) {
                                $lineDiscountAmount = $coupon->max_discount;
                            }

                            $orderDetail->discount_type   = $coupon->discount_type;
                            $orderDetail->discount        = $coupon->discount;
                            $orderDetail->discount_amount = $lineDiscountAmount;
                            $orderDetail->total_price     = ($orderDetail->price * $orderDetail->qty) - $lineDiscountAmount;
                            $orderDetail->updated_on      = date('Y-m-d H:i:s');
                            $orderDetail->update_user_id  = $user_id;
                            $orderDetail->save(false);
                        }
                    }

                    $data['status']          = self::API_OK;
                    $data['coupon_apply_id'] = $couponApplied->id;
                    $data['coupon_discount'] = $discountAmount;
                    $data['coupon_details']  = $coupon->asJson();
                } else {
                    throw new \yii\web\ServerErrorHttpException('Failed to apply the coupon. Please try again later.');
                }
            } catch (\yii\web\NotFoundHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\yii\web\BadRequestHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\yii\web\ServerErrorHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\Exception $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'An unexpected error occurred: ' . $e->getMessage();
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = 'User session not found. Please log in.';
        }

        return $this->sendJsonResponse($data);
    }





    //Remove Coupon
    public function actionRemoveCoupon()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            try {
                // Find the applied coupon
                $couponApplyId = isset($post['coupon_apply_id']) ? $post['coupon_apply_id'] : null;
                if (empty($couponApplyId)) {
                    throw new \yii\web\BadRequestHttpException("Coupon apply ID is required.");
                }

                $appliedCoupon = CouponsApplied::findOne($couponApplyId);
                if (empty($appliedCoupon)) {
                    throw new \yii\web\NotFoundHttpException("No applied coupon found.");
                }

                // Attempt to delete the applied coupon
                if ($appliedCoupon->delete()) {
                    // Find the cart related to this coupon and reset the coupon details
                    $cart = Cart::find()->where(['user_id' => $user_id, 'coupon_applied_id' => $couponApplyId])->one();
                    if (! empty($cart)) {
                        $cart->coupon_applied_id = null;
                        $cart->coupon_discount   = 0;
                        $cart->coupon_code       = null;

                        if ($cart->save(false)) {
                            $data['status']          = self::API_OK;
                            $data['coupon_discount'] = 0;
                            $data['details']         = Yii::t("app", "Coupon removed successfully.");
                        } else {
                            throw new \yii\web\ServerErrorHttpException("Failed to update the cart.");
                        }
                    } else {
                        throw new \yii\web\NotFoundHttpException("No cart found for the user with the applied coupon.");
                    }
                } else {
                    throw new \yii\web\ServerErrorHttpException("Failed to remove the applied coupon.");
                }
            } catch (\yii\web\BadRequestHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\yii\web\NotFoundHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\yii\web\ServerErrorHttpException $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = $e->getMessage();
            } catch (\Exception $e) {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "User session not found. Please log in.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCashMode()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(Yii::$app->request->headers['auth_code']) ? Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication 
            if (empty($user_id)) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'Invalid or missing AuthCode';
                return $this->sendJsonResponse($data);
            }

            // Find cart for the user
            $cart = Cart::find()->where(['user_id' => $user_id])->one();
            if (empty($cart)) {
                $data['status'] = self::API_NOK;
                $data['error']  = 'No cart found for the user';
                return $this->sendJsonResponse($data);
            }

            // Save order based on the cart
            $order = (new Orders())->saveOrderByCart(Orders::TYPE_COD, $user_id);
            if (! empty($order['status']) && $order['status'] == self::API_NOK) {
                $data['status'] = self::API_NOK;
                $data['error']  = $order['error'];
                return $this->sendJsonResponse($data);
            }

            // Delete cart items 
            $cartItems = CartItems::find()->where(['cart_id' => $cart->id])->all();
            if (! empty($cartItems)) {
                foreach ($cartItems as $cartItem) {
                    $cartItem->delete();
                }
            }

            // Delete the cart itself  
            $cart->delete();

            // Send notification to the user

            $scheduleDate = $order->schedule_date ?? 'Not scheduled';
            $scheduleTime = $order->schedule_time ?? 'Not scheduled';

            // Send notification to the user      
            $title = "Order Placed";
            $body  = "Your order (ID: #{$order->id}) has been placed successfully. Scheduled for: $scheduleDate at $scheduleTime";

            Yii::$app->notification->PushNotification(
                $order->id,
                $user_id,
                $title,
                $body,
                'redirect'
            );

            // Send notification to the vendor
            $vendorTitle = 'New Order Received';
            $vendorBody  = "You have received a new order with ID #{$order->id}. Scheduled for: $scheduleDate at $scheduleTime";

            Yii::$app->notification->PushNotification(
                $order->id,
                $order->vendorDetails->user_id,
                $vendorTitle,
                $vendorBody,
                'redirect'
            );

            // Return successful response
            $data['status']   = self::API_OK;
            $data['details']  = $order->asJson();
            $data['order_id'] = $order->id;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            // Generic exception handling
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionWalletMode()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', "Invalid or missing AuthCode"));
            }

            // Find cart for the user
            $cart = Cart::find()->where(['user_id' => $user_id])->one();

            if (empty($cart)) {
                throw new NotFoundHttpException(Yii::t('app', "No cart found for this user."));
            }

            // Predict payable amount first based on advance/full
            $settings    = new WebSetting();
            $advance_pay = $settings->getSettingBykey('advance_pay');
            $advance_pay = is_numeric($advance_pay) ? floatval($advance_pay) : 0;

            $cartTotalWithCharges = ($cart['amount'] + $cart['tip'] + $cart['cgst'] + $cart['sgst'] + $cart['service_fees'] + $cart['other_charges']) - floatval($cart['coupon_discount']);
            $predictedPayable     = $cart['payment_mode'] == Orders::PAYMENT_MODE_PARTIAL && $advance_pay > 0
                ? round($cartTotalWithCharges * $advance_pay / 100, 2)
                : round($cartTotalWithCharges, 2);

            // Check if the user has sufficient wallet balance
            $availableWalletAmount = Wallet::getAvailableBalance($user_id);
            if ($availableWalletAmount < $predictedPayable) {
                throw new \Exception(Yii::t('app', "Insufficient wallet balance. Required: ₹{amount}, Available: ₹{balance}", [
                    'amount'  => $predictedPayable,
                    'balance' => $availableWalletAmount,
                ]));
            }

            // Save the order
            $order = (new Orders())->saveOrderByCart(Orders::TYPE_WALLET, $user_id);

            if (! empty($order['status']) && $order['status'] == self::API_NOK) {
                $data['status'] = self::API_NOK;
                $data['error']  = $order['error'];
                return $this->sendJsonResponse($data);
            }

            // Delete cart items after order is saved
            $cartItems = CartItems::find()->where(['cart_id' => $cart['id']])->all();
            if (! empty($cartItems)) {
                foreach ($cartItems as $cartItem) {
                    $cartItem->delete();
                }
            }

            // Debit balance from the wallet
            $wallet                = new Wallet();
            $wallet->order_id      = $order->id;
            $wallet->user_id       = $user_id;
            $wallet->amount        = $order->payable_amount;
            $wallet->payment_type  = Wallet::STATUS_DEBITED;
            $wallet->status        = Wallet::STATUS_COMPLETED;
            $wallet->method_reason = Yii::t('app', "Order Placed");
            $wallet->description   = Yii::t('app', "Order placed successfully. Order ID: #{id}", ['id' => $order->id]);

            if (! $wallet->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', "Failed to update the wallet balance."));
            }

            $order_transaction_details                 = new OrderTransactionDetails();
            $order_transaction_details->order_id       = $order->id;
            $order_transaction_details->amount         = $order->payable_amount;
            $order_transaction_details->payment_source = OrderTransactionDetails::PAYMENT_SOURCE_WALLET;
            $order_transaction_details->payment_type   = OrderTransactionDetails::PAYMENT_TYPE_WALLET;
            $order_transaction_details->status         = OrderTransactionDetails::STATUS_SUCCESS;
            $order_transaction_details->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();
            $order_transaction_details->order_type = OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER;

            $order_transaction_details->save(false);
            $appliedCoupon = CouponsApplied::findOne(['cart_id' => $cart->id, 'status' => CouponsApplied::STATUS_INACTIVE]);
            if ($appliedCoupon) {
                $appliedCoupon->order_id = $order->id;
                $appliedCoupon->status   = CouponsApplied::STATUS_ACTIVE;
                $appliedCoupon->save(false);
            }

            // Delete the cart itself
            $cart = Cart::findOne(['id' => $cart['id']]);
            if (! empty($cart)) {
                $cart->delete();
            }
            $combo_packages_cart = ComboPackagesCart::find()->where(['user_id' => $user_id])->all();
            if (! empty($combo_packages_cart)) {
                foreach ($combo_packages_cart as $combo) {
                    $combo->delete();
                }
            }

            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();
            $order->payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
            $order->save(false);

            User::addReferralBonusOnFirstPaidOrder($user_id);

            if ($order->balance_amount == 0 || $order->balance_amount <= 0) {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
                $order->save(false);
            } else {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_PENDING;
                $order->save(false);
            }


            $order_id   = $order->id;
            Orders::markChildOrdersAsPaid($order_id);




            WebsocketEmitter::emitToVendor('order_created', (int) $order->vendorDetails->id, $order->asJson(), [
                'source'  => 'yii2',
                'version' => '1.0',
            ]);

            // Send notifications to the vendor and user

            // Assuming 'schedule_date' and 'schedule_time' are properties of the Orders model
            $scheduleDate = $order->schedule_date ?? 'Not scheduled';
            $scheduleTime = $order->schedule_time ?? 'Not scheduled';

            // Send notification to the user
            $title = "Order Placed";
            $body  = "Your order (ID: #{$order->id}) has been placed successfully. Scheduled for: $scheduleDate at $scheduleTime";

            Yii::$app->notification->PushNotification(
                $order->id,
                $user_id,
                $title,
                $body,
                'redirect'
            );

            WhatsApp::sendTemplate($order->user->contact_no, 'estetica_booking_confirmed', [
                'param_1'   => $order->user->first_name,
                'param_2'   => $order->schedule_date,
                'param_3'   => $order->schedule_time,
                'param_4'   => $order->vendorDetails->address,
            ]);

            // Send notification to the vendor
            $vendorTitle = 'New Order Received';
            $vendorBody  = "You have received a new order with ID #{$order->id}. Scheduled for: $scheduleDate at $scheduleTime";

            Yii::$app->notification->PushNotification(
                $order->id,
                $order->vendorDetails->user_id,
                $vendorTitle,
                $vendorBody,
                'redirect'
            );
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();
            WebsocketEmitter::emitToVendor('order_created', (int) $order->vendorDetails->id, $order->asJson(), [
                'source'  => 'yii2',
                'version' => '1.0',
            ]);

            // Yii::$app->notification->customVendorNoti($order->id, $order->shop->owner_id, $vendorTitle, $vendorBody, 'redirect');
            // Yii::$app->notification->customVendorNoti($order->id, $order->user_id, $title, $body, 'redirect');

            // Return successful response
            $data['status']   = self::API_OK;
            $data['details']  = $order->asJson();
            $data['order_id'] = $order->id;
        } catch (UnauthorizedHttpException $e) {
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
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionWalletModePendingPayment()
    {
        $response = [];
        $post     = Yii::$app->request->post();
        $authCode = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth     = new AuthSettings();
        $userId   = $auth->getAuthSession($authCode);
        $orderId  = $post['order_id'] ?? null;
        $end_key  = 'redirect';

        try {
            // 🔒 Step 1: Auth Check
            if (empty($userId)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'Invalid or missing AuthCode'));
            }

            // 📦 Step 2: Order Check
            $order = Orders::findOne(['id' => $orderId, 'user_id' => $userId]);
            if (! $order) {
                throw new NotFoundHttpException(Yii::t('app', 'Order not found or does not belong to this user.'));
            }

            // 💰 Step 3: Wallet Balance Check
            $totalAmount   = $order->payable_amount;
            $walletBalance = Wallet::getAvailableBalance($userId);
            if ($walletBalance < $totalAmount) {
                throw new \Exception(Yii::t('app', 'Insufficient wallet balance. Available: ₹{balance}', [
                    'balance' => $walletBalance,
                ]));
            }

            // 💳 Step 4: Debit Wallet
            $wallet                = new Wallet();
            $wallet->order_id      = $order->id;
            $wallet->user_id       = $userId;
            $wallet->amount        = $totalAmount;
            $wallet->payment_type  = Wallet::STATUS_DEBITED;
            $wallet->status        = Wallet::STATUS_COMPLETED;
            $wallet->method_reason = "Order Placed";
            $wallet->description   = Yii::t('app', 'Order placed. Amount debited for Order ID #{id}', ['id' => $order->id]);

            if (! $wallet->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to update wallet. Please try again.'));
            }

            $order_transaction_details                 = new OrderTransactionDetails();
            $order_transaction_details->order_id       = $order->id;
            $order_transaction_details->amount         = $totalAmount;
            $order_transaction_details->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();
            $order_transaction_details->payment_source = OrderTransactionDetails::PAYMENT_SOURCE_WALLET;
            $order_transaction_details->payment_type   = OrderTransactionDetails::PAYMENT_TYPE_QR;
            $order_transaction_details->status         = OrderTransactionDetails::STATUS_SUCCESS;
            $order_transaction_details->order_type = OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER;
            $order_transaction_details->save(false);

            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();
            $order->payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
            $order->save(false);

            if ($order->balance_amount == 0 || $order->balance_amount <= 0) {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
                $order->save(false);
            } else {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_PENDING;
                $order->save(false);
            }

            // Update order status to completed

            // 🔔 Step 5: Notifications
            $scheduleDate = $order->schedule_date ?? 'N/A';
            $scheduleTime = $order->schedule_time ?? 'N/A';

            // ➤ User Notification
            Yii::$app->notification->PushNotification(
                $order->id,
                $userId,
                Yii::t('app', 'Order Payment Successful'),
                Yii::t('app', 'Your order (ID: #{id}) has been paid using your wallet.', ['id' => $order->id]),
                'redirect'
            );

            // ➤ Vendor Notification
            if ($order->vendorDetails && $order->vendorDetails->user_id) {
                if (! empty($order->is_next_visit)) {
                    $notificationMessage = Yii::t('app', 'New Paid Order Received for Next Visit');
                    $end_key             = 'next_visit';
                } else {
                    $notificationMessage = Yii::t('app', 'New Paid Order Received');
                    $end_key             = 'redirect';
                }

                Yii::$app->notification->PushNotification(
                    $order->id,
                    $order->vendorDetails->user_id,
                    Yii::t('app', $notificationMessage),
                    Yii::t('app', 'Order #{id} has been placed and paid. Scheduled for {date} at {time}.', [
                        'id'   => $order->id,
                        'date' => $scheduleDate,
                        'time' => $scheduleTime,
                    ]),
                    $end_key
                );
            }

            Orders::markChildOrdersAsPaid($order->id);


            // ✅ Step 6: Final Response
            $response['status']   = self::API_OK;
            $response['details']  = $order->asJson();
            $response['order_id'] = $order->id;
        } catch (\Throwable $e) {
            $response['status'] = self::API_NOK;
            $response['error']  = Yii::t('app', 'Unexpected error: {msg}', ['msg' => $e->getMessage()]);
            Yii::error("Wallet payment error: " . $e->getMessage(), __METHOD__);
        }

        return $this->sendJsonResponse($response);
    }

    public function actionOnlineMode()
    {
        $data       = [];
        $post       = Yii::$app->request->post();
        $headers    = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth       = new AuthSettings();
        $user_id    = $auth->getAuthSession($headers);
        $use_wallet = ! empty($post['use_wallet']) ? $post['use_wallet'] : false;

        try {
            // Check user authentication 
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', "Invalid or missing AuthCode"));
            }

            // Find cart for the user
            $cart = Cart::find()->where(['user_id' => $user_id])->one();
            if (empty($cart)) {
                throw new NotFoundHttpException(Yii::t('app', "No cart found for this user."));
            }

            // Save the order
            $order = (new Orders())->saveOrderByCart(Orders::TYPE_ONLINE, $user_id);
            if (! empty($order['status']) && $order['status'] == self::API_NOK) {
                $data['status'] = self::API_NOK;
                $data['error']  = $order['error'];
                return $this->sendJsonResponse($data);
            }
            $walletBalance = Wallet::getAvailableBalance($user_id) ?? 0;
            if (($use_wallet === true || $use_wallet === 'true') && $walletBalance > 0) {
                $finalAmount = $order->payable_amount - $walletBalance;
                if ($finalAmount < 1) {
                    $finalAmount = 1;
                }
            } else {
                $finalAmount = $order->payable_amount;
            }

            $Razorpay = Razorpay::createAnOrder($order->id, $finalAmount);

            $razorpay_res = json_decode($Razorpay);

            if (empty($razorpay_res->error)) {
                $order_transaction_details                    = new OrderTransactionDetails();
                $order_transaction_details->order_id          = $order->id;
                $order_transaction_details->razorpay_order_id = $razorpay_res->id;
                $order_transaction_details->amount            = $finalAmount;
                $order_transaction_details->status            = OrderTransactionDetails::STATUS_PENDING;
                $order_transaction_details->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_ONLINE;
                $order_transaction_details->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
                $order_transaction_details->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_ONLINE;
                $order_transaction_details->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();

                $order_transaction_details->save(false);

                if (($use_wallet === true || $use_wallet === 'true') && $walletBalance > 0) {
                    $existingWalletTxn = OrderTransactionDetails::findOne([
                        'order_id'     => $order->id,
                        'payment_source' => OrderTransactionDetails::PAYMENT_TYPE_WALLET,
                        'razorpay_order_id'   => $razorpay_res->id,
                    ]);

                    if (! $existingWalletTxn) {
                        $order_transaction_details_wallet                    = new OrderTransactionDetails();
                        $order_transaction_details_wallet->order_id          = $order->id;
                        $order_transaction_details_wallet->razorpay_order_id = $razorpay_res->id;
                        $order_transaction_details_wallet->amount            = $walletBalance;
                        $order_transaction_details_wallet->status            = OrderTransactionDetails::STATUS_PENDING;
                        $order_transaction_details_wallet->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
                        $order_transaction_details_wallet->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_WALLET;
                        $order_transaction_details_wallet->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_WALLET;
                        $order_transaction_details_wallet->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();

                        $order_transaction_details_wallet->save(false);
                    }
                }

                $data['status']       = self::API_OK;
                $data['details']      = $order->asJson();
                $data['razorpay_res'] = $razorpay_res;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = ! empty($razorpay_res->error->description) ? $razorpay_res->error->description : 'Invalid request';
            }
        } catch (UnauthorizedHttpException $e) {
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
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionPendingAmountOnlineMode()
    {
        $data       = [];
        $post       = Yii::$app->request->post();
        $headers    = Yii::$app->request->headers->get('auth_code') ?? Yii::$app->request->getQueryParam('auth_code');
        $auth       = new AuthSettings();
        $user_id    = $auth->getAuthSession($headers);
        $order_id   = $post['order_id'] ?? null;
        $use_wallet = isset($post['use_wallet']) ? $post['use_wallet'] : false;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', "Invalid or missing AuthCode"));
            }

            $order = Orders::findOne(['id' => $order_id, 'user_id' => $user_id]);

            if (! $order) {
                throw new NotFoundHttpException(Yii::t('app', "Order not found for this user."));
            }

            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);

            $order->refresh();

            $walletBalance = Wallet::getAvailableBalance($user_id) ?? 0;
            $finalAmount   = $order->payable_amount;



            // Create Razorpay order
            $razorpayResponse = Razorpay::createAnOrder($order->id, $finalAmount);
            $razorpay_res     = json_decode($razorpayResponse);

            if (empty($razorpay_res->error)) {
                // Save online transaction
                $transaction                    = new OrderTransactionDetails();
                $transaction->order_id          = $order->id;
                $transaction->razorpay_order_id = $razorpay_res->id;
                $transaction->amount            = $finalAmount;
                $transaction->status            = OrderTransactionDetails::STATUS_PENDING;
                $transaction->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
                $transaction->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_ONLINE;
                $transaction->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_ONLINE;
                $transaction->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();

                if (! $transaction->save(false)) {
                    throw new ServerErrorHttpException(Yii::t('app', 'Failed to save transaction details.'));
                }



                // Use wallet if allowed
                if (($use_wallet === true || $use_wallet === 'true') && $walletBalance > 0) {
                    $finalAmount -= $walletBalance;
                    if ($finalAmount < 1) {
                        $finalAmount = 1;
                    }

                    // Save wallet transaction if not already saved
                    $existingWalletTxn = OrderTransactionDetails::findOne([
                        'order_id' => $order->id,
                        'payment_source' => OrderTransactionDetails::PAYMENT_TYPE_WALLET,
                        'razorpay_order_id' => $razorpay_res->id
                    ]);

                    if (! $existingWalletTxn) {
                        $walletTxn                    = new OrderTransactionDetails();
                        $walletTxn->order_id          = $order->id;
                        $walletTxn->razorpay_order_id = $razorpay_res->id;
                        $walletTxn->amount            = $walletBalance;
                        $walletTxn->status            = OrderTransactionDetails::STATUS_PENDING;
                        $walletTxn->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
                        $walletTxn->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_WALLET;
                        $walletTxn->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_WALLET;
                        $walletTxn->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();

                        $walletTxn->save(false);
                    }
                }


                $data['status']       = self::API_OK;
                $data['details']      = $order->asJson();
                $data['razorpay_res'] = $razorpay_res;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = $razorpay_res->error->description ?? Yii::t('app', 'Razorpay responded with an error.');
            }
        } catch (\Throwable $e) {
            Yii::error("Unexpected error: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionOrderConformation()
    {
        $data              = [];
        $post              = Yii::$app->request->post();
        $headers           = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth              = new AuthSettings();
        $user_id           = $auth->getAuthSession($headers);
        $order_id          = $post['order_id'] ?? null;
        $payment_id        = $post['payment_id'] ?? null;
        $razorpay_order_id = $post['razorpay_order_id'] ?? null;

        try {
            // Authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', "Invalid or missing AuthCode"));
            }

            // Required fields
            if (empty($order_id) || empty($payment_id)) {
                throw new BadRequestHttpException(Yii::t('app', "Missing order_id or payment_id."));
            }

            // Fetch order
            $order = Orders::findOne(['id' => $order_id]);
            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t('app', "Order not found."));
            }

            // Fetch payment details
            $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            if (! empty($fetchPaymentDetails->error)) {
                Yii::error("Razorpay fetch error: " . json_encode($fetchPaymentDetails), __METHOD__);
                throw new ServerErrorHttpException(Yii::t('app', "Error fetching payment details: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']));
            }

            // Attempt payment capture if not already captured
            if ($fetchPaymentDetails->status !== 'captured') {
                Razorpay::capturePayment($fetchPaymentDetails->amount, $payment_id);
                $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));

                if ($fetchPaymentDetails->status !== 'captured') {
                    Yii::error("Payment capture failed: " . json_encode($fetchPaymentDetails), __METHOD__);
                    throw new ServerErrorHttpException(Yii::t('app', "Payment capture failed: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']));
                }
            }

            // Process payment success
            if ($fetchPaymentDetails->status === 'captured') {
                $order_transaction = OrderTransactionDetails::find()
                    ->where(['order_id' => $order_id])
                    ->andWhere(['razorpay_order_id' => $razorpay_order_id])
                    ->andWhere(['payment_source' => OrderTransactionDetails::PAYMENT_SOURCE_ONLINE])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                if ($order_transaction) {
                    $order_transaction->status     = OrderTransactionDetails::STATUS_SUCCESS;
                    $order_transaction->payment_id = $payment_id;
                    $order_transaction->save(false);
                } else {
                    Yii::error("Order transaction not found for order_id={$order_id}, razorpay_order_id={$razorpay_order_id}", __METHOD__);
                    throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Order transaction not found for the given order and payment.'));
                }

                $order_transaction_wallet = OrderTransactionDetails::find()
                    ->where(['order_id' => $order_id, 'payment_source' => OrderTransactionDetails::PAYMENT_SOURCE_WALLET, 'razorpay_order_id' => $razorpay_order_id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                if ($order_transaction_wallet) {
                    $order_transaction_wallet->status = OrderTransactionDetails::STATUS_SUCCESS;
                    if ($order_transaction_wallet->save(false)) {
                        $wallet                = new Wallet();
                        $wallet->order_id      = $order->id;
                        $wallet->user_id       = $user_id;
                        $wallet->amount        = $order_transaction_wallet->amount;
                        $wallet->payment_type  = Wallet::PAYMENT_TYPE_DEBIT;
                        $wallet->status        = Wallet::STATUS_COMPLETED;
                        $wallet->method_reason = "Order Placed";
                        $wallet->description   = Yii::t('app', "Order placed successfully. Order ID: #{id}", ['id' => $order->id]);
                        $wallet->save(false);
                    }
                }

                $order_transaction_sum = OrderTransactionDetails::find()
                    ->where(['order_id' => $order_id])
                    ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                    ->sum('amount');

                $payable_amount        = $order->total_w_tax - $order_transaction_sum;
                $order->payment_status = Orders::PAYMENT_DONE;
                $order->payable_amount = Orders::formatMoney($payable_amount) ?? 0;
                $order->balance_amount = Orders::formatMoney($payable_amount) ?? 0;
                $order->save(false);

                User::addReferralBonusOnFirstPaidOrder($user_id);

                if ($order->balance_amount <= 0) {
                    $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
                    $order->save(false);
                } else {
                    $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_PENDING;
                    $order->save(false);
                }

                $order_id   = $order->id;
                Orders::markChildOrdersAsPaid($order_id);

                // Clean up cart and combo packages
                $cart = Cart::findOne(['user_id' => $user_id]);
                if (! empty($cart)) {
                    $appliedCoupon = CouponsApplied::findOne(['cart_id' => $cart->id, 'status' => CouponsApplied::STATUS_INACTIVE]);
                    if ($appliedCoupon) {
                        $appliedCoupon->order_id = $order->id;
                        $appliedCoupon->status   = CouponsApplied::STATUS_ACTIVE;
                        $appliedCoupon->save(false);
                    }
                    $cart->delete();
                }
                $combo_packages_cart = ComboPackagesCart::find()->where(['user_id' => $user_id])->all();
                foreach ($combo_packages_cart as $combo_package) {
                    $combo_package->delete();
                }
            } else {
                $order->payment_status = Orders::PAYMENT_FAILED;
                $order_transaction     = OrderTransactionDetails::findOne(['order_id' => $order_id]);
                if ($order_transaction) {
                    $order_transaction->status     = OrderTransactionDetails::STATUS_FAILED;
                    $order_transaction->payment_id = $payment_id;
                    $order_transaction->save(false);
                }
                $order_transaction_wallet = OrderTransactionDetails::find()
                    ->where(['order_id' => $order_id, 'payment_type' => OrderTransactionDetails::PAYMENT_TYPE_WALLET])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                if ($order_transaction_wallet) {
                    $order_transaction_wallet->status = OrderTransactionDetails::STATUS_FAILED;
                    $order_transaction_wallet->save(false);
                }
            }

            if (! $order->save(false)) {
                Yii::error("Order save failed: " . json_encode($order->getErrors()), __METHOD__);
                throw new ServerErrorHttpException(Yii::t('app', "Failed to update order payment status."));
            }

            // Notifications
            if ($order->payment_status === Orders::PAYMENT_DONE) {
                $existingSuccessCount = OrderTransactionDetails::find()
                    ->where(['order_id' => $order->id])
                    ->andWhere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                    ->count();

                $scheduleDate = $order->schedule_date ?? 'Not scheduled';
                $scheduleTime = $order->schedule_time ?? 'Not scheduled';

                if ($existingSuccessCount == 1) {
                    // New order
                    $title       = "Order Placed";
                    $body        = "Your order (ID: #{$order->id}) has been placed successfully. Scheduled for: $scheduleDate at $scheduleTime";
                    $vendorTitle = 'New Order Received';
                    $vendorBody  = "You have received a new order with ID #{$order->id}. Scheduled for: $scheduleDate at $scheduleTime";
                    Yii::$app->notification->PushNotification($order->id, $order->user_id, $title, $body, true);




                    WhatsApp::sendTemplate($order->user->contact_no, 'estetica_booking_confirmed', [
                        'param_1'   => $order->user->first_name,
                        'param_2'   => $order->schedule_date,
                        'param_3'   => $order->schedule_time,
                        'param_4'   => $order->vendorDetails->address,
                    ]);
                } else {
                    // Remaining payment
                    $title       = "Remaining Payment Received";
                    $body        = "Your payment for order ID #{$order->id} has been successfully completed.";
                    $vendorTitle = 'Remaining Payment Received';
                    $vendorBody  = "A remaining payment has been received for order ID #{$order->id}.";
                    Yii::$app->notification->PushNotification($order->id, $order->user_id, $title, $body, 'redirect');
                }
                Yii::$app->notification->PushNotification($order->id, $order->vendorDetails->user_id, $vendorTitle, $vendorBody, 'redirect');
            }

            WebsocketEmitter::emitToVendor('order_created', (int) $order->vendorDetails->id, $order->asJson(), [
                'source'  => 'yii2',
                'version' => '1.0',
            ]);

            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();

            $data['status']       = self::API_OK;
            $data['details']      = $order->asJson();
            $data['order_id']     = $order->id;
            $data['razorpay_res'] = $fetchPaymentDetails;
        } catch (UnauthorizedHttpException $e) {
            Yii::error('Unauthorized: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            Yii::error('Not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            Yii::error('Bad request: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            Yii::error('Server error: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            Yii::error('Unexpected error: ' . $e->getMessage() . ' ' . $e->getTraceAsString(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }



    private function sendOrderNotifications($order)
    {
        $scheduleDate = $order->schedule_date ?? 'Not scheduled';
        $scheduleTime = $order->schedule_time ?? 'Not scheduled';

        Yii::$app->notification->PushNotification(
            $order->id,
            $order->user_id,
            "Order Placed",
            "Your order (ID: #{$order->id}) has been placed successfully. Scheduled for: $scheduleDate at $scheduleTime",
            'redirect'
        );

        Yii::$app->notification->PushNotification(
            $order->id,
            $order->vendorDetails->user_id,
            'New Order Received',
            "You have received a new order with ID #{$order->id}. Scheduled for: $scheduleDate at $scheduleTime",
            'redirect'
        );
    }

    public function actionPaymentConformationWebhooks()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $rawBody   = file_get_contents('php://input');
        $signature = Yii::$app->request->headers->get('X-Razorpay-Signature');

        try {
            // 0) Verify Razorpay webhook signature
            $webhookSecret = Yii::$app->params['razorpay_webhook_secret'] ?? 'CHANGE_ME';
            if (! $this->verifyRazorpaySignature($rawBody, $signature, $webhookSecret)) {
                throw new \yii\web\UnauthorizedHttpException('Invalid webhook signature');
            }

            // 1) Decode JSON
            $payload = json_decode($rawBody);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \yii\web\BadRequestHttpException('Invalid JSON payload');
            }

            $event = $payload->event ?? null;
            if (! in_array($event, ['payment.authorized', 'payment.captured'], true)) {
                return ['status' => 'ignored', 'message' => 'Event not handled'];
            }

            $payment_id = $payload->payload->payment->entity->id ?? null;
            if (! $payment_id) {
                throw new \yii\web\BadRequestHttpException('Missing payment ID');
            }

            // 2) Fetch payment details from Razorpay
            $payment = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            if (! empty($payment->error)) {
                throw new \yii\web\ServerErrorHttpException('Fetch payment failed: ' . ($payment->error->description ?? 'Unknown'));
            }

            // 3) If only authorized, capture now
            if ($payment->status === 'authorized') {
                $capture = json_decode(Razorpay::capturePayment($payment->amount, $payment_id));
                if (! empty($capture->error)) {
                    throw new \yii\web\ServerErrorHttpException('Capture failed: ' . ($capture->error->description ?? 'Unknown'));
                }
                // Refresh payment details
                $payment = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            }

            if ($payment->status !== 'captured') {
                return ['status' => 'error', 'message' => 'Payment not captured'];
            }

            $razorpay_order_id = $payment->order_id ?? null;
            if (! $razorpay_order_id) {
                throw new \yii\web\BadRequestHttpException('Missing Razorpay order_id');
            }

            // 4) Find our transaction by Razorpay order id
            /** @var OrderTransactionDetails $orderTxn */
            $orderTxn = OrderTransactionDetails::find()
                ->where(['razorpay_order_id' => $razorpay_order_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if (! $orderTxn) {
                throw new \yii\web\NotFoundHttpException('Order transaction not found');
            }

            /** @var Orders $order */
            $order = Orders::findOne(['id' => $orderTxn->order_id]);
            if (! $order) {
                throw new \yii\web\NotFoundHttpException('Order not found');
            }

            // 5) Idempotency: if already marked success, exit early
            if ((int) $orderTxn->status === OrderTransactionDetails::STATUS_SUCCESS) {
                return ['status' => 'ok', 'message' => 'Already processed'];
            }

            // 6) Mark ONLINE transaction success + payment_id
            $orderTxn->payment_id = $payment_id;
            $orderTxn->status     = OrderTransactionDetails::STATUS_SUCCESS;
            $orderTxn->save(false);

            // 7) If there is a WALLET txn, mark it success and add wallet ledger
            $walletTxn = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'payment_source' => OrderTransactionDetails::PAYMENT_SOURCE_WALLET, 'razorpay_order_id' => $razorpay_order_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($walletTxn && (int) $walletTxn->status !== OrderTransactionDetails::STATUS_SUCCESS) {
                $walletTxn->status = OrderTransactionDetails::STATUS_SUCCESS;
                if ($walletTxn->save(false)) {
                    $wallet                = new Wallet();
                    $wallet->order_id      = $order->id;
                    $wallet->user_id       = $order->user_id;
                    $wallet->amount        = $walletTxn->amount;
                    $wallet->payment_type  = Wallet::PAYMENT_TYPE_DEBIT;
                    $wallet->status        = Wallet::STATUS_COMPLETED;
                    $wallet->method_reason = "Order Placed";
                    $wallet->description   = Yii::t('app', "Order placed successfully. Order ID: #{id}", ['id' => $order->id]);
                    $wallet->save(false);
                }
            }

            // 8) Recompute amounts and set payment statuses
            $successSum = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');

            $payable   = ($order->total_w_tax ?? 0) - ($successSum ?? 0);
            $formatted = Orders::formatMoney($payable) ?? 0;

            $order->payment_status = Orders::PAYMENT_DONE;
            $order->payable_amount = $formatted;
            $order->balance_amount = $formatted;
            $order->save(false);

            User::addReferralBonusOnFirstPaidOrder($order->user_id);

            if ($order->balance_amount <= 0) {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
            } else {
                $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_PENDING;
            }
            $order->save(false);

            // 9) Sync parent/child orders (if any)
            if (! empty($order->parent_order_id)) {
                $children = Orders::find()->where(['parent_order_id' => $order->parent_order_id])->all();
                foreach ($children as $child) {
                    $child->payment_status = Orders::PAYMENT_DONE;
                    $child->save(false);
                }
            }

            // 10) Cleanup cart & coupons
            $cart = Cart::findOne(['user_id' => $order->user_id]);
            if ($cart) {
                $appliedCoupon = CouponsApplied::findOne(['cart_id' => $cart->id, 'status' => CouponsApplied::STATUS_INACTIVE]);
                if ($appliedCoupon) {
                    $appliedCoupon->order_id = $order->id;
                    $appliedCoupon->status   = CouponsApplied::STATUS_ACTIVE;
                    $appliedCoupon->save(false);
                }
                $cart->delete();
            }
            $comboCarts = ComboPackagesCart::find()->where(['user_id' => $order->user_id])->all();
            foreach ($comboCarts as $cc) {
                $cc->delete();
            }

            // 11) Push notifications (same logic as confirmation)
            $successCount = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->count();

            $scheduleDate = $order->schedule_date ?? 'Not scheduled';
            $scheduleTime = $order->schedule_time ?? 'Not scheduled';

            if ($successCount == 1) {
                // First time success -> New order
                $title       = "Order Placed";
                $body        = "Your order (ID: #{$order->id}) has been placed successfully. Scheduled for: $scheduleDate at $scheduleTime";
                $vendorTitle = 'New Order Received';
                $vendorBody  = "You have received a new order with ID #{$order->id}. Scheduled for: $scheduleDate at $scheduleTime";
                Yii::$app->notification->PushNotification($order->id, $order->user_id, $title, $body, true);
            } else {
                // Additional success -> Remaining payment
                $title       = "Remaining Payment Received";
                $body        = "Your payment for order ID #{$order->id} has been successfully completed.";
                $vendorTitle = 'Remaining Payment Received';
                $vendorBody  = "A remaining payment has been received for order ID #{$order->id}.";
                Yii::$app->notification->PushNotification($order->id, $order->user_id, $title, $body, 'redirect');
            }
            Yii::$app->notification->PushNotification($order->id, $order->vendorDetails->user_id, $vendorTitle, $vendorBody, 'redirect');

            // 12) WebSocket emit to vendor room (generic)
            WebsocketEmitter::emitToVendor('order_created', (int) $order->vendorDetails->id, $order->asJson(), [
                'source'  => 'yii2-webhook',
                'version' => '1.0',
            ]);
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();

            return ['status' => 'success', 'message' => 'Order payment status updated'];
        } catch (\yii\web\HttpException $e) {
            Yii::error('Webhook HTTP error: ' . $e->statusCode . ' ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            Yii::error('Webhook error: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'Unexpected error'];
        }
    }

    /**
     * Verify Razorpay webhook signature.
     */
    private function verifyRazorpaySignature(string $payload, $signature, string $secret): bool
    {
        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        // Many setups compare HMAC hex to hex in their middleware; validate your gateway setting:
        return hash_equals($signature, hash_hmac('sha256', $payload, $secret));
    }

    public function actionPartialPaymentOrders()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $page    = (int) ($post['page'] ?? 0);
        $limit   = 10;
        $offset  = $page * $limit;

        if (! empty($user_id)) {
            $ordersAll = [];

            $query = Orders::find()
                ->where(['user_id' => $user_id])
                ->andWhere(['payment_mode' => Orders::PAYMENT_MODE_PARTIAL])
                ->andWhere(['>=', 'balance_amount', 1])
                ->andWhere(['payment_status' => Orders::PAYMENT_DONE])
                ->andWhere(['not in', 'status', [
                    Orders::STATUS_CANCELLED_BY_OWNER,
                    Orders::STATUS_CANCELLED_BY_USER,
                    Orders::STATUS_CANCELLED_BY_ADMIN,
                    Orders::STATUS_CANCELLED_BY_HOME_VISITORS,
                    Orders::STATUS_CANCELLED,
                ]])
                ->offset($offset)
                ->limit($limit);

            $orders = $query->all();

            foreach ($orders as $order) {
                $ordersAll[] = $order->partialPaymentOrdersasJson();
            }

            if (! empty($ordersAll)) {
                $data['status']  = self::API_OK;
                $data['details'] = $ordersAll;
                $data['page']    = $page;
                $data['count']   = count($ordersAll);
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No partial payment orders found.");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No user found.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUpComingMyOrders()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $page    = $post['page'] ?? 0;

        if (! empty($user_id)) {
            $now       = date('Y-m-d h:i A'); // current datetime for comparison
            $ordersAll = [];

            $endDate = date('Y-m-d', strtotime('+7 days'));
            $start   = date('Y-m-d');

            while (strtotime($start) <= strtotime($endDate)) {
                $query = Orders::find()
                    ->where(['user_id' => $user_id])
                    ->andWhere(['IN', 'status', [Orders::STATUS_ACCEPTED, Orders::STATUS_ASSIGNED_SERVICE_STAFF]])
                    ->andWhere(['schedule_date' => $start]);

                // Compare full datetime: CONCAT(schedule_date, schedule_time) > now
                $query->andWhere("STR_TO_DATE(CONCAT(schedule_date, ' ', schedule_time), '%Y-%m-%d %h:%i %p') > STR_TO_DATE(:now, '%Y-%m-%d %h:%i %p')", [
                    ':now' => $now,
                ]);

                Yii::info($query->createCommand()->getRawSql(), 'debug');

                $orders = $query->all();
                foreach ($orders as $order) {
                    $ordersAll[] = $order->asJson();
                }

                $start = date('Y-m-d', strtotime($start . ' +1 day'));
            }

            if (! empty($ordersAll)) {
                $data['status']  = self::API_OK;
                $data['details'] = $ordersAll;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No accepted upcoming orders found.");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No user found.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionOrderDetails()
    {
        $data     = [];
        $post     = Yii::$app->request->post();
        $headers  = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);
        $order_id = ! empty($post['order_id']) ? $post['order_id'] : '';
        if (! empty($user_id)) {
            $order = Orders::find()->Where(['user_id' => $user_id])
                ->andWhere(['id' => $order_id])
                ->one();
            if (! empty($order)) {
                $data['status']  = self::API_OK;
                $data['details'] = $order->asJson();
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", " No Order found");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", " No user found");
        }
        return $this->sendJsonResponse($data);
    }

    public function actionReel()
    {

        $data     = [];
        $headers  = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth     = new AuthSettings();
        $user_id  = $auth->getAuthSession($headers);
        $post     = Yii::$app->request->post();
        $page     = ! empty($post['page']) ? (int) $post['page'] : 1;
        $pageSize = 20;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // $query = Reels::find()->where(['status' => Reels::STATUS_ACTIVE])->orderBy(['id' => SORT_DESC]);

            $query = Reels::find()
                ->joinWith('vendorDetails') // Assuming relation name is vendorDetails
                ->where([
                    'reels.status'          => Reels::STATUS_ACTIVE,
                    'vendor_details.status' => VendorDetails::STATUS_ACTIVE,
                ])
                ->orderBy(['reels.id' => SORT_DESC]);

            // Create ActiveDataProvider for reels
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page - 1, // Yii2 pagination is 0-based 
                ],
                'sort'       => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);

            // Fetch and format the reels
            $list = [];
            foreach ($dataProvider->getModels() as $reel) {

                // Create a new record in ReelsViewCounts
                $reels_view_counts             = new ReelsViewCounts();
                $reels_view_counts->real_id    = $reel->id;
                $reels_view_counts->user_id    = $user_id;
                $reels_view_counts->ip_address = Yii::$app->request->userIP; // Capture the user's IP address

                if (! $reels_view_counts->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to save reel view count. Please try again later."));
                }

                // Update the reel's view count
                $reel->view_count += 1;
                if (! $reel->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to update reel view count. Please try again later."));
                }

                $list[] = $reel->asJson($user_id);
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
                throw new NotFoundHttpException(Yii::t("app", "No reels found"));
            }
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

    public function actionReportReel()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            if (empty($post['reel_id']) || empty($post['feedback'])) {
                throw new BadRequestHttpException(Yii::t("app", "Reel ID and feedback are required."));
            }

            $reel = Reels::findOne(['id' => $post['reel_id'], 'status' => Reels::STATUS_ACTIVE]);
            if (! $reel) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found."));
            }

            $adminEmail  = Yii::$app->params['adminEmail'];
            $subject     = "Reel Report - ID: " . $post['reel_id'];
            $messageBody = "User ID: " . $user_id . "\n";
            $messageBody .= "Reel ID: " . $post['reel_id'] . "\n";
            $messageBody .= "Feedback: " . $post['feedback'] . "\n";
            $messageBody .= "Reported At: " . date('Y-m-d H:i:s');

            $reel_reports              = new ReelReports();
            $reel_reports->user_id     = $user_id;
            $reel_reports->reel_id     = $post['reel_id'];
            $reel_reports->feedback    = $post['feedback'] ?? null;
            $reel_reports->reported_at = date('Y-m-d H:i:s');
            $reel_reports->status      = ReelReports::STATUS_ACTIVE;
            $reel_reports->save(false);

            // Sending email using Mailgun SMTP
            // $send = Yii::$app->mailer->compose()
            //     ->setFrom([Yii::$app->params['supportEmail'] => "Support"])
            //     ->setTo($adminEmail)
            //     ->setSubject($subject)
            //     ->setTextBody($messageBody)
            //     ->send();

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reel reported successfully.");

            // if ($send) {
            //     $data['status'] = self::API_OK;
            //     $data['message'] = Yii::t("app", "Reel reported successfully.");
            // } else {
            //     throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to send report. Please try again later."));
            // }

        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionLikeReel()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();
        $reel_id = isset($post['reel_id']) ? (int) $post['reel_id'] : null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate reel_id
            $reel = Reels::findOne($reel_id);
            if (! $reel) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found"));
            }

            // Check if user has already liked the reel
            $existingLike = ReelsLikes::find()->where(['reel_id' => $reel_id, 'user_id' => $user_id])->one();

            if ($existingLike) {
                // Unlike the reel
                if (! $existingLike->delete()) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to remove like. Please try again later."));
                }

                // Decrement the like count on the reel
                $reel->like_count -= 1;
                if (! $reel->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to update reel like count. Please try again later."));
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Reel unliked successfully");
            } else {
                // Like the reel
                $like             = new ReelsLikes();
                $like->reel_id    = $reel_id;
                $like->user_id    = $user_id;
                $like->ip_address = Yii::$app->request->userIP;
                $like->created_on = date('Y-m-d'); // Capture the user's IP address
                if (! $like->save()) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to save like. Please try again later."));
                }

                // Increment the like count on the reel
                $reel->like_count += 1;
                if (! $reel->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to update reel like count. Please try again later."));
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Reel liked successfully");
            }

            // Return updated like count
            $data['like_count'] = $reel->like_count;
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

    public function actionShareReel()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();
        $reel_id = isset($post['reel_id']) ? (int) $post['reel_id'] : null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate reel_id
            $reel = Reels::findOne($reel_id);
            if (! $reel) {
                throw new NotFoundHttpException(Yii::t("app", "Reel not found"));
            }

            // Create or update the share count entry in ReelsShareCounts
            $shareCount = ReelShareCounts::find()->where(['real_id' => $reel_id, 'user_id' => $user_id])->one();
            if (! $shareCount) {
                $shareCount          = new ReelShareCounts();
                $shareCount->real_id = $reel_id;
                $shareCount->user_id = $user_id;
                // $shareCount->ip_address = Yii::$app->request->userIP; // Capture the user's IP address
                $shareCount->created_on = date('Y-m-d');

                if (! $shareCount->save()) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to save reel share. Please try again later."));
                }
            }

            // Increment the reel's share count
            $reel->share_count += 1;
            if (! $reel->save(false)) {
                throw new \yii\web\ServerErrorHttpException(Yii::t("app", "Failed to update reel share count. Please try again later."));
            }

            // Return updated share count
            $data['status']      = self::API_OK;
            $data['message']     = Yii::t("app", "Reel shared successfully");
            $data['share_count'] = $reel->share_count; // Return updated share count

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

    public function actionWriteAReview()
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

            // Validate required parameters
            if (empty($post['order_id']) || empty($post['comment']) || empty($post['rating'])) {
                throw new BadRequestHttpException(Yii::t("app", "Missing required fields: order_id, comment, and rating."));
            }

            $order_id    = $post['order_id'];
            $comment     = $post['comment'];
            $description = $post['description'] ?? ''; // Optional
            $rating      = $post['rating'];

            // Validate order existence
            $order = Orders::findOne(['id' => $order_id]);
            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found."));
            }

            $vendor_details_id = $order->vendor_details_id;

            // Check if the rating is within a valid range
            if ($rating < 1 || $rating > 5) {
                throw new BadRequestHttpException(Yii::t("app", "Rating must be between 1 and 5."));
            }

            // Check if the user has already submitted a review for the same order
            $existingReview = ShopReview::findOne(['order_id' => $order_id, 'user_id' => $user_id]);
            if ($existingReview) {
                throw new BadRequestHttpException(Yii::t("app", "You have already submitted a review for this order."));
            }

            // Save the review
            $shop_review                    = new ShopReview();
            $shop_review->vendor_details_id = $vendor_details_id;
            $shop_review->user_id           = $user_id;
            $shop_review->order_id          = $order_id;
            $shop_review->comment           = $comment;
            $shop_review->description       = $description;
            $shop_review->rating            = $rating;
            $shop_review->status            = ShopReview::STATUS_ACTIVE;
            $shop_review->save(false);

            // Calculate and update the vendor's average rating
            $totalReviews = ShopReview::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->count();

            $totalRating = ShopReview::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->sum('rating');

            $avg_rating = $totalRating / $totalReviews;

            $vendor_details = VendorDetails::findOne(['id' => $vendor_details_id]);
            if (! empty($vendor_details)) {
                $vendor_details->avg_rating = $avg_rating;
                if (! $vendor_details->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to update the vendor's average rating."));
                }
            }

            // Successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Review submitted successfully.");
            $data['details'] = $shop_review->asJson();
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

    public function actionGetUserReviews()
    {

        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        // $page = isset($post['page']) ? $post['page'] : 0;
        $latitude  = isset($post['latitude']) ? $post['latitude'] : null;
        $longitude = isset($post['longitude']) ? $post['longitude'] : null;

        // if (! $latitude || ! $longitude) {
        //     throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
        // }

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch all reviews given by the user, with related data eager-loaded
            $reviews = ShopReview::find()
                ->where(['user_id' => $user_id])
                ->orderBy(['created_on' => SORT_DESC])
                ->with(['vendorDetails', 'order', 'user']) // Eager load relationships
                ->all();

            if (empty($reviews)) {
                throw new NotFoundHttpException(Yii::t("app", "No reviews found for this user."));
            }

            // Prepare response data   
            $reviewList = [];
            foreach ($reviews as $review) {
                // Add each review's JSON data to the reviewList array
                $reviewList[] = $review->asJsonReview($user_id, $latitude, $longitude);
            }

            // Successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Reviews fetched successfully.");
            $data['details'] = $reviewList; // Return the populated review list
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

    public function actionCancelBooking()
    {
        $data                           = [];
        $headers                        = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                           = new AuthSettings();
        $user_id                        = $auth->getAuthSession($headers);
        $post                           = Yii::$app->request->post();
        $settings                       = new WebSetting();
        $allow_cancellation_hours_limit = $settings->getSettingBykey('allow_cancellation_hours_limit') ?? 4;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $order_id      = $post['order_id'] ?? null;
            $cancel_reason = $post['cancel_reason'] ?? null;

            if (empty($order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Order ID is required."));
            }

            if (empty($cancel_reason)) {
                throw new BadRequestHttpException(Yii::t("app", "Cancel Reason is required."));
            }

            $order = Orders::find()
                ->where(['id' => $order_id])
                ->andWhere(['user_id' => $user_id])
                ->one();
            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or does not belong to the user."));
            }

            if ($order->status != Orders::STATUS_NEW_ORDER && $order->status != Orders::STATUS_ACCEPTED) {
                throw new BadRequestHttpException(Yii::t("app", "Only new or accepted orders can be canceled."));
            }

            // For accepted orders, enforce time check
            // if ($order->status == Orders::STATUS_ACCEPTED) {  }
            $schedule_datetime_str = $order->schedule_date . ' ' . date('H:i:s', strtotime($order->schedule_time));
            $schedule_datetime     = new \DateTime($schedule_datetime_str);
            $now                   = new \DateTime();

            $hours_difference = ($schedule_datetime > $now) ?
                ($schedule_datetime->getTimestamp() - $now->getTimestamp()) / 3600 : 0;

            if ($hours_difference < $allow_cancellation_hours_limit) {
                throw new BadRequestHttpException(Yii::t("app", "Accepted orders can only be canceled at least {$allow_cancellation_hours_limit} hours before the scheduled time."));
            }

            $order->status        = Orders::STATUS_CANCELLED_BY_USER;
            $order->cancel_reason = $cancel_reason;

            if (! $order->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to cancel the order. Please try again."));
            }

            $amount = OrderTransactionDetails::find()
                ->where(['order_id' => $order->id, 'status' => OrderTransactionDetails::STATUS_SUCCESS])
                ->sum('amount');

            // Refund to wallet
            $wallet                = new Wallet();
            $wallet->user_id       = $order->user_id;
            $wallet->amount        = $amount;
            $wallet->payment_type  = Wallet::STATUS_CREDITED;
            $wallet->method_reason = "Order Cancelled";
            $wallet->description   = Yii::t("app", "Order cancelled. Order ID #") . $order->id;

            $wallet->status = Wallet::STATUS_COMPLETED;

            if (! $wallet->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to process wallet refund."));
            }

            // Vendor notification
            $title = Yii::t("app", "Your Order Cancelled");
            $body  = Yii::t("app", "Your order (#{$order_id}) has been cancelled by the user. Reason: {$cancel_reason}");

            Yii::$app->notification->PushNotification(
                $order_id,
                $order->vendorDetails->user_id,
                $title,
                $body,
                'redirect'
            );

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order canceled successfully.");
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "{message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionBookAgain()
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

            // Check if the order_id is provided
            if (empty($post['order_id'])) {
                throw new BadRequestHttpException(Yii::t("app", "Order ID is required."));
            }

            $order_id = $post['order_id'];

            // Find the original order
            $originalOrder = Orders::findOne(['id' => $order_id, 'user_id' => $user_id]);
            if (empty($originalOrder)) {
                throw new NotFoundHttpException(Yii::t("app", "Original order not found or does not belong to the user."));
            }

            // Create a new cart based on the original order's items
            $cart                    = new Cart();
            $cart->user_id           = $user_id;
            $cart->vendor_details_id = $originalOrder->vendor_details_id;
            $cart->quantity          = $originalOrder->qty;
            $cart->amount            = $originalOrder->sub_total;
            $cart->service_fees      = $originalOrder->service_charge;
            $cart->other_charges     = $originalOrder->processing_charges;
            $cart->tip               = $originalOrder->tip_amt;
            $cart->cgst              = $originalOrder->cgst;
            $cart->sgst              = $originalOrder->sgst;
            $cart->coupon_code       = $originalOrder->voucher_code;
            $cart->coupon_discount   = $originalOrder->voucher_amount;
            $cart->service_date      = $originalOrder->schedule_date;
            $cart->service_time      = $originalOrder->schedule_time;
            $cart->service_address   = $originalOrder->service_address;

            if (! $cart->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save the new cart."));
            }

            // Add the items to the cart
            $orderItems = OrderDetails::findAll(['order_id' => $originalOrder->id]);
            foreach ($orderItems as $orderItem) {
                $cartItem          = new CartItems();
                $cartItem->cart_id = $cart->id;
                $cartItem->user_id = $user_id;

                $cartItem->service_item_id = $orderItem->service_id;
                $cartItem->quantity        = $orderItem->qty;
                $cartItem->amount          = $orderItem->price;

                if (! $cartItem->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to save cart item: {error}", [
                        'error' => json_encode($cartItem->getErrors()),
                    ]));
                }
            }

            // Return success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order re booked successfully.");
            $data['details'] = $cart->asJson();
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

    public function actionGetBookingDetailsById()
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

            // Validate the presence of order_id in the request
            if (empty($post['order_id'])) {
                throw new BadRequestHttpException(Yii::t("app", "Order ID is required."));
            }

            $order_id = $post['order_id'];

            // Find the order by ID and ensure it belongs to the authenticated user
            $originalOrder = Orders::findOne(['id' => $order_id, 'user_id' => $user_id]);
            if (empty($originalOrder)) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or does not belong to the user."));
            }

            // Prepare success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order details retrieved successfully.");
            $data['details'] = $originalOrder->asJson();
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

    public function actionFavoriteShop()
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

            // Check if post data is provided
            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t("app", "No data posted."));
            }

            $vendor_details_id = $post['vendor_details_id'];

            // Check if the shop exists and is active
            $vendorExists = VendorDetails::find()
                ->where(['id' => $vendor_details_id, 'status' => VendorDetails::STATUS_ACTIVE])
                ->exists();

            if (! $vendorExists) {
                throw new NotFoundHttpException(Yii::t("app", "Shop data not found or inactive."));
            }

            // Check if the shop is already favorited by the user
            $shopLike = ShopLikes::findOne(['vendor_details_id' => $vendor_details_id, 'user_id' => $user_id]);

            if ($shopLike) {
                // Shop is already favorited, so remove it (unfavorite)
                if ($shopLike->delete()) {
                    $data['status']  = self::API_OK;
                    $data['message'] = Yii::t("app", "Shop removed from favorites.");
                } else {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to remove shop from favorites."));
                }
            } else {
                // Shop is not favorited yet, so add it
                $save_shop_likes_data                    = new ShopLikes();
                $save_shop_likes_data->vendor_details_id = $vendor_details_id;
                $save_shop_likes_data->user_id           = $user_id;

                if ($save_shop_likes_data->save()) {
                    $data['status']  = self::API_OK;
                    $data['message'] = Yii::t("app", "Shop added to favorites.");
                    $data['details'] = $save_shop_likes_data->asJson();
                } else {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to add shop to favorites."));
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

    public function actionMyFavoriteShops()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $page      = isset($post['page']) ? (int) $post['page'] : 0;
        $latitude  = $post['latitude'] ?? null;
        $longitude = $post['longitude'] ?? null;

        if (! $latitude || ! $longitude) {
            throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
        }

        try {
            $query = VendorDetails::find()
                ->innerJoinWith('shopLikes as sl')
                ->where(['sl.user_id' => $user_id, 'vendor_details.status' => VendorDetails::STATUS_ACTIVE]);

            $provider = new ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => 20,
                    'page'     => $page,
                ],
                'sort'       => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);

            $list = [];
            foreach ($provider->models as $vendor) {
                $list[] = $vendor->asJsonMyFaverats($user_id, $latitude, $longitude);
            }

            $pagination = $provider->pagination;

            if (! empty($list)) {
                $data['status']     = self::API_OK;
                $data['details']    = $list;
                $data['pagination'] = [
                    'totalCount'  => $pagination->totalCount,
                    'pageCount'   => $pagination->getPageCount(),
                    'currentPage' => $pagination->getPage() + 1,
                    'perPage'     => $pagination->getPageSize(),
                ];
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No favorite shops found.");
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

    public function actionWalletTransactions()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Convert 1-based frontend page to 0-based for Yii pagination
        $page = isset($post['page']) ? max(0, ((int) $post['page'] - 1)) : 0;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $availableBalance = Wallet::getAvailableBalance($user_id);

            $query = Wallet::find()
                ->where(['user_id' => $user_id])
                ->andWhere(['status' => Wallet::STATUS_COMPLETED]);

            // Handle start date
            if (! empty($post['start_date'])) {
                $start_date = date('Y-m-d 00:00:00', strtotime($post['start_date']));
                $query->andWhere(['>=', 'created_on', $start_date]);
            }

            // Handle end date
            if (! empty($post['end_date'])) {
                $end_date = date('Y-m-d 23:59:59', strtotime($post['end_date']));
                $query->andWhere(['<=', 'created_on', $end_date]);
            }

            // Optional: Use between if both dates provided
            if (! empty($post['start_date']) && ! empty($post['end_date'])) {
                $start_date = date('Y-m-d 00:00:00', strtotime($post['start_date']));
                $end_date   = date('Y-m-d 23:59:59', strtotime($post['end_date']));
                $query->andWhere(['between', 'created_on', $start_date, $end_date]);
            }

            $walletTransactions = new ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => 20,
                    'page'     => $page,
                ],
                'sort'       => [
                    'defaultOrder' => ['created_on' => SORT_DESC],
                ],
            ]);

            $transactions = [];
            foreach ($walletTransactions->models as $transaction) {
                $transactions[] = $transaction->asJson();
            }

            if (! empty($transactions)) {
                $data['status']            = self::API_OK;
                $data['available_balance'] = $availableBalance;
                $data['transactions']      = $transactions;
                $data['pagination']        = [
                    'total_count'  => $walletTransactions->totalCount,
                    'page_size'    => $walletTransactions->pagination->pageSize,
                    'current_page' => $walletTransactions->pagination->page + 1, // 1-based
                    'page_count'   => $walletTransactions->pagination->getPageCount(),
                ];
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No wallet transactions found.");
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeliveryAddressList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Fetch active delivery addresses for the user
            $delivery_addresses = DeliveryAddress::find()
                ->where([
                    'user_id' => $user_id,
                    'status'  => DeliveryAddress::STATUS_ACTIVE,
                ])
                ->andWhere([
                    'or',
                    ['is_deleted' => 0],
                    ['is_deleted' => null],
                    ['is_deleted' => ''],
                ])
                ->all();

            // Prepare the response
            if (! empty($delivery_addresses)) {
                foreach ($delivery_addresses as $delivery_addresses_data) {
                    $list[] = $delivery_addresses_data->asJson();
                }
                if (! empty($list)) {
                    $data['status']  = self::API_OK;
                    $data['details'] = $list;
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "No active delivery addresses found.");
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No active delivery addresses found.");
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

    public function actionAddOrUpdateDeliveryAddress()
    {
        $data       = [];
        $headers    = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth       = new AuthSettings();
        $user_id    = $auth->getAuthSession($headers);
        $post       = Yii::$app->request->post();
        $address_id = Yii::$app->request->get('address_id', '');

        try {
            // Check if user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Validate required fields
            $requiredFields = ['address', 'location', 'latitude', 'longitude', 'address_label', 'land_mark'];
            foreach ($requiredFields as $field) {
                if (empty($post[$field])) {
                    throw new \InvalidArgumentException(Yii::t("app", "{field} is required.", ['field' => ucfirst($field)]));
                }
            }

            // Check if updating an existing address or creating a new one
            if (! empty($address_id)) {
                $delivery_address = DeliveryAddress::findOne(['id' => $address_id, 'user_id' => $user_id]);
                if (! $delivery_address) {
                    throw new \Exception(Yii::t("app", "Delivery address not found."));
                }
            } else {
                $delivery_address = new DeliveryAddress();
            }

            // Set delivery address properties
            $delivery_address->user_id      = $user_id;
            $delivery_address->address      = $post['address'];
            $delivery_address->location     = $post['location'];
            $delivery_address->floor_number = $post['floor_number'] ?? '';

            $delivery_address->latitude      = $post['latitude'];
            $delivery_address->longitude     = $post['longitude'];
            $delivery_address->address_label = $post['address_label'];
            $delivery_address->land_mark     = $post['land_mark'];
            $delivery_address->status        = DeliveryAddress::STATUS_ACTIVE;

            // Save the delivery address
            if (! $delivery_address->save(false)) {
                throw new \Exception("Error saving delivery address: " . json_encode($delivery_address->errors));
            }

            // Prepare successful response
            $data['status']  = self::API_OK;
            $data['details'] = $delivery_address->asJson();
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\InvalidArgumentException $e) {
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

            // Prepare the query
            $order = Orders::find()
                ->where(['user_id' => $user_id, 'id' => $order_id])
                ->one();

            // Check if the order is found
            if ($order) {
                $data['status']  = self::API_OK;
                $data['details'] = $order->asJson();
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

    public function actionListStoreReviews()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Check if user is authenticated

            // Validate vendor_details_id in the request
            if (empty($post['vendor_details_id'])) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor details ID is required."));
            }

            $vendor_details_id = $post['vendor_details_id'];

            // Pagination
            $page     = ! empty($post['page']) ? (int) $post['page'] : 1;
            $pageSize = ! empty($post['page_size']) ? (int) $post['page_size'] : 10;

            $query = ShopReview::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => ShopReview::STATUS_ACTIVE]);

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
                $reviewsData[] = $review->asJson();
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

    public function actionNotificationsss()
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

            // Get notifications list with pagination
            $pageSize = ! empty($post['page_size']) ? (int) $post['page_size'] : 10;
            $page     = ! empty($post['page']) ? (int) $post['page'] - 1 : 0;

            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => FcmNotification::find()->where(['user_id' => $user_id]),
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page,
                ],
                'sort'       => [
                    'defaultOrder' => ['created_on' => SORT_DESC],
                ],
            ]);

            // Fetch notifications data
            $notifications = $dataProvider->getModels();
            $totalCount    = $dataProvider->getTotalCount();

            // Prepare notification data
            $notificationData = [];
            foreach ($notifications as $notification) {
                $notificationData[] = $notification->asJson();
            }

            // Prepare pagination data
            $pagination = [
                'total_count' => $totalCount,
                'page'        => $dataProvider->pagination->page + 1, // Adjust for 0-based index
                'page_size'   => $pageSize,
                'total_pages' => ceil($totalCount / $pageSize),
            ];

            $FcmNotificationCount = FcmNotification::find()->where(['user_id' => $user_id, 'is_read' => FcmNotification::IS_READ_NO])->count();

            // Prepare response data
            $data['status']               = self::API_OK;
            $data['message']              = 'Notifications retrieved successfully.';
            $data['details']              = $notificationData;
            $data['pagination']           = $pagination;
            $data['FcmNotificationCount'] = $FcmNotificationCount;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'User authentication failed. Please log in.';
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Bad request. Please check your input.';
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Vendor details not found.';
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Server error occurred. Please try again later.';
        } catch (\Exception $e) {
            Yii::error("Error fetching notifications: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred: ' . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionNotifications()
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

            // Get orders associated with the user
            $orderIds = Orders::find()
                ->select('id')
                ->where(['user_id' => $user_id])

                ->column(); // Fetch order IDs as an array   

            if (empty($orderIds)) {
                $data['status']               = self::API_OK;
                $data['message']              = 'No notifications found.';
                $data['details']              = [];
                $data['pagination']           = [];
                $data['FcmNotificationCount'] = 0;
                return $this->sendJsonResponse($data);
            }

            // Get notifications list without pagination
            $query = FcmNotification::find()
                ->where(['user_id' => $user_id])
                ->andWhere(['order_id' => $orderIds]); // Only include notifications for valid orders

            $dataProvider = new \yii\data\ActiveDataProvider([
                'query'      => $query,
                'pagination' => false,
                'sort'       => [
                    'defaultOrder' => ['created_on' => SORT_DESC],
                ],
            ]);

            // Fetch all notifications
            $notifications = $dataProvider->getModels();
            $totalCount    = $dataProvider->getTotalCount();

            // Prepare notification data
            $notificationData = [];
            foreach ($notifications as $notification) {
                $notificationData[] = $notification->asJson();
            }

            // Get unread notification count
            $FcmNotificationCount = FcmNotification::find()
                ->where(['user_id' => $user_id, 'is_read' => FcmNotification::IS_READ_NO])
                ->andWhere(['order_id' => $orderIds]) // Ensure unread notifications belong to valid orders
                ->count();

            // Prepare response data
            $data['status']               = self::API_OK;
            $data['message']              = 'Notifications retrieved successfully.';
            $data['details']              = $notificationData;
            $data['pagination']           = null; // Set to null since pagination is disabled
            $data['FcmNotificationCount'] = $FcmNotificationCount;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'User authentication failed. Please log in.';
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Bad request. Please check your input.';
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Vendor details not found.';
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = 'Server error occurred. Please try again later.';
        } catch (\Exception $e) {
            Yii::error("Error fetching notifications: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred: ' . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
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

    public function actionGetAvailableBalance()
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

            // Fetch available wallet balance
            $wallet['availableWalletAmount'] = Wallet::getAvailableBalance($user_id);

            // Prepare success response
            $data['status']  = self::API_OK;
            $data['details'] = $wallet;
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::error('Unauthorized access: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::error('Data not found: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'Wallet data not found.';
        } catch (\Exception $e) {
            Yii::error('An unexpected error occurred: ' . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred. Please try again later.';
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCreateQrOrder()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];

        $post       = Yii::$app->request->post();
        $headers    = Yii::$app->request->headers;
        $auth_code  = $headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
        $auth       = new AuthSettings();
        $user_id    = $auth->getAuthSession($auth_code);
        $use_wallet = ! empty($post['use_wallet']) ? $post['use_wallet'] : false;
        $product_order_id = null;

        try {
            // ✅ Authentication check
            if (empty($user_id)) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Invalid or missing AuthCode',
                ]);
            }

            $order_id = $post['order_id'] ?? null;
            if (! $order_id) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Missing order_id',
                ]);
            }

            $order = Orders::findOne(['id' => $order_id, 'user_id' => $user_id]);
            if (! $order) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => 'Order not found',
                ]);
            }

            if (! empty($order['status']) && $order['status'] == self::API_NOK) {
                return $this->sendJsonResponse([
                    'status' => self::API_NOK,
                    'error'  => $order['error'],
                ]);
            }
            Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $order->refresh();
            $finalAmount = $order->payable_amount;
            $product_service_order_mappings = ProductServiceOrderMappings::findOne(['order_id' => $order->id]);
            if (!empty($product_service_order_mappings)) {
                $product_order_id  = $product_service_order_mappings->product_order_id;
            }

            $checkPendingAmount = OrderTransactionDetails::checkPendingAmount($order->id, $product_order_id);

            $walletBalance = Wallet::getAvailableBalance($user_id) ?? 0;
            if (($use_wallet == true || $use_wallet == 'true') && $walletBalance > 0) {
                $finalAmount = $order->payable_amount - $walletBalance;
                if ($finalAmount < 1) {
                    $finalAmount = 1;
                }
            }

            // ✅ Call Razorpay to create order
            $razorpayRes = Razorpay::createAnOrder($order->id, $finalAmount);
            $razorpayRes = json_decode($razorpayRes);

            if (! isset($razorpayRes->id)) {
                throw new ServerErrorHttpException($razorpayRes->error->description ?? 'Invalid Razorpay response');
            }

            // ✅ Save transaction details
            $transaction                    = new OrderTransactionDetails();
            $transaction->order_id          = $order->id;
            $transaction->razorpay_order_id = $razorpayRes->id;
            $transaction->amount            = $finalAmount;
            $transaction->status            = OrderTransactionDetails::STATUS_PENDING;
            $transaction->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_QR;
            $transaction->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
            $transaction->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_ONLINE;
            $transaction->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();

            if (! $transaction->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to save transaction details'));
            }

            if (($use_wallet === true || $use_wallet === 'true') && $walletBalance > 0) {
                $order_transaction_details_wallet = OrderTransactionDetails::findOne([
                    'order_id'     => $order->id,
                    'payment_source' => OrderTransactionDetails::PAYMENT_SOURCE_WALLET,
                    'status'       => OrderTransactionDetails::STATUS_PENDING,
                    'razorpay_order_id' => $razorpayRes->id
                ]);

                if (empty($order_transaction_details_wallet)) {
                    $order_transaction_details_wallet   = new OrderTransactionDetails();
                }
                $order_transaction_details_wallet->order_id          = $order->id;
                $order_transaction_details_wallet->razorpay_order_id = $razorpayRes->id;
                $order_transaction_details_wallet->amount            = $walletBalance;
                $order_transaction_details_wallet->transaction_order_id = OrderTransactionDetails::generateUniqueTransactionId();
                $order_transaction_details_wallet->status            = OrderTransactionDetails::STATUS_PENDING;
                $order_transaction_details_wallet->order_type        = Razorpay::ORDER_TYPE_SERVICE_ORDER;
                $order_transaction_details_wallet->payment_type      = OrderTransactionDetails::PAYMENT_TYPE_QR;
                $order_transaction_details_wallet->payment_source    = OrderTransactionDetails::PAYMENT_SOURCE_WALLET;
                $order_transaction_details_wallet->save(false);
            }

            return $this->sendJsonResponse([
                'status'       => self::API_OK,
                'details'      => $order->asJson(),
                'razorpay_res' => $razorpayRes,
            ]);
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVerifyQrPayment()
    {
        $data       = [];
        $post       = Yii::$app->request->post();
        $headers    = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth       = new AuthSettings();
        $user_id    = $auth->getAuthSession($headers);
        $order_id   = $post['order_id'] ?? null;
        $payment_id = $post['payment_id'] ?? null;

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', "Invalid or missing AuthCode"));
            }

            // Validate order and payment ID
            if (empty($order_id) || empty($payment_id)) {
                throw new BadRequestHttpException(Yii::t('app', "Missing order_id or payment_id."));
            }

            // Retrieve the order 
            $order = Orders::findOne(['id' => $order_id]);
            if (empty($order)) {
                throw new NotFoundHttpException(Yii::t('app', "Order not found."));
            }

            // Fetch payment details from Razorpay
            $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));
            if (! empty($fetchPaymentDetails->error)) {
                throw new ServerErrorHttpException(Yii::t('app', "Error fetching payment details: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']));
            }

            // Process payment capture if needed
            if ($fetchPaymentDetails->status !== 'captured') {
                Razorpay::capturePayment($fetchPaymentDetails->amount, $payment_id);
                $fetchPaymentDetails = json_decode(Razorpay::fetchPaymentDetails($payment_id));

                if ($fetchPaymentDetails->status !== 'captured') {
                    throw new ServerErrorHttpException(Yii::t('app', "Payment capture failed: {message}", ['message' => $fetchPaymentDetails->error->description ?? 'Unknown error']));
                }
            }

            // Update order payment status
            if ($fetchPaymentDetails->status === 'captured') {

                $order_transaction = OrderTransactionDetails::find()->where(['order_id' => $order_id])->orderBy(['id' => SORT_DESC])->one();
                if ($order_transaction) {
                    $order_transaction->status     = OrderTransactionDetails::STATUS_SUCCESS;
                    $order_transaction->payment_id = $payment_id;
                    $order_transaction->save(false);
                }

                $order_transaction_sum = OrderTransactionDetails::find()->where((['order_id' => $order_id]))
                    ->andWHere(['status' => OrderTransactionDetails::STATUS_SUCCESS])
                    ->sum('amount');

                $payable_amount        = $order->total_w_tax - $order_transaction_sum;
                $order->payment_status = Orders::PAYMENT_DONE;
                $order->payable_amount = Orders::formatMoney($payable_amount) ?? 0;
                $order->balance_amount = Orders::formatMoney($payable_amount) ?? 0;
                $order->save(false);
                if ($order->balance_amount <= 0) {
                    $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_DONE;
                    $order->save(false);
                } else {
                    $order->fill_payment_status = Orders::FULL_PAYMENT_STATUS_PENDING;
                    $order->save(false);
                }

                // Delete the cart itself
                $cart = Cart::findOne(['user_id' => $user_id]);
                if (! empty($cart)) {

                    $appliedCoupon = CouponsApplied::findOne(['cart_id' => $cart->id, 'status' => CouponsApplied::STATUS_INACTIVE]);
                    if ($appliedCoupon) {

                        $appliedCoupon->order_id = $order->id;
                        $appliedCoupon->status   = CouponsApplied::STATUS_ACTIVE;
                        $appliedCoupon->save(false);
                    }
                    $cart->delete();
                }
            } else {
                $order->payment_status = Orders::PAYMENT_FAILED;

                $order_transaction = OrderTransactionDetails::findOne(['order_id' => $order_id]);
                if ($order_transaction) {
                    $order_transaction->status     = OrderTransactionDetails::STATUS_FAILED;
                    $order_transaction->payment_id = $payment_id;
                    $order_transaction->save(false);
                }
            }

            if (! $order->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', "Failed to update order payment status."));
            }

            // ** Add notification logic after successful order update **
            if ($order->payment_status === Orders::PAYMENT_DONE) {

                // var_dump($order);
                // die();
                $scheduleDate = $order->schedule_date ?? 'Not scheduled';
                $scheduleTime = $order->schedule_time ?? 'Not scheduled';

                // Send notification to the user
                $title = " payment completed successfully";
                $body  = "Your order (ID: #{$order->id}) has paid  successfully.";

                Yii::$app->notification->PushNotification(
                    $order->id,
                    $order->user_id,
                    $title,
                    $body,
                    'redirect'
                );

                // Send notification to the vendor
                $vendorTitle = 'Pending Payment Received';
                $vendorBody  = "You have received a remaining amount with ID #{$order->id}. ";

                Yii::$app->notification->PushNotification(
                    $order->id,
                    $order->vendorDetails->user_id,
                    $vendorTitle,
                    $vendorBody,
                    'redirect'
                );
            }

            // Set response for successful payment
            $data['status']       = self::API_OK;
            $data['details']      = $order->asJson();
            $data['order_id']     = $order->id;
            $data['razorpay_res'] = $fetchPaymentDetails;
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
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
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

    public function actionGetSubscriptionsList()
    {
        $data = [];

        // Fetch all active subscriptions
        $subscriptions = Subscriptions::find()
            ->where(['status' => Subscriptions::STATUS_ACTIVE]) // Example: Only fetch active subscriptions
            ->all();

        // Fetch and format the subscriptions
        $list = [];
        foreach ($subscriptions as $subscription) {
            $list[] = $subscription->asJson(); // Convert subscription object to array
        }

        // Check if there are subscriptions to return
        if (! empty($list)) {
            $data['status']  = self::API_OK;
            $data['details'] = $list;
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No subscriptions found");
        }

        // Send the response in JSON format
        return $this->sendJsonResponse($data);
    }

    public function actionPremiumSubCategory()
    {
        $data   = [];
        $cat_id = Yii::$app->request->get('cat_id');

        try {
            // Ensure the category ID is provided
            if (empty($cat_id)) {
                throw new \yii\web\BadRequestHttpException('Category ID is required.');
            }

            // Fetch active and premium subcategories for the given category ID
            $subCategoryQuery = SubCategory::find()
                ->where(['status' => SubCategory::STATUS_ACTIVE, 'is_premium' => SubCategory::PREMIUM])
                ->andWhere(['main_category_id' => (int) $cat_id])
                ->orderBy(['sortOrder' => SORT_ASC]);

            // Execute the query
            $subCategories = $subCategoryQuery->all();
            // echo $subCategoryQuery->createCommand()->rawSql;

            if (! empty($subCategories)) {
                // Convert subcategories to JSON format
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($subCategory) {
                    return $subCategory->asJson();
                }, $subCategories);
            } else {
                // Handle no subcategories found
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Premium Sub Category Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            // Handle validation errors
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            // Handle general exceptions
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAutoCancelOrders()
    {
        $data = [];

        try {
            // Fetch the current time 
            $currentTime = new \DateTime();

            // Find orders that are still "new" and past their scheduled time and date
            $orders = Orders::find()
                ->where(['status' => Orders::STATUS_NEW_ORDER])
                ->andWhere(['<', "CONCAT(schedule_date, ' ', schedule_time)", $currentTime->format('Y-m-d H:i:s')])
                ->all();

            if (empty($orders)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "No orders to cancel.");
                return $this->sendJsonResponse($data);
            }

            // Process each order and update its status
            foreach ($orders as $order) {
                $order->status        = Orders::STATUS_CANCELLED;
                $order->cancel_reason = Yii::t("app", "Order automatically canceled due to inactivity after scheduled time.");

                if (! $order->save(false)) {
                    throw new ServerErrorHttpException(Yii::t("app", "Failed to cancel some orders. Please try again."));
                }
            }

            // Successful response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "{count} orders canceled successfully.", ['count' => count($orders)]);
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionTestNotification()
    {
        $title = "New Msg For You";
        $body  = "You got a new order check it.";
        Yii::$app->notification->DemoNotification($id = '', 89, $title, $body, "new_order", '1136');
    }

    public function actionScanPayHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            return [
                'status'  => self::API_NOK,
                'message' => 'User authentication failed. Please log in.',
            ];
        }

        $OrderTransactionDetails = OrderTransactionDetails::find()
            ->where([
                'create_user_id' => $user_id,
                'payment_type'   => OrderTransactionDetails::PAYMENT_TYPE_QR,
                'status'         => OrderTransactionDetails::STATUS_SUCCESS,

            ])
            ->orderBy(['created_on' => SORT_DESC])
            ->all();

        $list = [];

        foreach ($OrderTransactionDetails as $scanpay) {
            $list[] = $scanpay->asJson();
        }

        if (! empty($list)) {
            $data['status']  = self::API_OK;
            $data['details'] = $list;
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "No History found");
        }

        return $data;
    }

    public function actionSaveAddress()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            if (! empty($post)) {
                //var_dump($post); exit;

                $model               = new DeliveryAddress();
                $model->user_id      = $user_id;
                $model->address      = $post['address'];
                $model->location     = $post['location'];
                $model->latitude     = $post['latitude'];
                $model->pincode      = $post['pincode'];
                $model->longitude    = $post['longitude'];
                $model->floor_number = isset($post['floor_number']) ? $post['floor_number'] : '';

                $model->address_label = $post['address_label'];
                $model->land_mark     = isset($post['land_mark']) ? $post['land_mark'] : '';
                $model->status        = 1;
                //$model->created_date = date ( "Y-m-d" );

                if ($model->save(false)) {

                    $data['status']  = self::API_OK;
                    $data['details'] = $model;
                } else {
                    $data['message'] = "Fields Cannot Be Empty";
                }
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No Data Post");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = "No User found.";
        }
        return $this->sendJsonResponse($data);
    }

    // Update Delivery Address
    public function actionUpdateAddress()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            if (! empty($post)) {
                //  var_dump($post['address']); exit;

                $model = DeliveryAddress::find()->Where(['id' => $post['id']])->one();
                if (! empty($model)) {
                    $model->user_id       = $user_id;
                    $model->address       = $post['address'];
                    $model->location      = $post['location'];
                    $model->latitude      = $post['latitude'];
                    $model->pincode       = $post['pincode'];
                    $model->longitude     = $post['longitude'];
                    $model->address_label = $post['address_label'];
                    $model->land_mark     = isset($post['land_mark']) ? $post['land_mark'] : '';

                    $model->status = 1;
                    //$model->created_date = date ( "Y-m-d" );

                    if ($model->save()) {

                        $data['status']  = self::API_OK;
                        $data['details'] = $model;
                    } else {
                        $data['status'] = self::API_NOK;
                        $data['error']  = Yii::t("app", "Failed to save");
                    }
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "No Address found");
                }
            } else {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No Data Post");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = "No User found.";
        }
        return $this->sendJsonResponse($data);
    }

    //Delete Address
    public function actionDeleteAddress($id)
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            // Find the address by user ID and address ID
            $model = DeliveryAddress::find()->where(['user_id' => $user_id, 'id' => $id])->one();

            if (! empty($model)) {
                $model->is_deleted = DeliveryAddress::IS_DELETED_YES; // Use constant here 

                if ($model->save(false)) {
                    $data['status']  = self::API_OK;
                    $data['details'] = 'Address deleted successfully';
                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "Failed to delete the address.");
                }
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No address found.");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = "No user found.";
        }

        return $this->sendJsonResponse($data);
    }

    //Get Saved Address

    public function actionMyAddress()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = isset(\Yii::$app->request->headers['auth_code']) ? \Yii::$app->request->headers['auth_code'] : Yii::$app->request->getQueryParam('auth_code');
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {

            $model = DeliveryAddress::find()->Where(['user_id' => $user_id])->andWhere(['status' => DeliveryAddress::STATUS_ACTIVE])
                ->andWhere([
                    'or',
                    ['is_deleted' => null],
                    ['is_deleted' => ''],
                    ['<>', 'is_deleted', DeliveryAddress::IS_DELETED_YES],
                ])

                ->all();

            if (! empty($model)) {

                $data['status']  = self::API_OK;
                $data['details'] = $model;
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t("app", "No address found");
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = "No User found.";
        }
        return $this->sendJsonResponse($data);
    }

    public function actionDisableExpiredOrders()
    {
        $data = [];
        try {
            // Get the current date and time, then subtract 10 hours
            $currentDateTime = new \DateTime();
            $expiredTime     = $currentDateTime->modify('-10 hours')->format('Y-m-d H:i:s');

            // Get expired orders that are still active
            $expiredOrders = Orders::find()
                ->where([
                    'and',
                    ['in', 'status', [
                        Orders::STATUS_NEW_ORDER,
                        Orders::STATUS_ACCEPTED,
                        Orders::STATUS_SERVICE_STARTED,
                        Orders::STATUS_ASSIGNED_SERVICE_STAFF,
                    ]],
                ])
                ->andWhere([
                    'or',
                    ['is_deleted' => null],
                    ['is_deleted' => ''],
                    ['!=', 'is_deleted', Orders::IS_DELETED_YES],
                ])

                ->andWhere(new \yii\db\Expression("
                STR_TO_DATE(CONCAT(schedule_date, ' ', schedule_time), '%Y-%m-%d %I:%i %p') < :expiredTime
            ", [':expiredTime' => $expiredTime]))
                ->all();

            if (empty($expiredOrders)) {
                $data['status']  = self::API_NOK;
                $data['message'] = "No expired ongoing orders found.";
                return $this->sendJsonResponse($data);
            }

            // Collect affected order IDs and assigned staff IDs
            $orderIds = [];
            $staffIds = [];

            foreach ($expiredOrders as $order) {
                $orderIds[] = $order->id;

                // Find the assigned staff for this order
                $assignment = HomeVisitorsHasOrders::findOne(['order_id' => $order->id]);
                if ($assignment && ! in_array($assignment->home_visitor_id, $staffIds)) {
                    $staffIds[] = $assignment->home_visitor_id;
                }

                // Mark order as deleted
                $order->is_deleted = Orders::IS_DELETED_YES;
                $order->save(false);
            }

            // Set affected staff members to "idle"
            if (! empty($staffIds)) {
                Staff::updateAll(
                    ['current_status' => Staff::CURRENT_STATUS_IDLE],
                    ['id' => $staffIds]
                );
            }

            $data['status']  = self::API_OK;
            $data['message'] = count($orderIds) . " expired orders disabled. " . count($staffIds) . " staff set to idle.";
        } catch (\Exception $e) {
            Yii::error("Error disabling expired orders: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = "An unexpected error occurred: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionMyOrders()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $pageSize = 20;
        $page     = isset($post['page']) ? max(0, ((int) $post['page'] - 1)) : 0;

        $status           = $post['status'] ?? '';
        $main_category_id = $post['main_category_id'] ?? '';
        $sort_by_time     = strtolower($post['sort_by_time'] ?? '');
        $sort_by_rate     = strtolower($post['sort_by_rate'] ?? '');

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $query = Orders::find()
                ->where(['user_id' => $user_id])
                ->andWhere([
                    'or',
                    ['is_deleted' => null],
                    ['is_deleted' => ''],
                    ['!=', 'is_deleted', Orders::IS_DELETED_YES],
                ]);

            // Filter by status
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
            } else {
                $query->andWhere(['in', 'status', [
                    Orders::STATUS_NEW_ORDER,
                    Orders::STATUS_ACCEPTED,
                ]]);
            }



            // Filter by category
            if (! empty($main_category_id)) {
                $query->andWhere(['main_category_id' => $main_category_id]);
            }

            // Sorting logic
            if (!empty($sort_by_time) && in_array($sort_by_time, ['asc', 'desc'])) {
                $query->orderBy(new \yii\db\Expression("CONCAT(schedule_date, ' ', schedule_time) " . strtoupper($sort_by_time)));
            } elseif (!empty($sort_by_rate) && in_array($sort_by_rate, ['asc', 'desc'])) {
                $query->orderBy(['total_w_tax' => ($sort_by_rate === 'asc') ? SORT_ASC : SORT_DESC]);
            } else {
                $query->orderBy(['id' => SORT_DESC]); // Default
            }

            $provider = new ActiveDataProvider([
                'query'      => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                    'page'     => $page,
                ],
            ]);

            $orders = [];
            foreach ($provider->models as $order) {
                $orders[] = $order->asJsonMyOrdersUser();
            }

            $data['status']       = self::API_OK;
            $data['currentPage']  = $page + 1;
            $data['pageSize']     = $pageSize;
            $data['totalRecords'] = $provider->totalCount;
            $data['totalPages']   = ceil($provider->totalCount / $pageSize);
            $data['details']      = $orders;
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetServicesByVendorId()
    {
        $data = [];

        // try {
        $post                  = Yii::$app->request->post();
        $headers               = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth                  = new AuthSettings();
        $user_id               = $auth->getAuthSession($headers);
        $vendor_details_id     = $post['vendor_details_id'] ?? null;
        $store_service_type_id = $post['store_service_type_id'] ?? null;

        if (empty($vendor_details_id)) {
            throw new BadRequestHttpException(Yii::t('app', 'vendor_details_id and service_type are required.'));
        }

        $vendor_details = VendorDetails::findOne(['id' => $vendor_details_id]);
        if (empty($vendor_details)) {
            throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
        }

        // Setup pagination

        $request = Yii::$app->request;
        // $page = $request->post('page', $request->get('page', 1));
        $page = 1;
        // $pageSize = min(max(($get['pageSize'] ?? 10), 1), 500);
        //   /test
        $pageSize = 100;

        // Query subcategories with active services
        $query = SubCategory::find()
            ->where(['sub_category.status' => SubCategory::STATUS_ACTIVE])
            ->andWhere(['sub_category.vendor_details_id' => $vendor_details_id])
            ->andWhere(['sub_category.store_service_type_id' => $store_service_type_id]);

        // Pagination
        $pagination = new \yii\data\Pagination([
            'totalCount' => $query->count(),
            'pageSize'   => $pageSize,
            'page'       => $page - 1,
        ]);

        $subcategories = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $list = [];

        foreach ($subcategories as $subcategoryServices) {

            $list[] = $subcategoryServices->customJson($user_id, $post); // Assuming this exists

        }

        if (empty($list)) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'No services found for this vendor.');
        } else {
            $data['status']     = self::API_OK;
            $data['details']    = $list;
            $data['pagination'] = [
                'totalCount'  => $pagination->totalCount,
                'pageCount'   => $pagination->getPageCount(),
                'currentPage' => $pagination->page + 1,
                'pageSize'    => $pagination->pageSize,
            ];
            $data['message'] = Yii::t('app', 'Services retrieved successfully.');
        }
        // } catch (\Exception $e) {
        //     $data['status'] = self::API_NOK;
        //     $data['error'] = Yii::t('app', 'An unexpected error occurred: {message}', ['message' => $e->getMessage()]);
        // }

        return $this->sendJsonResponse($data);
    }



    public function actionHomeSearchOriginal()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $post         = Yii::$app->request->post();
        $cat_id       = isset($post['cat_id']) ? $post['cat_id'] : null;
        $search       = isset($post['search']) ? $post['search'] : null;
        $service_type = isset($post['service_type']) ? $post['service_type'] : null;
        $sort         = isset($post['sort']) ? $post['sort'] : null;
        $latitude     = isset($post['latitude']) ? $post['latitude'] : null;
        $longitude    = isset($post['longitude']) ? $post['longitude'] : null;
        $distance     = ! empty($post['distance']) ? $post['distance'] : 10;

        try {
            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            // Query to fetch nearby vendors based on latitude and longitude
            $vendorQuery = VendorDetails::find()
                ->select([
                    '*',
                    "(6371 * acos(cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(latitude)))) AS distance",
                ])
                ->addParams([
                    ':latitude'  => $latitude,
                    ':longitude' => $longitude,
                ])
                ->having('distance < :distance')
                ->addParams([':distance' => $distance])
                ->where(['vendor_details.status' => VendorDetails::STATUS_ACTIVE])
                ->andWhere(['main_category_id' => (int) $cat_id])
                ->orderBy(['distance' => SORT_ASC]);

            if (! empty($search)) {
                $vendorQuery->andWhere(['like', 'business_name', $search]);
            }

            $vendors = $vendorQuery->all();
            $stores  = ! empty($vendors) ? array_map(function ($vendor) use ($user_id, $latitude, $longitude) {
                return $vendor->asJsonHomeSearch($user_id, $latitude, $longitude);
            }, $vendors) : [];

            if (empty($cat_id)) {
                throw new \yii\web\BadRequestHttpException('Category ID is required.');
            }

            // Fetch active subcategories for the given category ID and apply the search key
            $subCategoryQuery = SubCategory::find()
                ->where(['status' => SubCategory::STATUS_ACTIVE, 'main_category_id' => (int) $cat_id]);

            if (! empty($search)) {
                $subCategoryQuery->andWhere(['like', 'title', $search]);
            }

            $subCategories  = $subCategoryQuery->orderBy(['sortOrder' => SORT_ASC])->all();
            $subCategoryIds = array_map(function ($sub) {
                return $sub->id;
            }, $subCategories);

            Yii::info("SubCategory IDs: " . json_encode($subCategoryIds), 'debug');

            // Fetch services
            $serviceQuery = Services::find()->where(['status' => Services::STATUS_ACTIVE]);

            if (! empty($subCategoryIds)) {
                $serviceQuery->andWhere(['services.sub_category_id' => $subCategoryIds]);
            } elseif (empty($search)) {
                $serviceQuery->andWhere('0=1'); // prevent fetching everything
            }

            if (! empty($search)) {
                $serviceQuery->andWhere(['like', 'service_name', $search]);
            }

            if (! empty($service_type)) {
                if ($service_type == Services::TYPE_WALK_IN) {
                    $serviceQuery->andWhere(['walk_in' => 1]);
                } elseif ($service_type == Services::TYPE_HOME_VISIT) {
                    $serviceQuery->andWhere(['home_visit' => 1]);
                }
            }

            if (! empty($sort) && in_array($sort, ['asc', 'desc'])) {
                $serviceQuery->orderBy(['service_for' => ($sort === 'asc') ? SORT_ASC : SORT_DESC]);
            }

            $services = $serviceQuery->all();

            // Prepare data for subcategories, services, and vendors
            if (! empty($subCategories) || ! empty($services) || ! empty($vendors)) {
                $data['status']  = self::API_OK;
                $data['details'] = [
                    'subCategories' => array_map(function ($subCategory) {
                        return $subCategory->asJson();
                    }, $subCategories),
                    'services'      => array_map(function ($service) {
                        return $service->asJson();
                    }, $services),
                    'stores'        => $stores,
                ];
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Sub Category, Services, or Vendors Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionHomeSearch()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        $cat_id       = $post['cat_id'] ?? null;
        $search       = $post['search'] ?? null;
        $service_type = $post['service_type'] ?? null;
        $sort         = $post['sort'] ?? null;
        $latitude     = $post['latitude'] ?? null;
        $longitude    = $post['longitude'] ?? null;
        $distance     = ! empty($post['distance']) ? (float) $post['distance'] : 100;

        // Vendor pagination
        $vendorPage   = isset($post['vendor_page']) ? max((int) $post['vendor_page'], 1) : 1;
        $vendorLimit  = isset($post['vendor_limit']) ? (int) $post['vendor_limit'] : 10;
        $vendorOffset = ($vendorPage - 1) * $vendorLimit;

        // Subcategory pagination
        $subCatPage   = isset($post['subcategory_page']) ? max((int) $post['subcategory_page'], 1) : 1;
        $subCatLimit  = isset($post['subcategory_limit']) ? (int) $post['subcategory_limit'] : 10;
        $subCatOffset = ($subCatPage - 1) * $subCatLimit;

        // Service pagination
        $servicePage   = isset($post['service_page']) ? max((int) $post['service_page'], 1) : 1;
        $serviceLimit  = isset($post['service_limit']) ? (int) $post['service_limit'] : 10;
        $serviceOffset = ($servicePage - 1) * $serviceLimit;

        try {
            if (! $latitude || ! $longitude) {
                throw new BadRequestHttpException(Yii::t('app', 'Latitude and Longitude are required.'));
            }

            // Vendor query
            $vendorQuery = VendorDetails::find()
                ->alias('v')
                ->innerJoinWith(['services s', 'subCategories sc'])
                ->select([
                    'v.*',
                    "(6371 * acos(cos(radians(:latitude)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(v.latitude)))) AS distance",
                ])
                ->addParams([':latitude' => $latitude, ':longitude' => $longitude])
                ->having('distance < :distance')
                ->addParams([':distance' => $distance])
                ->where(['v.status' => VendorDetails::STATUS_ACTIVE])
                // ->andFilterWhere(['v.main_category_id' => $cat_id])
                // ->groupBy('v.id')
                ->orderBy([
                    'v.is_premium' => SORT_DESC,
                    'v.avg_rating' => SORT_DESC,
                    'distance'     => SORT_ASC,
                ]);

            $vendorQuery->andWhere([
                'or',
                ['like', 'v.business_name', $search . '%', false], // Starts with $search
                // ['like', 's.service_name', $search],
                // ['like', 's.description', $search],
                // ['like', 'sc.title', $search],
            ]);

            $vendorCountQuery = clone $vendorQuery;
            $totalVendors     = (new \yii\db\Query())->from(['c' => $vendorCountQuery])->count();

            $vendors = $vendorQuery->limit($vendorLimit)->offset($vendorOffset)->all();

            $stores = ! empty($vendors) ? array_map(function ($vendor) use ($user_id, $latitude, $longitude) {
                return $vendor->asJsonHomeSearch($user_id, $latitude, $longitude);
            }, $vendors) : [];

            $vendorIds = ! empty($vendors) ? array_map(function ($vendor) {
                return $vendor->id;
            }, $vendors) : [];

            // Subcategories
            $subCategoryQuery = SubCategory::find()
                ->where(['status' => SubCategory::STATUS_ACTIVE]);

            if (! empty($cat_id)) {
                $subCategoryQuery->andFilterWhere(['sub_category.main_category_id' => $cat_id]);
            }

            if ($vendorIds) {
                $subCategoryQuery->andFilterWhere(['in', 'vendor_details_id', $vendorIds]);
            }

            $subCategoryQuery->andWhere(['like', 'title', $search]);

            $totalSubCategories = (clone $subCategoryQuery)->count();
            $subCategories      = $subCategoryQuery->orderBy(['sortOrder' => SORT_ASC])
                ->limit($subCatLimit)->offset($subCatOffset)
                ->all();

            // Services
            $serviceQuery = Services::find()
                ->where(['status' => Services::STATUS_ACTIVE])
                ->andWhere(['in', 'vendor_details_id', $vendorIds]);

            $serviceQuery->andWhere([
                'or',
                ['like', 'service_name', $search],
                ['like', 'services.description', $search],
            ]);

            if (! empty($service_type)) {
                // if ($service_type == Services::TYPE_WALK_IN) {
                //     $serviceQuery->andWhere(['walk_in' => 1]);
                // } elseif ($service_type == Services::TYPE_HOME_VISIT) {
                //     $serviceQuery->andWhere(['home_visit' => 1]);
                // }
                $serviceQuery->andWhere(['type' => $service_type]);
            }

            if (! empty($sort) && in_array($sort, ['asc', 'desc'])) {
                $serviceQuery->orderBy(['service_for' => ($sort === 'asc') ? SORT_ASC : SORT_DESC]);
            }

            $totalServices = (clone $serviceQuery)->count();
            $services      = $serviceQuery->limit($serviceLimit)->offset($serviceOffset)->all();

            if (! empty($subCategories) || ! empty($services) || ! empty($vendors)) {
                $data['status']  = self::API_OK;
                $data['details'] = [
                    'subCategories' => array_map(function ($sc) {
                        return $sc->asJsonSearch();
                    }, $subCategories),
                    'services'      => array_map(function ($s) {
                        return $s->asJson();
                    }, $services),
                    'stores'        => $stores,
                ];
                $data['pagination'] = [
                    'vendors'       => [
                        'page'        => $vendorPage,
                        'limit'       => $vendorLimit,
                        'total_count' => $totalVendors,
                        'total_pages' => ceil($totalVendors / $vendorLimit),
                        'has_more'    => ($vendorOffset + count($vendors)) < $totalVendors,
                    ],
                    'subCategories' => [
                        'page'        => $subCatPage,
                        'limit'       => $subCatLimit,
                        'total_count' => $totalSubCategories,
                        'total_pages' => ceil($totalSubCategories / $subCatLimit),
                        'has_more'    => ($subCatOffset + count($subCategories)) < $totalSubCategories,
                    ],
                    'services'      => [
                        'page'        => $servicePage,
                        'limit'       => $serviceLimit,
                        'total_count' => $totalServices,
                        'total_pages' => ceil($totalServices / $serviceLimit),
                        'has_more'    => ($serviceOffset + count($services)) < $totalServices,
                    ],
                ];
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No Sub Category, Services, or Vendors Found');
            }
        } catch (\yii\web\BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionCreateWalletPaymentOrder()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated 
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
        }

        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        // Get amount from POST request
        $amount = isset($post['amount']) ? $post['amount'] : null;

        // Validate the required parameters
        if (empty($amount) || ! is_numeric($amount) || $amount <= 0) {

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Invalid request. Amount is required and should be valid.");
        }

        // Generate Razorpay order
        $order = Razorpay::createAnOrder($user_id, $amount);

        // This function will call Razorpay's API to create an order
        Yii::info('Razorpay API Response: ' . $order, __METHOD__);

        // Decode the response
        $order = json_decode($order, true);
        // Check if the order was successfully created 
        if ($order && isset($order['id'])) {

            // Store in wallet with status "inactive" or "pending" initially
            $wallet                    = new Wallet();
            $wallet->razorpay_order_id = $order['id'];
            $wallet->user_id           = $user_id; // User ID
            $wallet->amount            = $amount;
            $wallet->payment_type      = Wallet::STATUS_CREDITED; // Adjust based on your logic
            $wallet->method_reason     = "Order Placed";
            $wallet->description       = Yii::t("app", "Payment initiated for Order ID #") . $order['id'];

            $wallet->status = Wallet::STATUS_PENDING; // Wallet status is pending initially

            // Save the wallet record
            if (! $wallet->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save wallet record."));
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Payment order created successfully.");

            $data['razorpay_order_id'] = $order['id'];

            $data['amount'] = $amount;
        } else {

            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Failed to create Razorpay order.");
        }
        return $this->sendJsonResponse($data);
    }

    public function actionVerifyWalletPayment()
    {
        $data                = Yii::$app->request->post();
        $razorpay_order_id   = isset($data['razorpay_order_id']) ? $data['razorpay_order_id'] : null;
        $razorpay_payment_id = isset($data['razorpay_payment_id']) ? $data['razorpay_payment_id'] : null;

        // Verify payment
        if (! $razorpay_order_id || ! $razorpay_payment_id) {

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Missing Order Id or Payment Id.");
        }

        $payment_details = Razorpay::verifyPayment($razorpay_order_id, $razorpay_payment_id); // This will call Razorpay API to verify the payment

        if ($payment_details && $payment_details['status'] == 'captured') {

            // Payment captured successfully, update wallet status
            $wallet = Wallet::findOne(['razorpay_order_id' => $razorpay_order_id, 'status' => Wallet::STATUS_PENDING]);

            if ($wallet) {

                $wallet->transaction_id = $razorpay_payment_id;
                $wallet->status         = Wallet::STATUS_COMPLETED; // Update wallet status to completed
                $wallet->save(false);                               // Save wallet with completed status

                // Additional logic can go here to update the user's wallet balance if necessary

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Wallet amount successfully added.");
            } else {

                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Wallet record not found.");
            }
        } else {

            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Payment Verification Failed.");
        }
        return $this->sendJsonResponse($data);
    }

    // public function actionBanners()
    // {
    //     $data = [];
    //     $cat_id = Yii::$app->request->get('cat_id');
    //     $is_featured = Yii::$app->request->get('is_featured');

    //     try {

    //         // Initialize query to fetch banners with active vendors
    //         if (!empty($cat_id)) {
    //             $query = Banner::find()
    //                 ->alias('b')
    //                 ->where([
    //                     'b.status' => Banner::STATUS_ACTIVE,
    //                     'b.main_category_id' => (int)$cat_id

    //                 ])
    //                 ->orderBy(['b.sort_order' => SORT_ASC]);
    //         } else {

    //             $query = Banner::find()
    //                 ->alias('b')
    //                 ->joinWith('vendorDetails vd') // Assuming relation name is vendorDetails
    //                 ->where([
    //                     'b.status' => Banner::STATUS_ACTIVE,
    //                     'vd.status' => VendorDetails::STATUS_ACTIVE
    //                 ])
    //                 ->orderBy(['b.sort_order' => SORT_ASC]);
    //         }

    //         $banners = $query->all();

    //         if (empty($banners)) {
    //             throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No banners found for the given criteria.'));
    //         }

    //         $data = [
    //             'status' => self::API_OK,
    //             'message' => Yii::t('app', 'Banners retrieved successfully.'),
    //             'details' => array_map(function ($banner) {
    //                 return $banner->asJson();
    //             }, $banners)
    //         ];
    //     } catch (Exception $e) {
    //         $data = [
    //             'status' => self::API_NOK,
    //             'error' => $e->getMessage()
    //         ];
    //     } catch (\Exception $e) {
    //         $data = [
    //             'status' => self::API_NOK,
    //             'error' => Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()])
    //         ];
    //     }

    //     return $this->sendJsonResponse($data);
    // }

    public function actionBanners()
    {
        $data        = [];
        $cat_id      = Yii::$app->request->get('cat_id');
        $is_featured = Yii::$app->request->get('is_featured');

        try {
            $now = date('H:i:s');

            // Setup base query
            $query = Banner::find()
                ->alias('b')
                ->joinWith(['vendorDetails vd', 'bannerTimings bt']) // Ensure relation 'bannerTimings' exists
                ->where([
                    'b.status'  => Banner::STATUS_ACTIVE,
                    'vd.status' => VendorDetails::STATUS_ACTIVE,
                    'bt.status' => 1, // assuming 1 = active
                ])
                ->andWhere(['<=', 'bt.start_time', $now])
                ->andWhere(['>=', 'bt.end_time', $now])
                ->orderBy(['b.sort_order' => SORT_ASC])
                ->groupBy('b.id'); // in case of multiple timings

            if (! empty($cat_id)) {
                $query->andWhere(['b.main_category_id' => (int) $cat_id]);
            }

            if ($is_featured !== null) {
                $query->andWhere(['b.is_featured' => (int) $is_featured]);
            }

            $banners = $query->all();

            if (empty($banners)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No banners found for the given criteria.'));
            }

            $data = [
                'status'  => self::API_OK,
                'message' => Yii::t('app', 'Banners retrieved successfully.'),
                'details' => array_map(function ($banner) {
                    return $banner->asJson();
                }, $banners),
            ];
        } catch (\yii\web\NotFoundHttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionRunSkinAnalysis()
    {
        $uploadedFile = UploadedFile::getInstanceByName('image');

        if (! $uploadedFile) {
            return $this->asJson(['status' => 'error', 'message' => 'No image uploaded']);
        }

        // Save the uploaded image
        $uploadPath = Yii::getAlias('@app/web/uploads/');
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $imagePath = $uploadPath . uniqid('skin_') . '.' . $uploadedFile->extension;
        if (! $uploadedFile->saveAs($imagePath)) {
            return $this->asJson(['status' => 'error', 'message' => 'Failed to save uploaded image']);
        }

        // Determine OS & Python path
        $os = strtolower(PHP_OS);
        if (strpos($os, 'win') !== false) {
            $python = 'python'; // Ensure it's added to environment variables
        } else {
            $whichPython = trim(shell_exec('which python3'));
            $python      = $whichPython ?: '/usr/bin/python3'; // fallback
        }

        // Python script path
        $scriptPath = Yii::getAlias('@app/analyze_skin.py');

        if (! file_exists($scriptPath)) {
            return $this->asJson(['status' => 'error', 'message' => 'Python script not found']);
        }

        // Command
        $cmd    = escapeshellcmd("$python $scriptPath $imagePath");
        $output = shell_exec($cmd);

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE || isset($result['error'])) {
            return $this->asJson([
                'status'  => 'error',
                'message' => $result['error'] ?? 'Failed to parse Python output.',
            ]);
        }

        return $this->asJson([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    public function actionStaffByStoreId()
    {

        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));

        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated 

        $post = Yii::$app->request->post();
        // Get amount from POST request
        $vendor_details_id = ! empty($post['vendor_details_id']) ? $post['vendor_details_id'] : null;
        $staff             = Staff::find()->where(['vendor_details_id' => $vendor_details_id, 'status' => Staff::STATUS_ACTIVE])->all();
        if (! empty($staff)) {
            foreach ($staff as $staff_data) {
                $list[] = $staff_data->asJson();
            }
            $data['status']  = self::API_OK;
            $data['details'] = $list;
            $data['message'] = Yii::t("app", "Staff retrieved successfully.");
        } else {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "No staff found for this vendor.");
            return $this->sendJsonResponse($data);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionStoreGallery()
    {
        $data = [];
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $post              = Yii::$app->request->post();
            $vendor_details_id = $post['vendor_details_id'] ?? null;

            if (empty($vendor_details_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Vendor ID is required.'));
            }

            $business_images = BusinessImages::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => Staff::STATUS_ACTIVE])
                ->all();

            $reels = Reels::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'status' => Staff::STATUS_ACTIVE])
                ->all();

            if (empty($business_images) && empty($reels)) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'No gallery content found for this vendor.'));
            }

            $list = [
                'images' => array_map(function ($img) {
                    return $img->asJson();
                }, $business_images),
                'reels'  => array_map(function ($reel) {
                    return $reel->asJson();
                }, $reels),
            ];

            $data = [
                'status'  => self::API_OK,
                'details' => $list,
                'message' => Yii::t('app', 'Gallery retrieved successfully.'),
            ];
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionComboPacks()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $post              = Yii::$app->request->post();
            $vendor_details_id = $post['vendor_details_id'] ?? null;

            if (empty($vendor_details_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Vendor ID is required.'));
            }

            $combo_packages = ComboPackages::find()
                ->innerJoinWith('comboServices')
                ->where(['combo_packages.vendor_details_id' => $vendor_details_id])
                ->andWhere(['combo_packages.status' => ComboPackages::STATUS_ACTIVE])
                ->andWhere(['combo_services.status' => ComboServices::STATUS_ACTIVE])
                ->all();

            $list = [];

            foreach ($combo_packages as $combo) {
                $list[] = $combo->asJson($user_id); // Assuming this method exists in your model
            }

            $data['status']  = self::API_OK;
            $data['count']   = count($list);
            $data['details'] = ! empty($list) ? $list : (object) [];
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

    private function addComboToCart($combo_package_id, $user_id)
    {
        $combo_package = ComboPackages::findOne(['id' => $combo_package_id, 'status' => ComboPackages::STATUS_ACTIVE]);
        if (! $combo_package) {
            throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Combo package not found.'));
        }

        $qty = 1;

        $vendor_id = $combo_package->vendor_details_id; // Assumes combo belongs to one vendor
        $services  = ComboServices::find()->where(['combo_package_id' => $combo_package_id])->all();
        if (empty($services)) {
            throw new \yii\web\BadRequestHttpException(Yii::t('app', 'No services found in this combo package.'));
        }

        $price = $combo_package->discount_price ?? $combo_package->price;

        $settings = new WebSetting();
        $tax      = $settings->getSettingBykey('tax') ?? 0;
        $cgst     = $tax / 2;
        $sgst     = $tax / 2;
        $conv_fee = $settings->getSettingBykey('conv_fee') ?? 0;

        // Check for existing cart
        $cart = Cart::findOne(['user_id' => $user_id, 'vendor_details_id' => $vendor_id]);

        if (! $cart) {
            $cart                        = new Cart();
            $cart->user_id               = $user_id;
            $cart->vendor_details_id     = $vendor_id;
            $cart->amount                = 0;
            $cart->quantity              = 0;
            $cart->type_id               = Services::TYPE_WALK_IN; // Or decide based on combo
            $cart->cgst                  = $cgst;
            $cart->sgst                  = $sgst;
            $cart->tax                   = $cgst + $sgst;
            $cart->package_order_exist   = true;
            $shop                        = VendorDetails::findOne($vendor_id);
            $cart->service_fees          = $shop->min_service_fee ?? $conv_fee;
            $serviceFeeTax               = number_format(($cart->service_fees * $tax) / 100, 2, '.', '');
            $cart->service_fees_with_tax = number_format($cart->service_fees + $serviceFeeTax, 2, '.', '');
            $cart->save(false);
        }
        $amount = $price;

        // Add all services from combo
        foreach ($services as $serviceItem) {
            $service = Services::findOne([
                'id'     => $serviceItem->services_id,
                'status' => Services::STATUS_ACTIVE,
            ]);

            if (! $service) {
                continue;
            }

            // 🔁 Check if this service already exists in the cart
            $existingItem = CartItems::findOne([
                'cart_id'            => $cart->id,
                'service_item_id'    => $service->id,
                'is_package_service' => 1,
            ]);

            if ($existingItem) {
                // Skip if already added from combo
                continue;
            }

            $this->addOrUpdateCartItem($cart->id, $service->id, $qty, $amount, $user_id, 1);
        }

        // $cart->amount += $amount;
        // $cart->package_amount += $amount;
        // $cart->quantity += $qty;

        if (! $cart->save(false)) {

            $settings                           = new WebSetting();
            $referral_discount_amount           = Orders::calculateReferralDiscount($user_id, $amount);
            $cart->referral_discount_percentage = $settings->getSettingBykey('referral_discount_percentage');
            $cart->referral_discount_amount     = $referral_discount_amount;

            throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to save the cart.'));
        }

        return $cart;
    }

    public function actionAddToCartCombo()
    {
        $data                       = [];
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Decode JSON input from raw body
            $jsonInput = Yii::$app->request->getRawBody();
            $postData  = ! empty($jsonInput) ? json_decode($jsonInput, true) : Yii::$app->request->post();

            // Check if json_decode failed or returned null
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($postData)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Invalid JSON input.'));
            }

            $combo_packages = $postData['combo_packages'] ?? null;

            if (empty($combo_packages) || ! is_array($combo_packages)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Combo package IDs are required in an array.'));
            }

            $carts = [];
            foreach ($combo_packages as $combo) {
                $combo_package_id = $combo['combo_package_id'] ?? null;
                if (empty($combo_package_id)) {
                    throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Combo Package ID is required for each combo.'));
                }

                $combo_package = ComboPackages::findOne(['id' => $combo_package_id, 'status' => ComboPackages::STATUS_ACTIVE]);
                if (! $combo_package) {
                    throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Combo package {id} not found or inactive.', ['id' => $combo_package_id]));
                }

                // Check for vendor conflict
                $existing_cart = Cart::find()->where(['user_id' => $user_id])->andWhere(['!=', 'quantity', 0])->one();
                if ($existing_cart && $existing_cart->vendor_details_id != $combo_package->vendor_details_id) {
                    throw new \yii\web\BadRequestHttpException(Yii::t('app', 'You already have services from a different vendor in your cart.'));
                }

                // Insert or update ComboPackagesCart
                $combo_packages_cart = ComboPackagesCart::findOne([
                    'combo_package_id' => $combo_package_id,
                    'user_id'          => $user_id,
                ]) ?? new ComboPackagesCart();

                $combo_packages_cart->combo_package_id = $combo_package_id;
                $combo_packages_cart->user_id          = $user_id;
                $combo_packages_cart->amount           = $combo_package->discount_price ?? $combo_package->price;
                $combo_packages_cart->status           = ComboPackagesCart::STATUS_ACTIVE;

                if (! $combo_packages_cart->save(false)) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to save combo package cart item for ID {id}.', ['id' => $combo_package_id]));
                }

                // Add services to cart
                $cart = $this->addComboToCart($combo_package_id, $user_id);
            }

            //update cart
            Cart::updateCartTotalsByUser($user_id);

            $cart = Cart::find()->where(['user_id' => $user_id])->one();

            $data['status']  = self::API_OK;
            $data['carts']   = $cart->asJsonAddToCart();
            $data['message'] = Yii::t('app', 'Combo packages added to cart successfully.');
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

    public function actionRemoveCartCombo()
    {
        $data = [];

        try {
            // Authenticate user
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $post                   = Yii::$app->request->post();
            $combo_packages_cart_id = $post['combo_packages_cart_id'] ?? null;

            if (empty($combo_packages_cart_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'ComboPackagesCart ID is required.'));
            }

            // Fetch the combo package cart record
            $combo_packages_cart = ComboPackagesCart::findOne([
                'id'      => $combo_packages_cart_id,
                'user_id' => $user_id,
            ]);

            if (! $combo_packages_cart) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Cart item not found.'));
            }

            $combo_package_id = $combo_packages_cart->combo_package_id;

            // Get vendor ID from combo package
            $combo_package = ComboPackages::findOne($combo_package_id);
            if (! $combo_package) {
                throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Combo package not found.'));
            }

            $vendor_id = $combo_package->vendor_details_id;

            // Step 1: Fetch ComboServices and extract services_id
            $comboServices = ComboServices::find()
                ->select('services_id')
                ->where([
                    'vendor_details_id' => $vendor_id,
                    'combo_package_id'  => $combo_package_id,
                ])
                ->asArray()
                ->all();

            $servicesIds = array_column($comboServices, 'services_id');

            // Only proceed if servicesIds is not empty
            if (! empty($servicesIds)) {

                // Step 2: Fetch CartItems where services_id IN (servicesIds) and other conditions
                $cartItems = CartItems::find()
                    ->where(['in', 'service_item_id', $servicesIds])
                    ->andWhere(['is_package_service' => 1, 'user_id' => $user_id])
                    ->all();

                // Step 3: Delete if any cart items found
                if (! empty($cartItems)) {
                    foreach ($cartItems as $cartItem) {
                        $cartItem->delete();
                    }
                }
            }

            // Delete the combo_packages_cart record
            if (! $combo_packages_cart->delete()) {
                throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to remove combo package from cart.'));
            }

            // Update the cart totals after combo removal
            $cart = Cart::findOne(['user_id' => $user_id, 'vendor_details_id' => $vendor_id]);
            if ($cart) {
                // Sum up remaining service quantities and amounts
                $services_total = CartItems::find()
                    ->where([
                        'cart_id'            => $cart->id,
                        'is_package_service' => 0,
                    ])
                    ->sum('amount') ?? 0;

                $quantity_total = CartItems::find()
                    ->where([
                        'cart_id'            => $cart->id,
                        'is_package_service' => 0, // ← Removed extra comma here!
                    ])
                    ->sum('quantity') ?? 0;

                $combo_quantity_total = ComboPackagesCart::find()
                    ->where([
                        'user_id' => $user_id,
                        // Optionally add vendor_details_id if you need per-vendor counting:
                        // 'vendor_details_id' => $vendor_id,
                        'status'  => ComboPackagesCart::STATUS_ACTIVE,
                    ])
                    ->count();

                // Sum of remaining active combo package amounts
                $combo_total = ComboPackagesCart::find()
                    ->where(['user_id' => $user_id, 'status' => ComboPackagesCart::STATUS_ACTIVE])
                    ->sum('amount') ?? 0;

                // Update cart fields
                $cart->package_amount = $combo_total;
                $cart->amount         = $services_total + $combo_total;
                $cart->quantity       = $quantity_total + $combo_quantity_total;

                // If cart is now empty, delete it
                if ($cart->quantity == 0 && $cart->amount == 0 && $cart->package_amount == 0) {
                    $cart->delete();
                    $data['message'] = Yii::t('app', 'Cart is now empty after removing combo package.');
                } else {
                    $cart->save(false);
                    $data['message'] = Yii::t('app', 'Combo package removed and cart updated successfully.');
                }

                $data['cartQty']    = $cart->quantity ?? 0;
                $data['cartAmount'] = $cart->amount ?? 0;

                $settings                           = new WebSetting();
                $referral_discount_amount           = Orders::calculateReferralDiscount($user_id, $cart->amount);
                $cart->referral_discount_percentage = $settings->getSettingBykey('referral_discount_percentage');
                $cart->referral_discount_amount     = $referral_discount_amount;
            }

            $data['status'] = self::API_OK;
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

    public function actionGetOffersByStoreId()
    {
        $data = [];

        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            $post              = Yii::$app->request->post();
            $vendor_details_id = $post['vendor_details_id'] ?? null; // ✅ Fixed key

            if (empty($vendor_details_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Vendor ID is required.'));
            }
            $currentDate = date('Y-m-d H:i:s');

            $coupon_vendor = Coupon::find()
                ->joinWith('couponVendors as cs')
                ->where(['cs.status' => CouponVendor::STATUS_ACTIVE])
                ->andWhere(['coupon.status' => Coupon::STATUS_ACTIVE])
                ->andWhere(['cs.vendor_details_id' => $vendor_details_id])
                ->andWhere(['<=', 'coupon.start_date', $currentDate])
                ->andWhere([
                    'or',
                    ['>=', 'coupon.end_date', $currentDate],
                    ['coupon.end_date' => null],
                ])
                ->orderBy(['coupon.id' => SORT_DESC])
                ->all();

            $list = [];

            if (! empty($coupon_vendor)) {
                foreach ($coupon_vendor as $coupon) {
                    $list[] = $coupon->asJson();
                }
            }

            $data['status']  = self::API_OK;
            $data['details'] = $list;
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

    public function actionMakeCallToVendor()
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
            $makeAnonymousCall   = $MyOperatorComponent->makeAnonymousCall($user_conract, $vendor_contact);

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

    public function actionDownloadInvoice()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $order_id = Yii::$app->request->post('order_id') ?? Yii::$app->request->getBodyParam('order_id') ?? Yii::$app->request->get('order_id');

            if (empty($order_id)) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => 'Missing order_id',
                ]);
            }

            $order = Orders::find()->where(['id' => $order_id, 'user_id' => $user_id])->one();

            if (! $order) {
                return $this->asJson([
                    'status' => self::API_NOK,
                    'error'  => Yii::t("app", "Order not found."),
                ]);
            }

            $html = $this->renderPartial('orders/invoice-template', ['order' => $order]);

            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', 'application/pdf');
            Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="invoice_' . $order->id . '.pdf"');

            return $pdfContent;
        } catch (UnauthorizedHttpException $e) {
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Yii::error("Invoice generation failed: " . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "An error occurred while generating the invoice."),
            ]);
        }
    }

    public function actionBannerViewCount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $post      = Yii::$app->request->post();
        $banner_id = (int)($post['banner_id'] ?? 0);

        if (empty($banner_id)) {
            return ['status' => self::API_NOK, 'error' => 'banner_id is required.'];
        }

        $banner = Banner::findOne($banner_id);
        if (! $banner) {
            return ['status' => self::API_NOK, 'error' => 'Banner not found.'];
        }

        $sessionId    = $post['session_id'] ?? Yii::$app->request->cookies->getValue('client_session', null);
        $fingerprint  = $post['fingerprint'] ?? null;
        $ip           = Yii::$app->request->userIP;
        $ua           = Yii::$app->request->userAgent;
        $performedAt  = date('Y-m-d H:i:s');

        // Dedupe window for views: avoid counting repeat views in short timeframe (1 hour here)
        $cache = Yii::$app->cache;
        $dedupeKey = sprintf('banner:%d:view:uid:%s:sess:%s', $banner_id, $user_id ?: 'g', $sessionId ?: 'g');
        $dedupeTtl = 3600; // 1 hour, tune as appropriate

        $isUnique = 1;
        try {
            if ($cache->get($dedupeKey)) {
                $isUnique = 0;
            } else {
                $cache->set($dedupeKey, 1, $dedupeTtl);
                $isUnique = 1;
            }
        } catch (\Throwable $e) {
            Yii::error("Cache dedupe error: " . $e->getMessage(), __METHOD__);
            $isUnique = 1;
        }

        // Insert audit row
        $log = new BannerChargeLogs();
        if ($log->hasAttribute('banner_id')) $log->banner_id = $banner_id;
        if ($log->hasAttribute('user_id')) $log->user_id = $user_id;
        if ($log->hasAttribute('action')) $log->action = 'view';
        if ($log->hasAttribute('charge_amount')) $log->charge_amount = 0.5;
        if ($log->hasAttribute('ip_address')) $log->ip_address = $ip;
        if ($log->hasAttribute('user_agent')) $log->user_agent = $ua;
        if ($log->hasAttribute('performed_at')) $log->performed_at = $performedAt;
        if ($log->hasAttribute('session_id')) $log->session_id = $sessionId;
        if ($log->hasAttribute('device_fingerprint')) $log->device_fingerprint = $fingerprint;
        if ($log->hasAttribute('is_unique')) $log->is_unique = $isUnique;
        if ($log->hasAttribute('status')) $log->status = 1;
        if ($log->hasAttribute('create_user_id')) $log->create_user_id = $user_id;
        if ($log->hasAttribute('update_user_id')) $log->update_user_id = $user_id;

        try {
            $log->save(false);
        } catch (\Throwable $e) {
            Yii::error("Failed to save BannerChargeLogs: " . $e->getMessage(), __METHOD__);
        }

        // Fast counters: Redis preferred
        $counterKey = "banner:{$banner_id}:counters";
        try {
            if (!empty(Yii::$app->redis)) {
                Yii::$app->redis->hIncrBy($counterKey, 'views', 1);
                if ($isUnique) {
                    Yii::$app->redis->hIncrBy($counterKey, 'unique_views', 1);
                }
            }
        } catch (\Throwable $e) {
            Yii::error("Redis increment failed: " . $e->getMessage(), __METHOD__);
        }

        // DB fallback: increment banner.views_count if exists
        try {
            if ($isUnique && $banner->hasAttribute('views_count')) {
                $banner->updateCounters(['views_count' => 1]);
            }
        } catch (\Throwable $e) {
            Yii::error("DB updateCounters failed for views: " . $e->getMessage(), __METHOD__);
        }

        return [
            'status'    => self::API_OK,
            'message'   => 'View tracked.',
            'is_unique' => (int)$isUnique
        ];
    }


    public function actionBannerClickCount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        $post      = Yii::$app->request->post();
        $banner_id = (int)($post['banner_id'] ?? 0);

        if (empty($banner_id)) {
            return ['status' => self::API_NOK, 'error' => 'banner_id is required.'];
        }

        $banner = Banner::findOne($banner_id);
        if (! $banner) {
            return ['status' => self::API_NOK, 'error' => 'Banner not found.'];
        }

        // client/session info (optional)
        $sessionId    = $post['session_id'] ?? Yii::$app->request->cookies->getValue('client_session', null);
        $fingerprint  = $post['fingerprint'] ?? null;
        $ip           = Yii::$app->request->userIP;
        $ua           = Yii::$app->request->userAgent;
        $performedAt  = date('Y-m-d H:i:s');

        // Dedupe window: avoid counting duplicate clicks from same user/session in short window (60s)
        $cache = Yii::$app->cache;
        $dedupeKey = sprintf('banner:%d:click:uid:%s:sess:%s', $banner_id, $user_id ?: 'g', $sessionId ?: 'g');
        $dedupeTtl = 600; // seconds — tune as per business logic

        $isUnique = 1;
        try {
            if ($cache->get($dedupeKey)) {
                $isUnique = 0; // already counted in window
            } else {
                $cache->set($dedupeKey, 1, $dedupeTtl);
                $isUnique = 1;
            }
        } catch (\Throwable $e) {
            Yii::error("Cache dedupe error: " . $e->getMessage(), __METHOD__);
            // if cache is unavailable, we conservatively treat as unique
            $isUnique = 1;
        }

        // Insert audit row (always)
        $log = new BannerChargeLogs();
        if ($log->hasAttribute('banner_id')) $log->banner_id = $banner_id;
        if ($log->hasAttribute('user_id')) $log->user_id = $user_id;
        if ($log->hasAttribute('action')) $log->action = 'click';
        if ($log->hasAttribute('charge_amount')) $log->charge_amount = 1;
        if ($log->hasAttribute('ip_address')) $log->ip_address = $ip;
        if ($log->hasAttribute('user_agent')) $log->user_agent = $ua;
        if ($log->hasAttribute('performed_at')) $log->performed_at = $performedAt;
        if ($log->hasAttribute('session_id')) $log->session_id = $sessionId;
        if ($log->hasAttribute('device_fingerprint')) $log->device_fingerprint = $fingerprint;
        if ($log->hasAttribute('is_unique')) $log->is_unique = $isUnique;
        if ($log->hasAttribute('status')) $log->status = 1;
        if ($log->hasAttribute('create_user_id')) $log->create_user_id = $user_id;
        if ($log->hasAttribute('update_user_id')) $log->update_user_id = $user_id;

        try {
            $log->save(false);
        } catch (\Throwable $e) {
            Yii::error("Failed to save BannerChargeLogs: " . $e->getMessage(), __METHOD__);
            // we don't fail the API — continue, but log error for investigation
        }

        // Fast counters: prefer Redis (Yii::$app->redis) and keep a DB fallback (updateCounters) if the banner has appropriate column
        $counterKey = "banner:{$banner_id}:counters";
        try {
            if (!empty(Yii::$app->redis)) {
                Yii::$app->redis->hIncrBy($counterKey, 'clicks', 1);
                if ($isUnique) {
                    Yii::$app->redis->hIncrBy($counterKey, 'unique_clicks', 1);
                }
            }
        } catch (\Throwable $e) {
            Yii::error("Redis increment failed: " . $e->getMessage(), __METHOD__);
        }

        // DB fallback: increment DB counter if attribute exists (atomic)
        try {
            if ($banner->hasAttribute('clicks_count')) {
                $banner->updateCounters(['clicks_count' => 1]);
            } elseif ($banner->hasAttribute('click_count')) {
                $banner->updateCounters(['click_count' => 1]);
            }
        } catch (\Throwable $e) {
            Yii::error("DB updateCounters failed for clicks: " . $e->getMessage(), __METHOD__);
        }

        return [
            'status'    => self::API_OK,
            'message'   => 'Click tracked.',
            'is_unique' => (int)$isUnique
        ];
    }


    public function actionUpdateOrderRatingFlag()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        // Check if user is authenticated 
        if (empty($user_id)) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User not found or unauthorized.');
            return $this->sendJsonResponse($data);
        }

        $post        = Yii::$app->request->post();
        $order_id    = ! empty($post['order_id']) ? $post['order_id'] : null;
        $rating_flag = ! empty($post['rating_flag']) ? $post['rating_flag'] : Orders::RATING_FLAG_NOT_RATED;

        if (empty($order_id)) {

            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'Order ID is required.');
            return $this->sendJsonResponse($data);
        }

        $order = Orders::findOne(['id' => $order_id, 'user_id' => $user_id]);

        if (! $order) {
            throw new \yii\web\NotFoundHttpException(Yii::t('app', 'Order not found.'));
        }

        // Update the rating flag
        $order->rating_flag = $rating_flag;
        if ($order->save(false)) {
            $data['status']  = self::API_OK;
            $data['details'] = Yii::t('app', 'Order rating flag updated successfully.');
        } else {
            throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to update order rating flag.'));
        }

        return $this->sendJsonResponse($data);
    }

    public function actionQuizzes()
    {
        $data = [];

        try {
            // Authenticate user
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Get POST data for filtering
            $post              = Yii::$app->request->post();
            $vendor_details_id = isset($post['vendor_details_id']) ? (int) $post['vendor_details_id'] : null;
            $page              = isset($post['page']) ? max(1, (int) $post['page']) : 1;
            $perPage           = isset($post['per_page']) ? max(1, min(100, (int) $post['per_page'])) : 20;

            // Build query
            $query = Quizzes::find()
                ->where(['status' => Quizzes::STATUS_ACTIVE])
                ->orderBy(['created_on' => SORT_DESC]);

            // Apply vendor filter if provided
            if ($vendor_details_id !== null) {
                $query->andWhere(['vendor_details_id' => $vendor_details_id]);
            }

            // Apply pagination
            $count   = $query->count();
            $quizzes = $query
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->all();

            if (empty($quizzes)) {
                throw new NotFoundHttpException(Yii::t('app', 'No quizzes found.'));
            }

            // Map quizzes to JSON format
            $list = array_map(function ($quiz) {
                return $quiz->asJson();
            }, $quizzes);

            $data = [
                'status'      => self::API_OK,
                'count'       => count($list),
                'total_count' => $count,
                'page'        => $page,
                'per_page'    => $perPage,
                'details'     => $list,
                'message'     => Yii::t('app', 'Quizzes retrieved successfully.'),
            ];
        } catch (\yii\web\HttpException $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionSaveQuizzesResponse()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        try {

            // Check if user is authenticated 
            if (empty($user_id)) {
                $data = [
                    'status' => self::API_NOK,
                    'error'  => Yii::t('app', 'User not found or unauthorized.'),
                ];
                return $this->sendJsonResponse($data);
            }

            $post            = Yii::$app->request->post();
            $quizzesResponse = ! empty($post['quizzesResponse']) ? $post['quizzesResponse'] : null;

            if (empty($quizzesResponse)) {
                $data = [
                    'status' => self::API_NOK,
                    'error'  => Yii::t('app', 'Quizzes response is required.'),
                ];
                return $this->sendJsonResponse($data);
            }

            $quizzesResponse = json_decode($quizzesResponse);
            if (! empty($quizzesResponse)) {
                foreach ($quizzesResponse as $response) {
                    $quizResponse              = new QuizUserAnswers();
                    $quizResponse->user_id     = $user_id;
                    $quizResponse->quiz_id     = $response->quiz_id;
                    $quizResponse->question_id = $response->quiz_questions_id;
                    $quizResponse->answer_id   = $response->answer_id;
                    if (! $quizResponse->save(false)) {
                        throw new ServerErrorHttpException(Yii::t('app', 'Failed to save quizzes response.'));
                    }
                }
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Quizzes response saved successfully.');
            } else {
                $data = [
                    'status' => self::API_NOK,
                    'error'  => Yii::t('app', 'Invalid quizzes response format.'),
                ];
                return $this->sendJsonResponse($data);
            }
        } catch (\Exception $e) {
            $data = [
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'An error occurred: {message}', ['message' => $e->getMessage()]),
            ];
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteCartComboPackages()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! empty($user_id)) {
            $combo_packages_cart = CombopackagesCart::find()->where(['user_id' => $user_id])->all();

            if (! empty($combo_packages_cart)) {
                $cart_items = CartItems::find()->where(['user_id' => $user_id])->andWhere(['is_package_service' => 1])->all();
                if (! empty($cart_items)) {
                    foreach ($cart_items as $cart_item) {
                        $cart_item->delete();
                    }
                }

                foreach ($combo_packages_cart as $combo_package) {
                    $combo_package->delete();
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Combo packages removed successfully.');
            } else {
                $data['status'] = self::API_NOK;
                $data['error']  = Yii::t('app', 'No combo packages found to delete.');
            }
        } else {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'User authentication failed. Please log in.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionApplyReferralCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request                    = Yii::$app->request->post();

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));

        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return ['status' => self::API_NOK, 'error' => 'User not authenticated.'];
        }

        $user = User::findOne(['id' => $user_id]);
        if (! $user) {
            return ['status' => self::API_NOK, 'error' => 'User not found.'];
        }

        $referralCode = $request['referral_code'] ?? null;

        if (! $referralCode) {
            return ['status' => self::API_NOK, 'error' => 'Referral code is required.'];
        }

        if (! empty($user->referral_id)) {
            return ['status' => self::API_NOK, 'error' => 'Referral code already applied.'];
        }

        $referrer = User::find()->where(['referral_code' => $referralCode])->one();

        if (! $referrer) {
            return ['status' => self::API_NOK, 'error' => 'Invalid referral code.'];
        }

        if ($referrer->id == $user_id) {
            return ['status' => self::API_NOK, 'error' => 'You cannot use your own referral code.'];
        }

        $user->referral_id       = $referrer->id;
        $user->show_referral_tab = false;

        if ($user->save(false)) {
            $first_name = $referrer->first_name ?? null;
            $last_name  = $referrer->last_name ?? null;

            $full_name = $first_name . ' ' . $last_name;

            return [
                'status'      => self::API_OK,
                'message'     => 'Referral code applied successfully.',
                'referrer_id' => $referrer->id,
                'person_name' => $full_name,

            ];
        }

        return ['status' => self::API_NOK, 'error' => 'Failed to apply referral code.'];
    }

    public function actionSkipReferral()
    {
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));

        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (! $user_id) {
            return ['status' => self::API_NOK, 'error' => 'User not authenticated.'];
        }

        $user = User::findOne(['id' => $user_id]);
        if (! $user) {
            return ['status' => self::API_NOK, 'error' => 'User not found.'];
        }

        // Check if referral already applied
        if (! empty($user->referral_id)) {
            return ['status' => self::API_NOK, 'error' => 'Referral already applied. Cannot skip.'];
        }

        // Mark referral as skipped
        $user->show_referral_tab = false;

        if ($user->save(false)) {
            return [
                'status'  => self::API_OK,
                'message' => 'Referral skipped successfully.',
            ];
        }

        return ['status' => self::API_NOK, 'error' => 'Failed to skip referral.'];
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

        $post  = Yii::$app->request->post();
        $email = filter_var($post['email'] ?? null, FILTER_VALIDATE_EMAIL);

        if (! $email) {
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
        $otp                   = User::generateOtp();
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
        User::cleanupExpiredOtps($email);

        if (! $model->save(false)) {
            throw new ServerErrorHttpException('Failed to save OTP.');
        }

        // Render email template
        $html = Yii::$app->view->render('@app/modules/api/views/mail/otp', [
            'otp'    => $otp,
            'expiry' => EmailOtpVerifications::OTP_EXPIRY_MINUTES,
        ]);

        // Create mailer message
        $mailer = Yii::$app->mailer->compose()
            ->setFrom(['support@esteticanow.com' => 'EsteticaNow'])
            ->setTo($email)
            ->setSubject('Your OTP Verification Code')
            ->setHtmlBody($html)
            ->setTextBody("Your OTP for verification is: $otp\nThis OTP will expire in " . EmailOtpVerifications::OTP_EXPIRY_MINUTES . " minutes.");

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
        $user = User::find()->where(['id' => $user_id])->one();
        if (! $user) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User Not found.'),
            ]);
        }

        // Verify OTP
        $result = User::verifyOtpEmail($email, $otp);
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

    public function actionTestTemplate()
    {


        $result = \app\components\WhatsApp::getTemplateParameterKeys('welcome_user');

        if ($result['success']) {
            print_r($result['keys']);
        }
    }



    public function actionResetAllOrders()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Fetch all orders (you can add where() if you only want specific ones)
            $orders = Orders::find()->all();
            $count = 0;

            foreach ($orders as $order) {
                Orders::recalculateOrderPrice($order->id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
                $count++;
            }

            return [
                'status' => 'success',
                'message' => "Recalculated prices for {$count} orders",
                'total_orders_processed' => $count
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error recalculating orders: ' . $e->getMessage(),
            ];
        }
    }
}
