<?php
namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\components\OrderAssignmentService;
use app\models\User;
use app\modules\admin\models\Auth;
use app\modules\admin\models\AuthSession;
use app\modules\admin\models\base\TemporaryUsers;
use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\BypassNumbers;
use app\modules\admin\models\ComboOrder;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\Days;
use app\modules\admin\models\EmailOtpVerifications;
use app\modules\admin\models\GuestUserDeposits;
use app\modules\admin\models\HomeVisitorsHasOrders;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\Services;
use app\modules\admin\models\Staff;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\SubCategory;
use app\modules\admin\models\VendorBrands;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\VendorPayout;
use app\modules\admin\models\VendorSuppliers;
use app\modules\admin\models\Wallet;
use app\modules\api\controllers\BKController;
use Exception;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class WebVendorController extends BKController
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
                            'check',
                            'verify-user',
                            'walk-in-immediate-order',
                            'available-slots',
                            'verify-otp-order-create',
                            'pre-booking-order',
                            'register-store',
                            'user-auto-register-otp-verify',
                            'verify-otp-email-mobile',
                            'upload-files',
                            'send-email-otp',
                            'register-managers',
                            'add-or-update-staff-batch',
                            'manager-store-list',
                            'delete-manager',
                            'add-new-service-for-existing-order',
                            'web-vendor-dashboard',
                            'delete-order-services',
                            'send-otp-registration',
                            'send-otp-check'

                        ],

                        'allow'   => true,
                        'roles'   => [ 
                            '@',
                        ],
                    ],
                    [

                        'actions' => [
                            'check',
                            'verify-user',
                            'walk-in-immediate-order',
                            'available-slots',
                            'verify-otp-order-create',
                            'pre-booking-order',
                            'register-store',
                            'user-auto-register-otp-verify',
                            'verify-otp-email-mobile',
                            'upload-files',
                            'send-email-otp',
                            'register-managers',
                            'add-or-update-staff-batch',
                            'manager-store-list',
                            'delete-manager',
                            'add-new-service-for-existing-order',
                            'web-vendor-dashboard',
                            'delete-order-services',
                            'send-otp-registration',
                            'send-otp-check'




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

    //Check Address or Pin code deliverable or not

    public function actionCheck()
    {
        $data = [];

        $headers   = getallheaders();
        $auth_code = isset($headers['auth_code']) ? $headers['auth_code'] : null;
        if ($auth_code == null) {
            $auth_code = \Yii::$app->request->get('auth_code');
        }
        if ($auth_code) {
            $auth_session = AuthSession::find()->where([
                'auth_code' => $auth_code,
            ])->one();
            if ($auth_session) {
                $user           = $auth_session->createUser;
                $data['status'] = self::API_OK;
                $data['detail'] = $user->asJsonVendor();
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
            $data['auth']  = isset($auth_code) ? $auth_code : '';
        }

        return $this->sendJsonResponse($data);
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
        $storesHasUsers = storesHasUsers::find()->where(['guest_user_id' => $user_guest->id,'vendor_details_id' => $vendorDetails->id])->one();

        if (! empty($storesHasUsers)) {
            return $this->sendJsonResponse([
                'status'  => self::API_OK,
                'details' => $user_guest->asJsonUser(),
            ]);
        } else {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t('app', 'User does not have store access.'),
            ]);
        }
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
        $user = User::findOne(['contact_no' => $contact_no, 'user_role' => User::ROLE_USER]);
        if ($user) {
            $data['status']  = self::API_OK;
            $data['details'] = $user->asJsonUser();
        }

        $user                 = new User();
        $user->username       = $contact_no . '@' . User::ROLE_VENDOR . '.com';
        $user->contact_no     = $contact_no;
        $user->unique_user_id = User::generateUniqueUserId('P');
        $user->device_token   = $post['device_token'] ?? null;
        $user->device_type    = $post['device_type'] ?? null;
        $user->referral_code  = "";
        $user->user_role      = User::ROLE_VENDOR;
        $user->status         = User::STATUS_ACTIVE;
        $user->referral_code  = User::generateUniqueReferralCode();
        if (! $user->save(false)) {
            throw new ServerErrorHttpException(Yii::t('app', 'Failed to register user.'));
        }
        $data['status']  = self::API_OK;
        $data['details'] = $user->asJsonUser();

        return $this->sendJsonResponse($data);
    }
 


    public function actionUserAutoRegisterOtpVerify()
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

            if (! $contact_no || ! $session_code || ! $otp_code) {
                throw new BadRequestHttpException(Yii::t('app', 'Missing required parameters.'));
            }

            // Verify OTP with external service
            $send_otp       = Yii::$app->notification->verifyOtp($session_code, $otp_code);
            $send_otp       = json_decode($send_otp, true);
            $bypass_numbers = BypassNumbers::find()->where(['mobile_number' => $contact_no])->one();

            // Bypass the OTP verification for specific numbers 
            if (! empty($bypass_numbers->mobile_number) && $bypass_numbers->mobile_number == $contact_no) {
                $send_otp['Status'] = 'Success';
            }

            if ($send_otp['Status'] == 'Success') {
                // Register a new user
                $existingUser = User::findOne(['contact_no' => $contact_no, 'user_role' => User::ROLE_USER]);
                if ($existingUser) {
                    $data['status']  = self::API_OK;
                    $data['details'] = $existingUser->asJsonUser();
                    return $this->sendJsonResponse($data);

                }

                $newUser                 = new User();
                $newUser->username       = $contact_no . '@' . User::ROLE_USER . '.com';
                $newUser->contact_no     = $contact_no;
                $newUser->unique_user_id = User::generateUniqueUserId();
                $newUser->device_token   = $post['device_token'] ?? null;
                $newUser->device_type    = $post['device_type'] ?? null;
                $newUser->referral_code  = User::generateUniqueReferralCode();
                $newUser->user_role      = User::ROLE_USER;

                if ($newUser->save(false)) {

                    $data['status']  = self::API_OK;
                    $data['details'] = $newUser->asJsonUser();
                } else {
                    throw new ServerErrorHttpException(Yii::t('app', 'Failed to register new user.'));
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

    public function actionRegisterStore()
    {
        $data = [];
        $post = Yii::$app->request->post();

        try {
            // Validate required fields
            $required = ['full_name', 'email', 'contact_no', 'vendor_store_type', 'brand_name', 'brand_logo'];
            foreach ($required as $field) {
                if (empty($post[$field])) {
                    throw new \yii\web\BadRequestHttpException(Yii::t('app', "Missing required field: $field"));
                }
            }

            $transaction = Yii::$app->db->beginTransaction();

            $tempUser                    = new TemporaryUsers();
            $tempUser->username          = $post['contact_no'] . '@' . User::ROLE_VENDOR . '.com';
            $tempUser->contact_no        = $post['contact_no'];
            $tempUser->date_of_birth     = $post['date_of_birth'];
            $tempUser->gender            = $post['gender'];
            $tempUser->unique_user_id    = User::generateUniqueUserId('P');
            $tempUser->first_name        = $post['full_name'];
            $tempUser->email             = $post['email'];
            $tempUser->device_token      = $post['device_token'] ?? 1;
            $tempUser->device_type       = $post['device_type'] ?? 1;
            $tempUser->user_role         = User::ROLE_VENDOR;
            $tempUser->referral_code     = User::generateUniqueReferralCode();
            $tempUser->vendor_store_type = $post['vendor_store_type'];
            $tempUser->brand_name        = $post['brand_name'];
            $tempUser->brand_logo        = $post['brand_logo'] ?? null;
            $tempUser->numbers_stores    = $post['numbers_stores'] ?? 1;

            if (! $tempUser->save(false)) {
                $transaction->rollBack();
                $data['status'] = self::API_NOK;
                $data['error']  = $tempUser->getFirstErrors();
                return $this->sendJsonResponse($data);
            }

            $transaction->commit();
            $data['status']            = self::API_OK;
            $data['temporary_user_id'] = $tempUser->id;
            $data['details']           = $tempUser;
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionVerifyOtpEmailMobile()
    {
        $transaction = null;

        try {
            // 1. Check for posted data
            $post = Yii::$app->request->post();
            if (empty($post)) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'No data posted.'));
            }

            // 2. Extract and validate required parameters
            $requiredFields = ['contact_no', 'session_code', 'otp_code', 'email', 'email_otp', 'temporary_user_id'];
            $params         = [];

            foreach ($requiredFields as $field) {
                $params[$field] = $post[$field] ?? null;
                if (empty($params[$field])) {
                    throw new \yii\web\BadRequestHttpException(
                        Yii::t('app', 'Missing required parameter: {field}', ['field' => $field])
                    );
                }
            }

            // 3. Mobile OTP verification
            $mobileOtpResult = $this->verifyMobileOtp($params['contact_no'], $params['session_code'], $params['otp_code']);
            if ($mobileOtpResult['Status'] !== 'Success') {
                throw new \yii\web\UnprocessableEntityHttpException(
                    $mobileOtpResult['Details'] ?? 'Mobile OTP verification failed'
                );
            }

            // 4. Email OTP verification
            if (! User::verifyOtpEmail($params['email'], $params['email_otp'])) {
                throw new \yii\web\UnprocessableEntityHttpException(
                    Yii::t('app', 'Email OTP invalid or expired.')
                );
            }

            // 5. Find temporary user
            $tempUser = TemporaryUsers::findOne($params['temporary_user_id']);
            if (! $tempUser) {
                throw new \yii\web\NotFoundHttpException(
                    Yii::t('app', 'Temporary user record not found.')
                );
            }

            // 6. Check for duplicate registration
            $existingUser = User::findOne([
                'contact_no' => $tempUser->contact_no,
                'user_role'  => User::ROLE_VENDOR,
            ]);
            if ($existingUser) {
                throw new \yii\web\ConflictHttpException(
                    Yii::t('app', 'This phone number is already registered.')
                );
            }

            // 7. Create user with transaction
            $transaction = Yii::$app->db->beginTransaction();

            $user = $this->createUserFromTemporary($tempUser, $post);
            $this->createAuthRecord($user, $params['contact_no']);

            // Clean up temporary user
            $tempUser->delete();

            $transaction->commit();

            // 8. Login and prepare response
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
                'error_code' => $e->statusCode ?? 400,
            ]);
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            Yii::error([
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);

            return $this->sendJsonResponse([
                'status'     => self::API_NOK,
                'error'      => Yii::t('app', 'An unexpected error occurred. Please try again.'),
                'error_code' => 500,
            ]);
        }
    }

    /**
     * Verify mobile OTP with bypass logic
     */
    private function verifyMobileOtp($contactNo, $sessionCode, $otpCode)
    {
        // Check for bypass numbers
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
     * Create user from temporary user data
     */
    private function createUserFromTemporary($tempUser, $postData)
    {
        $user                    = new User();
        $user->username          = $tempUser->username;
        $user->contact_no        = $tempUser->contact_no;
        $user->unique_user_id    = User::generateUniqueUserId('P');
        $user->first_name        = $tempUser->first_name;
        $user->email             = $tempUser->email;
        $user->date_of_birth =     $tempUser->date_of_birth;
        $user->gender            = $tempUser->gender;
        $user->user_role         = $tempUser->user_role;
        $user->device_token      = $postData['device_token'] ?? null;
        $user->device_type       = $postData['device_type'] ?? null;
        $user->status            = User::STATUS_ACTIVE;
        $user->referral_code     = User::generateUniqueReferralCode();
        $user->vendor_store_type = $tempUser->vendor_store_type;

        if (! $user->save(false)) {
            $errors = [];
            foreach ($user->getFirstErrors() as $field => $error) {
                $errors[] = "{$field}: {$error}";
            }

            throw new \yii\web\ServerErrorHttpException(
                Yii::t('app', 'Failed to create user account: {errors}', [
                    'errors' => implode(', ', $errors),
                ])
            );
        }

        $vendor_brands = VendorBrands::find()
            ->where(['user_id' => $user->id])
            ->one();
        if (empty($vendor_brands)) {
            $vendor_brands = new VendorBrands();
        }
        $vendor_brands->name       = $tempUser->brand_name;
        $vendor_brands->brand_logo = $tempUser->brand_logo;
        $vendor_brands->user_id    = $user->id;
        $vendor_brands->save(false);

        return $user;
    }

    /**
     * Create auth record for user
     */
    private function createAuthRecord($user, $contactNo)
    {
        $auth            = new Auth();
        $auth->user_id   = $user->id;
        $auth->source    = User::ROLE_STAFF;
        $auth->source_id = $contactNo;

        if (! $auth->save()) {
            throw new \yii\web\ServerErrorHttpException(
                Yii::t('app', 'Failed to create authentication record.')
            );
        }

        return $auth;
    }

    public function actionUploadFiles()
    {
        $data = [];

        try {

            $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');

            if (! $uploadedFile) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'No file uploaded.'));
            }

            $uploadResult = Yii::$app->notification->imageKitUpload($uploadedFile);

            if (empty($uploadResult['url'])) {
                throw new \yii\web\ServerErrorHttpException('ImageKit upload failed.');
            }

            $data['status']   = self::API_OK;
            $data['file_url'] = $uploadResult['url'];
        } catch (\yii\web\HttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionSendEmailOtp()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data                       = [];

        try {
            $post  = Yii::$app->request->post();
            $email = filter_var($post['email'] ?? null, FILTER_VALIDATE_EMAIL);

            if (! $email) {
                throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Invalid email address.'));
            }

            // Check rate limit
            if (User::isRateLimited($email)) {
                throw new \yii\web\TooManyRequestsHttpException(Yii::t('app', 'Too many OTP requests. Please try again later.'));
            }

            // Generate and hash OTP
            $otp                   = User::generateOtp();
            $model                 = new EmailOtpVerifications();
            $model->email          = $email;
            $model->otp            = password_hash($otp, PASSWORD_BCRYPT);
            $model->is_verified    = 0;
            $model->status         = EmailOtpVerifications::STATUS_ACTIVE;
    

            // Clean up expired OTPs
            User::cleanupExpiredOtps($email);

            if (! $model->save(false)) {
                throw new \yii\web\ServerErrorHttpException('Failed to save OTP.');
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

            // Add List-Unsubscribe header for SwiftMailer
            if ($mailer instanceof \yii\swiftmailer\Message) {
                $mailer->getSwiftMessage()->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsubscribe@esteticanow.com>');
            }

            // Send email
            $sent = $mailer->send();

            if (! $sent) {
                throw new \yii\web\ServerErrorHttpException('Failed to send OTP email.');
            }

            $data['status']  = self::API_OK;
            $data['message'] = 'OTP sent successfully.';
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred.');
        }

        return $this->sendJsonResponse($data);
    }

    public function actionRegisterManagers()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->getRawBody();

        try {
            // Check user authentication
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $userVendor = User::findOne(['id' => $user_id, 'user_role' => User::ROLE_VENDOR]);
            if (empty($userVendor)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }

            $post = json_decode($post, true);

            if (empty($post)) {
                throw new BadRequestHttpException(Yii::t("app", "No data posted."));
            }

            $success = [];
            $errors  = [];

            foreach ($post as $index => $manager) {
                $rowInfo = [
                    'row'           => $index + 1,
                    'mobile_number' => $manager['mobile_number'] ?? null,
                    'manager_name'  => $manager['manager_name'] ?? null,
                ];

                if (empty($manager['outlet_location']) || empty($manager['manager_name']) || empty($manager['mobile_number'])) {
                    $errors[] = array_merge($rowInfo, [
                        'error' => "Incomplete manager data.",
                    ]);
                    continue;
                }

                // Check if user already exists by mobile number
                $existingUser = User::find()
                    ->where(['contact_no' => $manager['mobile_number'], 'user_role' => User::ROLE_VENDOR])
                    ->one();
                if ($existingUser) {
                    $errors[] = array_merge($rowInfo, [
                        'error' => "User already registered with this mobile number: " . $manager['mobile_number'],
                    ]);
                    continue;
                }

                $user                   = new User();
                $user->username         = $manager['mobile_number'] . '@' . User::ROLE_VENDOR . '.com';
                $user->contact_no       = $manager['mobile_number'];
                $user->unique_user_id   = User::generateUniqueUserId('P');
                $user->first_name       = $manager['manager_name'];
                $user->email            = $manager['email'] ?? null;
                $user->device_token     = $manager['device_token'] ?? null;
                $user->location         = $manager['outlet_location'] ?? null;
                $user->create_user_id   = $user_id;
                $user->allow_onboarding = $userVendor->allow_onboarding;
                $user->user_role        = User::ROLE_VENDOR;

                if (! $user->save(false)) {
                    $errors[] = array_merge($rowInfo, [
                        'error' => "Failed to register manager: " . json_encode($user->getErrors()),
                    ]);
                    continue;
                }

                $success[] = array_merge($rowInfo, [
                    'user_id' => $user->id,
                    'message' => 'Manager registered successfully',
                ]);
            }

            $data['status']  = ! empty($errors) ? self::API_NOK : self::API_OK;
            $data['message'] = Yii::t("app", "Managers registration completed.");
            $data['result']  = [
                'success' => $success,
                'errors'  => $errors,
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            Yii::error("Error registering managers: " . $e->getMessage(), __METHOD__);
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddOrUpdateStaffBatch()
    {
        $data      = [];
        $results   = [];
        $headers   = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth      = new AuthSettings();
        $user_id   = $auth->getAuthSession($headers);
        $raw       = Yii::$app->request->getRawBody();
        $staffList = json_decode($raw, true);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $VendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($VendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No Vendor details found for this user."));
            }
            $vendor_details_id = $VendorDetails->id;

            if (empty($staffList) || ! is_array($staffList)) {
                throw new BadRequestHttpException(Yii::t("app", "No staff data posted or invalid format."));
            }

            foreach ($staffList as $index => $post) {
                $result = [
                    'input_index' => $index,
                    'mobile_no'   => $post['mobile_no'] ?? '',
                    'status'      => null,
                    'message'     => '',
                    'details'     => null,
                ];
                try {
                    $staff_id       = $post['staff_id'] ?? '';
                    $mobile_no      = $post['mobile_no'] ?? '';
                    $email          = $post['email'] ?? '';
                    $full_name      = $post['full_name'] ?? '';
                    $gender         = $post['gender'] ?? '';
                    $dob            = $post['dob'] ?? '';
                    $role           = User::ROLE_STAFF;
                    $profile_image  = $post['profile_image'] ?? '';
                    $experience     = $post['experience'] ?? '';
                    $specialization = $post['specialization'] ?? '';
                    $aadhaar_number = $post['aadhaar_number'] ?? '';

                    if (empty($mobile_no) || empty($full_name) || empty($role)) {
                        throw new \Exception(Yii::t("app", "Full name, mobile number, and role are required."));
                    }

                    $dobFormatted = ! empty($dob) ? date('Y-m-d', strtotime($dob)) : null;
                    $user         = null;

                    if (! empty($staff_id)) {
                        $user = User::findOne(['id' => $staff_id]);
                        if (! $user) {
                            throw new NotFoundHttpException(Yii::t("app", "No staff found with the given ID."));
                        }
                    } else {
                        // Check duplicate
                        $existingUser = User::find()
                            ->where(['contact_no' => $mobile_no])
                            ->andWhere(['IN', 'user_role', [User::ROLE_STAFF, User::ROLE_HOME_VISITOR]])
                            ->one();

                        if ($existingUser) {
                            throw new \Exception(Yii::t("app", "Staff already exists with this mobile number."));
                        }

                        $user = new User();
                    }

                    // User setup
                    $username            = $mobile_no . '@' . $role . '.com';
                    $user->username      = $username;
                    $user->email         = $email;
                    $user->first_name    = $full_name;
                    $user->contact_no    = $mobile_no;
                    $user->date_of_birth = $dobFormatted;
                    $user->gender        = $gender;
                    $user->user_role     = $role;

                    if ($user->isNewRecord) {
                        $user->unique_user_id = User::generateUniqueUserId('H');
                    }

                    $user->save(false);

                    // Staff
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

                    $result['status']  = 'success';
                    $result['message'] = ! empty($staff_id)
                    ? Yii::t("app", "Staff updated successfully.")
                    : Yii::t("app", "Staff added successfully.");
                    $result['details'] = $staff->asJson();
                } catch (\Exception $e) {
                    $result['status']  = 'error';
                    $result['message'] = $e->getMessage();
                }
                $results[] = $result;
            }

            $data['status']  = self::API_OK;
            $data['results'] = $results;
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionManagerStoreList()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $post   = Yii::$app->request->post();
            $page   = isset($post['page']) && is_numeric($post['page']) && $post['page'] > 0 ? (int) $post['page'] : 1;
            $limit  = isset($post['limit']) && is_numeric($post['limit']) && $post['limit'] > 0 ? (int) $post['limit'] : 10;
            $offset = ($page - 1) * $limit;

            // Get total count for pagination
            $totalCount = User::find()
                ->where(['create_user_id' => $user_id, 'user_role' => User::ROLE_VENDOR])
                ->count();

            // Fetch paginated vendors
            $vendors = User::find()
                ->where(['create_user_id' => $user_id, 'user_role' => User::ROLE_VENDOR])
                ->offset($offset)
                ->limit($limit)
                ->all();

            $vendorDetails = [];
            foreach ($vendors as $vendor) {
                $vendorDetails[] = $vendor->asJsonVendor();
            }

            if (empty($vendorDetails)) {
                throw new NotFoundHttpException(Yii::t("app", "No vendor details found for this user."));
            }

            $data['status']     = self::API_OK;
            $data['message']    = Yii::t("app", "Vendor details retrieved successfully.");
            $data['details']    = $vendorDetails;
            $data['pagination'] = [
                'page'        => $page,
                'per_page'    => $limit,
                'total_count' => (int) $totalCount,
                'page_count'  => (int) ceil($totalCount / $limit),
            ];
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }

    public function actionDeleteManager()
    {
        $data        = [];
        $transaction = null;

        // 1. Authentication
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
        }

        // 2. Validate request method
        if (! Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
        }

        // 3. Get and validate input parameters
        $post            = Yii::$app->request->post();
        $user_id_manager = $post['user_id'] ?? null;

        if (empty($user_id_manager)) {
            throw new BadRequestHttpException(Yii::t("app", "Valid manager user ID is required."));
        }

        // 4. Find the manager user
        $user = User::findOne([
            'id'        => $user_id_manager,
            'user_role' => User::ROLE_VENDOR,
        ]);

        if (! $user) {
            throw new NotFoundHttpException(Yii::t("app", "Manager not found or you don't have permission to delete this manager."));
        }

        // Store user data for response before deletion
        $deletedUserData = [
            'id'       => $user->id,
            'username' => $user->username,
        ];

        // 8. Delete the user
        if (! $user->delete()) {

            throw new ServerErrorHttpException(
                Yii::t("app", "Failed to delete manager: {errors}", [
                    'errors' => implode(', ', $user->getFirstErrors()),
                ])
            );
        }

        // 9. Log the deletion
        Yii::info("Manager deleted successfully: user_id={$user_id_manager} by user_id={$user_id}", __METHOD__);

        // 10. Prepare success response
        $data['status']  = self::API_OK;
        $data['message'] = Yii::t("app", "Manager deleted successfully.");
        $data['details'] = $deletedUserData;

        return $this->sendJsonResponse($data);
    }

    public function actionAddNewServiceForExistingOrder()
    {

        $data        = [];
        $transaction = null;

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $getVendorIdByUser = User::getVendorIdByUserId($user_id);

            // 2. Validate request method
            if (! Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get and validate input parameters
            $post       = Yii::$app->request->post();
            $service_id = $post['service_id'] ?? null;
            $order_id   = $post['order_id'] ?? null;

            if (empty($service_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid service ID is required."));
            }

            if (empty($order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid order ID is required."));
            }

            $orders = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $getVendorIdByUser]);
            if (! $orders) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or you don't have permission to add services to this order."));
            }

            $services = Services::findOne(['id' => $service_id, 'vendor_details_id' => $getVendorIdByUser]);
            if (! $services) {
                throw new NotFoundHttpException(Yii::t("app", "Service not found or you don't have permission to add this service."));
            }
            $price       = $services->price ?? $orders->from_price;
            $total_price = $price;
            $service_id  = $services->id;
            $qty         = 1;

            $order_details              = new OrderDetails();
            $order_details->order_id    = $orders->id;
            $order_details->service_id  = $service_id;
            $order_details->qty         = $qty;
            $order_details->price       = $price;
            $order_details->total_price = $total_price;
            $order_details->save(false);
            $orders->refresh();
            Orders::recalculateOrderPrice($order_id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $orders->refresh();
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Service added successfully.");
            $data['details'] = $order_details->asJson();
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status']     = self::API_NOK;
            $data['error']      = Yii::t("app", "An unexpected error occurred while deleting the manager.");
            $data['error_code'] = 500;

            Yii::error([
                'message' => $e->getMessage(),

            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

public function actionWebVendorDashboard()
{
    $data      = [];
    $dashboard = [];
    $post      = Yii::$app->request->post();
    $headers   = Yii::$app->request->headers['auth_code'] ?? Yii::$app->request->getQueryParam('auth_code');
    $auth      = new AuthSettings();
    $user_id   = $auth->getAuthSession($headers);

    $start_date       = !empty($post['start_date']) ? $this->convertToDate($post['start_date']) : null;
    $end_date         = !empty($post['end_date']) ? $this->convertToDate($post['end_date']) : null;
    $today            = date('Y-m-d');

    if (!$user_id) {
        return $this->sendJsonResponse([
            'status' => self::API_NOK,
            'error'  => Yii::t("app", "User authentication failed. Please log in."),
        ]);
    }

    // try {
        $vendorDetails = VendorDetails::find()
            ->where(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE])
            ->one();

        if (!$vendorDetails) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error'  => Yii::t("app", "Vendor details not found."),
            ]);
        }

        $vendor_details_id = $vendorDetails->id;

        // Base conditions
        $orderConditions = [
            'o.vendor_details_id' => $vendor_details_id,
            'o.status'            => Orders::STATUS_SERVICE_COMPLETED
        ];

        // Date filtering for Orders
        if ($start_date && $end_date) {
            $orderConditions = ['and', $orderConditions, ['between', 'o.completed', "$start_date 00:00:00", "$end_date 23:59:59"]];
        } else {
            $orderConditions = ['and', $orderConditions, ['between', 'DATE(o.completed)', $today, $today]];
        }

        // Total earnings
        $total_earnings = Orders::find()
            ->alias('o')
            ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
            ->where($orderConditions)
            ->sum('ve.vendor_received_amount') ?? 0;

        // Vendor payout pending query
        $vendorPayoutQuery = VendorPayout::find()
            ->where(['vendor_details_id' => $vendor_details_id])
            ->andWhere(['status' => VendorPayout::STATUS_PROCESSING]);

        // Date filtering for Vendor Payouts
        if ($start_date && $end_date) {
            $vendorPayoutQuery->andWhere(['between', 'created_on', "$start_date 00:00:00", "$end_date 23:59:59"]);
        } else {
            $vendorPayoutQuery->andWhere(['between', 'DATE(created_on)', $today, $today]);
        }
        $vendor_payout_pending = $vendorPayoutQuery->sum('amount') ?? 0;

        // Orders count by platform source
        $baseOrderQuery = Orders::find()
            ->alias('o')
            ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id')
            ->where(['o.vendor_details_id' => $vendor_details_id])
            ->andWhere($orderConditions);

        $total_orders = (clone $baseOrderQuery)->count();
        
        $total_orders_app = (clone $baseOrderQuery)
            ->andWhere(['o.platform_source' => Orders::PLATFORM_SOURCE_APP])
            ->count();
            
        $total_orders_vendor_web = (clone $baseOrderQuery)
            ->andWhere(['o.platform_source' => Orders::PLATFORM_SOURCE_WEB_VENDOR])
            ->count();
            
        $total_orders_web = (clone $baseOrderQuery)
            ->andWhere(['o.platform_source' => Orders::PLATFORM_SOURCE_WEB])
            ->count();

        // Earnings comparison
        $baseEarningsQuery = Orders::find()
            ->alias('o')
            ->innerJoin('vendor_earnings ve', 'o.id = ve.order_id AND ve.status = ' . VendorEarnings::STATUS_APPROVED)
            ->where(['o.vendor_details_id' => $vendor_details_id, 'o.status' => Orders::STATUS_SERVICE_COMPLETED]);

        $today_earnings = (clone $baseEarningsQuery)
            ->andWhere(['between', 'DATE(o.completed)', $today, $today])
            ->sum('ve.vendor_received_amount') ?? 0;
            
        $week_earnings = (clone $baseEarningsQuery)
            ->andWhere(['>=', 'DATE(o.completed)', date('Y-m-d', strtotime('-1 week'))])
            ->sum('ve.vendor_received_amount') ?? 0;
            
        $month_earnings = (clone $baseEarningsQuery)
            ->andWhere(['>=', 'DATE(o.completed)', date('Y-m-d', strtotime('-1 month'))])
            ->sum('ve.vendor_received_amount') ?? 0;
            
        $custom_range_earnings = 0;
        if ($start_date && $end_date) {
            $custom_range_earnings = (clone $baseEarningsQuery)
                ->andWhere(['between', 'o.completed', "$start_date 00:00:00", "$end_date 23:59:59"])
                ->sum('ve.vendor_received_amount') ?? 0;
        }

        $earnings_comparison = [
            'today' => (float)$today_earnings,
            'week'  => (float)$week_earnings,
            'month' => (float)$month_earnings,
            'custom_range' => (float)$custom_range_earnings,
        ];

        // Orders comparison
        $baseOrderCountQuery = Orders::find()
            ->where(['vendor_details_id' => $vendor_details_id, 'status' => Orders::STATUS_SERVICE_COMPLETED]);

        $orders_today = (clone $baseOrderCountQuery)
            ->andWhere(['between', 'DATE(completed)', $today, $today])
            ->count();
            
        $orders_last_week = (clone $baseOrderCountQuery)
            ->andWhere(['>=', 'DATE(completed)', date('Y-m-d', strtotime('-1 week'))])
            ->count();
            
        $orders_month = (clone $baseOrderCountQuery)
            ->andWhere(['>=', 'DATE(completed)', date('Y-m-d', strtotime('-1 month'))])
            ->count();
            
        $orders_custom = 0;
        if ($start_date && $end_date) {
            $orders_custom = (clone $baseOrderCountQuery)
                ->andWhere(['between', 'completed', "$start_date 00:00:00", "$end_date 23:59:59"])
                ->count();
        }

        $orders_comparison = [
            'today' => $orders_today,
            'week' => $orders_last_week,
            'month' => $orders_month,
            'custom' => $orders_custom,
        ];

        // Active bookings
        $active_bookings_count = Orders::find()
            ->where(['vendor_details_id' => $vendor_details_id])
            ->andWhere(['!=', 'status', Orders::STATUS_SERVICE_COMPLETED])
            ->count();
            
        $active_bookings_progress = ($total_orders > 0) ? 
            ($active_bookings_count / $total_orders * 100) : 0;

        $active_bookings = [
            'count' => $active_bookings_count,
            'progress' => round($active_bookings_progress, 2)
        ];

        // Staff information
        $staff_total = Staff::find()->where(['vendor_details_id' => $vendor_details_id])->andWhere(['status' => Staff::STATUS_ACTIVE])->count();
        $on_duty = 0; // TODO: Implement actual on-duty logic
        $attendance = 0; // TODO: Implement actual attendance logic
        $staff = [
            'staff_total' => $staff_total,
            'on_duty' => $on_duty,
            'attendance' => $attendance
        ];

        // Top booked services for pie chart
        $top_services_raw = Orders::find()
            ->alias('o')
            ->innerJoin('order_details od', 'o.id = od.order_id')
            ->innerJoin('services s', 'od.service_id = s.id')
            ->select([
                's.id as service_id',
                's.service_name as service_name',
                'COUNT(od.id) as booking_count',
                'SUM(od.total_price) as total_revenue'
            ])
            ->where(['o.vendor_details_id' => $vendor_details_id])
            ->groupBy(['s.id', 's.service_name'])
            ->orderBy(['booking_count' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();

        // Format data for pie chart
        $top_service_booked = [
            'labels' => [],
            'data' => [],
            'colors' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
            'revenue' => []
        ];

        foreach ($top_services_raw as $index => $service) {
            $top_service_booked['labels'][] = $service['service_name'] ?: "Service #{$service['service_id']}";
            $top_service_booked['data'][] = (int)$service['booking_count'];
            $top_service_booked['revenue'][] = (float)$service['total_revenue'];
        }

        // Earnings trend graphs data
        $charts = [
            'daily' => $this->getEarningsChartData($vendor_details_id, 'daily', $today, $today),
            'weekly' => $this->getEarningsChartData($vendor_details_id, 'weekly', date('Y-m-d', strtotime('-7 days')), $today),
            'monthly' => $this->getEarningsChartData($vendor_details_id, 'monthly', date('Y-m-d', strtotime('-30 days')), $today),
            'custom' => $start_date && $end_date ? $this->getEarningsChartData($vendor_details_id, 'custom', $start_date, $end_date) : null
        ];

        // Prepare dashboard data
        $dashboard = [
            'earnings' => [
                'total_earnings' => (float)$total_earnings,
                'earnings_comparison' => $earnings_comparison
            ],
            'pending_payouts' => (float)$vendor_payout_pending,
            'total_orders' => [
                'total_orders' => $total_orders,
                'orders_comparison' => $orders_comparison
            ],
            'platform_orders' => [
                'app' => $total_orders_app,
                'vendor_web' => $total_orders_vendor_web,
                'web' => $total_orders_web
            ],
            'active_bookings' => $active_bookings,
            'staff' => $staff,
            'top_service_booked' => $top_service_booked,
            'charts' => $charts,
            'date_range' => [
                'start_date' => $start_date ?? $today,
                'end_date' => $end_date ?? $today,
            ]
        ];

        return $this->sendJsonResponse([
            'status'  => self::API_OK,
            'details' => $dashboard,
        ]);
    // } catch (\Exception $e) {
    //     Yii::error("Error processing dashboard data: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
    //     return $this->sendJsonResponse([
    //         'status' => self::API_NOK,
    //         'error'  => Yii::t("app", "An unexpected error occurred while processing the request."),
    //     ]);
    // }
    }

    /**
     * Get earnings chart data for different time periods
     */



private function convertToDate($dateString)
{
    if (empty($dateString)) {
        return null;
    }

    try {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    } catch (\Exception $e) {
        // Log the invalid date for debugging if needed
        Yii::error("Invalid date format provided: {$dateString}", __METHOD__);
        return null;
    }
}


    private function getEarningsChartData($vendor_details_id, $period, $start_date, $end_date)
    {
        $labels = [];
        $data = [];
        
        switch ($period) {
            case 'daily':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $day = date('Y-m-d', strtotime("-$i days"));
                    $day_label = date('D', strtotime("-$i days")); // Mon, Tue, etc.
                    
                    $day_earnings = VendorEarnings::find()
                        ->where(['vendor_details_id' => $vendor_details_id])
                        ->andWhere(['DATE(created_on)' => $day])
                        ->sum('vendor_received_amount') ?: 0;
                        
                    $labels[] = $day_label;
                    $data[] = (float)$day_earnings;
                }
                break;
                
            case 'weekly':
                // Last 4 weeks
                for ($i = 3; $i >= 0; $i--) {
                    $week_start = date('Y-m-d', strtotime("-" . ($i * 7) . " days", strtotime('monday this week')));
                    $week_end = date('Y-m-d', strtotime("+6 days", strtotime($week_start)));
                    $week_label = "Week " . date('W', strtotime($week_start));
                    
                    $week_earnings = VendorEarnings::find()
                        ->where(['vendor_details_id' => $vendor_details_id])
                        ->andWhere(['>=', 'DATE(created_on)', $week_start])
                        ->andWhere(['<=', 'DATE(created_on)', $week_end])
                        ->sum('vendor_received_amount') ?: 0;
                        
                    $labels[] = $week_label;
                    $data[] = (float)$week_earnings;
                }
                break;
                
            case 'monthly':
                // Last 6 months
                for ($i = 5; $i >= 0; $i--) {
                    $month_start = date('Y-m-01', strtotime("-$i months"));
                    $month_end = date('Y-m-t', strtotime("-$i months"));
                    $month_label = date('M Y', strtotime("-$i months"));
                    
                    $month_earnings = VendorEarnings::find()
                        ->where(['vendor_details_id' => $vendor_details_id])
                        ->andWhere(['>=', 'DATE(created_on)', $month_start])
                        ->andWhere(['<=', 'DATE(created_on)', $month_end])
                        ->sum('vendor_received_amount') ?: 0;
                        
                    $labels[] = $month_label;
                    $data[] = (float)$month_earnings;
                }
                break;
                
            case 'custom':
                // Custom date range - daily breakdown
                $current_date = $start_date;
                while ($current_date <= $end_date) {
                    $day_label = date('M d', strtotime($current_date));
                    
                    $day_earnings = VendorEarnings::find()
                        ->where(['vendor_details_id' => $vendor_details_id])
                        ->andWhere(['DATE(created_on)' => $current_date])
                        ->sum('vendor_received_amount') ?: 0;
                        
                    $labels[] = $day_label;
                    $data[] = (float)$day_earnings;
                    
                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                }
                break;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'period' => $period
        ];
    }









public function actionSendOtpCheck(){

       $data = [];
        try {
            $post = Yii::$app->request->post();
            if (! empty($post)) {
                $contact_no = $post['contact_no']?? null;
                if(empty($contact_no)){
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "Missing required parameter: contact_no");
                    return $this->sendJsonResponse($data);
                }

                $get_vendor_user = User::find()->where(['contact_no'=>$contact_no])->andWhere(['user_role'=>User::ROLE_VENDOR])->one();

                if (!empty($get_vendor_user)) {
                    $data['status'] = self::API_OK;
                    $data['registration_required'] = false;

                } else {
                    $data['status'] = self::API_NOK;
                    $data['error']  = Yii::t("app", "Vendor user not found");
                    $data['registration_required'] = true;
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



}