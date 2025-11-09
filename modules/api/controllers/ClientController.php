<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\admin\models\base\Wallet;
use app\modules\admin\models\GuestUserDeposits;
use app\modules\admin\models\MemberShips;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoresUsersMemberships;
use app\modules\admin\models\User;
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

class ClientController extends BKController
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
                            'create-client',
                            'update-client',
                            'change-client-status',
                            'get-clients',
                            'view-client',
                            'create-membership',
                            'update-membership',
                            'get-memberships',
                            'change-status-membership',
                            'add-vip-client',
                            'client-wallet',
                            'add-deposit'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'create-client',
                            'update-client',
                            'change-client-status',
                            'get-clients',
                            'view-client',
                            'create-membership',
                            'update-membership',
                            'get-memberships',
                            'change-status-membership',
                            'add-vip-client',
                            'client-wallet',
                            'add-deposit'
                        ],
                        'allow'   => true,
                        'roles'   => ['?', '*'],
                    ],
                ],
            ],
        ]);
    }


    public function actionCreateClient()
    {
        $data        = [];
        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
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

            /** -------------------------
             * 2. Validate request method
             * ------------------------ */
            if (! Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            /** -------------------------
             * 3. Get and validate POST data
             * ------------------------ */
            $post = Yii::$app->request->post();

            $firstname        = trim($post['first_name'] ?? '');
            $lastname         = trim($post['last_name'] ?? '');
            $email            = trim($post['email'] ?? '');
            $contact_no       = trim($post['contact_no'] ?? '');
            $address          = trim($post['address'] ?? '');
            $member_ships_id  = trim($post['member_ships_id'] ?? '');

            $role = User::ROLE_GUEST;

            // Required fields check
            if (empty($firstname)) {
                throw new BadRequestHttpException(Yii::t("app", "First name is required."));
            }
            if (empty($email)) {
                throw new BadRequestHttpException(Yii::t("app", "Email is required."));
            }
            if (empty($contact_no)) {
                throw new BadRequestHttpException(Yii::t("app", "Contact number is required."));
            }
            if (empty($address)) {
                throw new BadRequestHttpException(Yii::t("app", "Address is required."));
            }

            // Membership validation if provided
            if (!empty($member_ships_id)) {
                $membership = MemberShips::find()
                    ->where(['id' => $member_ships_id, 'vendor_details_id' => $vendorId])
                    ->one();

                if (empty($membership)) {
                    throw new BadRequestHttpException(Yii::t("app", "Invalid membership ID."));
                }
            }

            /** -------------------------
             * 4. Check if user already exists
             * ------------------------ */
            $existingUser = User::find()
                ->orWhere(['contact_no' => $contact_no])
                ->andWhere(['user_role' => $role])
                ->one();

            if ($existingUser) {
                // Check if already linked with this vendor
                $alreadyLinked = StoresHasUsers::find()
                    ->where([
                        'vendor_details_id' => $vendorId,
                        'guest_user_id'     => $existingUser->id
                    ])->exists();

                if ($alreadyLinked) {
                    throw new BadRequestHttpException(Yii::t("app", "Client already exists and linked with this vendor."));
                }

                // ✅ Not linked yet: create vendor link
                $storeUser = new StoresHasUsers();
                $storeUser->vendor_details_id = $vendorId;
                $storeUser->vendor_user_id    = $user_id;
                $storeUser->guest_user_id     = $existingUser->id;
                $storeUser->status            = StoresHasUsers::STATUS_ACTIVE;

                if (!$storeUser->save(false)) {
                    throw new Exception(Yii::t("app", "Failed to link existing client to store: ") . json_encode($storeUser->getErrors()));
                }

                // Add membership if provided
                if (!empty($member_ships_id)) {
                    $stores_users_memberships = StoresUsersMemberships::find()
                        ->where([
                            'stores_has_users_id' => $storeUser->id,
                            'membership_id'       => $member_ships_id
                        ])
                        ->one();

                    if (!$stores_users_memberships) {
                        $stores_users_memberships = new StoresUsersMemberships();
                        $stores_users_memberships->stores_has_users_id = $storeUser->id;
                        $stores_users_memberships->membership_id       = $member_ships_id;
                        $stores_users_memberships->status              = StoresUsersMemberships::STATUS_ACTIVE;

                        if (!$stores_users_memberships->save(false)) {
                            throw new Exception(Yii::t("app", "Failed to assign membership: ") . json_encode($stores_users_memberships->getErrors()));
                        }
                    }
                }

                $transaction->commit();

                $data['status']    = self::API_OK;
                $data['message']   = Yii::t("app", "Existing client linked successfully.");
                $data['client_id'] = $existingUser->id;

                return $this->sendJsonResponse($data);
            }

            /** -------------------------
             * 5. Create new user
             * ------------------------ */
            $user = new User();
            $user->first_name = $firstname;
            $user->last_name  = $lastname;
            $user->unique_user_id = User::generateUniqueUserId('GUEST');
            $user->username = $contact_no . '@' . $role . '.com';
            $user->email      = $email;
            $user->contact_no = $contact_no;
            $user->address    = $address;
            $user->user_role  = $role;
            $user->referral_code = User::generateUniqueReferralCode();
            $user->status     = User::STATUS_ACTIVE;

            if (!$user->save(false)) {
                throw new Exception(Yii::t("app", "Failed to create user: ") . json_encode($user->getErrors()));
            }

            // Link client to store
            $storeUser = new StoresHasUsers();
            $storeUser->vendor_details_id = $vendorId;
            $storeUser->vendor_user_id    = $user_id;
            $storeUser->guest_user_id     = $user->id;
            $storeUser->status            = StoresHasUsers::STATUS_ACTIVE;

            if (!$storeUser->save(false)) {
                throw new Exception(Yii::t("app", "Failed to link client to store: ") . json_encode($storeUser->getErrors()));
            }

            // Add membership if provided
            if (!empty($member_ships_id)) {
                $stores_users_memberships = new StoresUsersMemberships();
                $stores_users_memberships->stores_has_users_id = $storeUser->id;
                $stores_users_memberships->membership_id       = $member_ships_id;
                $stores_users_memberships->status              = StoresUsersMemberships::STATUS_ACTIVE;

                if (!$stores_users_memberships->save(false)) {
                    throw new Exception(Yii::t("app", "Failed to assign membership: ") . json_encode($stores_users_memberships->getErrors()));
                }
            }

            /** -------------------------
             * 6. Commit transaction
             * ------------------------ */
            $transaction->commit();

            /** -------------------------
             * 7. Response
             * ------------------------ */
            $data['status']    = self::API_OK;
            $data['message']   = Yii::t("app", "Client created successfully.");
            $data['client'] = $user->asJsonUserClient($vendorId);
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->getCode() ?: 500;

            Yii::error([
                'message' => $e->getMessage(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionUpdateClient()
    {
        $data = [];
        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
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


            $post = Yii::$app->request->post();

            $client_id       = isset($post['client_id']) ? (int)$post['client_id'] : null;
            $firstname       = isset($post['first_name']) ? trim($post['first_name']) : null;
            $lastname        = isset($post['last_name']) ? trim($post['last_name']) : null;
            $email           = isset($post['email']) ? strtolower(trim($post['email'])) : null;
            $contact_no      = isset($post['contact_no']) ? trim($post['contact_no']) : null;
            $address         = isset($post['address']) ? trim($post['address']) : null;
            $member_ships_id = isset($post['member_ships_id']) ? trim($post['member_ships_id']) : null;

            // client_id is mandatory for update-only action
            if (empty($client_id)) {
                throw new BadRequestHttpException(Yii::t("app", "client_id is required for update."));
            }


            $user = User::findOne(['id' => $client_id]);
            if (!$user || $user->user_role !== User::ROLE_GUEST) {
                throw new BadRequestHttpException(Yii::t("app", "Client not found."));
            }

            $storeLink = StoresHasUsers::find()
                ->where([
                    'vendor_details_id' => $vendorId,
                    'guest_user_id'     => $user->id
                ])
                ->one();

            if (!$storeLink) {
                throw new BadRequestHttpException(Yii::t("app", "This client is not linked with your store."));
            }


            if (!empty($member_ships_id)) {
                $membership = MemberShips::find()
                    ->where(['id' => $member_ships_id, 'vendor_details_id' => $vendorId])
                    ->one();

                if (empty($membership)) {
                    throw new BadRequestHttpException(Yii::t("app", "Invalid membership ID."));
                }
            }



            if (!empty($contact_no)) {
                $conflict = User::find()
                    ->where(['contact_no' => $contact_no])
                    ->andWhere(['<>', 'id', $user->id])
                    ->one();
                if ($conflict) {
                    throw new BadRequestHttpException(Yii::t("app", "Contact number already used by another user."));
                }
            }


            $changed = false;
            if ($firstname !== null && $firstname !== $user->first_name) {
                $user->first_name = $firstname;
                $changed = true;
            }
            if ($lastname !== null && $lastname !== $user->last_name) {
                $user->last_name = $lastname;
                $changed = true;
            }
            if ($email !== null && $email !== $user->email) {
                $user->email = $email;
                $changed = true;
            }
            if ($contact_no !== null && $contact_no !== $user->contact_no) {
                $user->contact_no = $contact_no;
                $changed = true;
            }
            if ($address !== null && $address !== $user->address) {
                $user->address = $address;
                $changed = true;
            }

            if ($changed) {
                if (!$user->save(false)) {
                    throw new Exception(Yii::t("app", "Failed to update client: ") . json_encode($user->getErrors()));
                }
            }


            if (!empty($member_ships_id)) {
                $stores_users_memberships = StoresUsersMemberships::find()
                    ->where([
                        'stores_has_users_id' => $storeLink->id,
                        'membership_id'       => $member_ships_id
                    ])
                    ->one();

                if (empty($stores_users_memberships)) {
                    $stores_users_memberships = new StoresUsersMemberships();
                    $stores_users_memberships->stores_has_users_id = $storeLink->id;
                }
                $stores_users_memberships->membership_id       = $member_ships_id;
                $stores_users_memberships->status              = StoresUsersMemberships::STATUS_ACTIVE;

                if (!$stores_users_memberships->save(false)) {
                    throw new Exception(Yii::t("app", "Failed to assign membership: ") . json_encode($stores_users_memberships->getErrors()));
                }
            }

            $transaction->commit();

            $data['status']    = self::API_OK;
            $data['message']   = Yii::t("app", "Client updated successfully.");
            $data['client'] = $user->asJsonUserClient($vendorId);
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->getCode() ?: 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }







    public function actionChangeClientStatus()
    {
        $data = [];

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

            $post = Yii::$app->request->post();

            $client_id = !empty($post['client_id']) ? (int)$post['client_id'] : null;
            $status    = isset($post['status']) ? $post['status'] : null;

            if (empty($client_id)) {
                throw new BadRequestHttpException(Yii::t("app", "client_id is required for update."));
            }

            if ($status === null || $status === '') {
                throw new BadRequestHttpException(Yii::t("app", "status is required."));
            }

            // Normalize status to integer when possible
            if (is_numeric($status)) {
                $status = (int)$status;
            } else {
                // if you expect string statuses like 'active', you can keep as-is or normalize here
                $status = trim($status);
            }

            // load client and ensure role = guest
            $client = User::find()->where(['id' => $client_id, 'user_role' => User::ROLE_GUEST])->one();
            if (empty($client)) {
                throw new BadRequestHttpException(Yii::t("app", "Client not found."));
            }

            // Ensure client is associated with this vendor
            $storesHasUsers = StoresHasUsers::find()
                ->where(['guest_user_id' => $client->id, 'vendor_details_id' => $vendorId])
                ->one();

            if (empty($storesHasUsers)) {
                throw new BadRequestHttpException(Yii::t("app", "User is not associated with your store."));
            }




            // update and save using validation
            $storesHasUsers->status = $status;

            if (!$storesHasUsers->save(false)) {
                // return model validation errors
                throw new Exception(Yii::t("app", "Failed to update client status: ") . json_encode($storesHasUsers->getErrors()));
            }

            $data['status']    = self::API_OK;
            $data['message']   = Yii::t("app", "Client status updated successfully.");
            $data['client'] = $client->asJsonUserClient($vendorId);
        } catch (\Throwable $e) {


            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);

            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->getCode() ?: 500;
        }

        return $this->sendJsonResponse($data);
    }





    public function actionCreateMembership()
    {
        $data        = [];

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
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




            $post = Yii::$app->request->post();

            $membershipName = trim($post['membership_name'] ?? '');
            $color          = trim($post['color'] ?? '');
            $discount       = trim($post['discount'] ?? '');
            $isVipPlan      = trim($post['is_vip_plan'] ?? '');
            $membership_validity       = trim($post['membership_validity'] ?? '');
            $actual_price       = trim($post['actual_price'] ?? '');
            $discount_price       = trim($post['discount_price'] ?? '');


            // Required fields check
            if (empty($membershipName) || empty($color) || $discount === '') {
                throw new BadRequestHttpException(Yii::t("app", "Membership name, color, and discount are required."));
            }

            /** -------------------------
             * 4. Check for duplicate membership name under the same vendor
             * ------------------------ */
            $existingMembership = MemberShips::find()
                ->where(['vendor_details_id' => $vendorId, 'membership_name' => $membershipName])
                ->one();

            if ($existingMembership) {
                throw new BadRequestHttpException(Yii::t("app", "Membership name already exists for this vendor."));
            }

            /** -------------------------
             * 5. Create new Membership
             * ------------------------ */
            $membership   = new MemberShips();
            $membership->vendor_details_id = $vendorId;
            $membership->membership_name   = $membershipName;
            $membership->color             = $color;
            $membership->discount          = $discount;
            $membership->is_vip_plan       = $isVipPlan;
            $membership->membership_validity = $membership_validity;
            $membership->actual_price      = $actual_price;
            $membership->discount_price    = $discount_price;
            $membership->status            = MemberShips::STATUS_ACTIVE;

            if (! $membership->save(false)) {
                throw new Exception(Yii::t("app", "Failed to create membership: ") . json_encode($membership->getErrors()));
            }



            /** -------------------------
             * 7. Response
             * ------------------------ */
            $data['status']        = self::API_OK;
            $data['message']       = Yii::t("app", "Membership created successfully.");
            $data['membership_id'] = $membership->id;
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

    public function actionUpdateMembership()
    {
        $data        = [];

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
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




            $post = Yii::$app->request->post();
            $member_ships_id = trim($post['member_ships_id'] ?? '');
            $membershipName = trim($post['membership_name'] ?? '');
            $color          = trim($post['color'] ?? '');
            $discount       = trim($post['discount'] ?? '');
            $isVipPlan      = trim($post['is_vip_plan'] ?? '');
            $membership_validity       = trim($post['membership_validity'] ?? '');
            $actual_price       = trim($post['actual_price'] ?? '');
            $discount_price       = trim($post['discount_price'] ?? '');

            // Required fields check
            if (empty($membershipName) || empty($color) || $discount === '') {
                throw new BadRequestHttpException(Yii::t("app", "Membership name, color, and discount are required."));
            }

            /** -------------------------
             * 4. Check for duplicate membership name under the same vendor
             * ------------------------ */
            $membership = MemberShips::find()
                ->where(['vendor_details_id' => $vendorId, 'id' => $member_ships_id])
                ->one();

            if (empty($membership)) {
                throw new BadRequestHttpException(Yii::t("app", "Membership not found."));
            }

            /** -------------------------
             * 5. Create new Membership
             * ------------------------ */

            $membership->membership_name   = $membershipName;
            $membership->color             = $color;
            $membership->discount          = $discount;
            $membership->is_vip_plan       = $isVipPlan;
            $membership->membership_validity = $membership_validity;
            $membership->actual_price      = $actual_price;
            $membership->discount_price    = $discount_price;

            if (! $membership->save(false)) {
                throw new Exception(Yii::t("app", "Failed to create membership: ") . json_encode($membership->getErrors()));
            }



            /** -------------------------
             * 7. Response
             * ------------------------ */
            $data['status']        = self::API_OK;
            $data['message']       = Yii::t("app", "Membership created successfully.");
            $data['membership_id'] = $membership->id;
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



    public function actionChangeStatusMembership()
    {

        $data        = [];

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            $post = Yii::$app->request->post();

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            // Get vendor ID for logged-in user
            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }



            $member_ships_id = $post['member_ships_id'] ?? null;
            $status = $post['status'] ?? null;

            if (empty($member_ships_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Membership ID is required."));
            }

            $memberships = MemberShips::find()
                ->where(['vendor_details_id' => $vendorId])
                ->andWhere(['id' => $member_ships_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if (empty($memberships)) {
                throw new NotFoundHttpException(Yii::t("app", "Membership not found."));
            }

            $memberships->status = $status;
            $memberships->save(false);

            $data['status']      = self::API_OK;
            $data['message']     = Yii::t("app", "MemberShips fetched successfully.");
            $data['memberships'] = $memberships;
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




    public function actionGetClients()
    {
        $data = [];

        try {
            // -------------------------
            // 1. Authentication
            // -------------------------
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }

            // -------------------------
            // 2. Inputs & defaults
            // -------------------------
            $id = Yii::$app->request->get('id'); // optional single client id
            $page = max(1, (int)Yii::$app->request->post('page', 1));
            $pageSize = max(1, (int)Yii::$app->request->post('pageSize', 12));
            $pageSize = min($pageSize, 100); // safety cap
            $search = trim((string)Yii::$app->request->post('search', ''));
            $is_vip = Yii::$app->request->post('is_vip', null);
            $status = Yii::$app->request->post('status', null);
            $membership_id = Yii::$app->request->post('membership_id', []);


            // -------------------------
            // 3. Build query
            // -------------------------
            $query = StoresHasUsers::find()
                ->where(['vendor_details_id' => $vendorId])
                ->with(['guestUser']); // eager load guestUser relation to avoid N+1

            if (!empty($id)) {
                $query->andWhere(['guest_user_id' => $id]);
            }

            if (!empty($is_vip) || $is_vip === '0' || $is_vip === 0) {
                // treat non-empty numeric values and '0' appropriately
                $query->andWhere(['is_vip' => (int)$is_vip]);
            }

            if (!empty($status)) {
                $query->andWhere(['stores_has_users.status' => $status]);
            }

            // apply search on joined user columns if provided
            if (!empty($search)) {
                // alias guestUser table as 'u' to reference its columns safely
                $query->joinWith(['guestUser' => function ($q) {
                    $q->alias('u');
                }]);



                $likeCond = [
                    'or',
                    ['like', 'u.first_name', $search],
                    ['like', 'u.last_name', $search],
                    ['like', 'u.email', $search],
                    ['like', 'u.contact_no', $search],
                ];
                $query->andWhere($likeCond);
            }

            if(!empty($membership_id) && is_array($membership_id)) {
                // join with StoresUsersMemberships to filter by membership_id
                $query->joinWith(['storesUsersMemberships' => function ($q) use ($membership_id) {
                    $q->alias('sum'); // alias for clarity
                    $q->andWhere(['sum.membership_id' => $membership_id]);
                }]);
            }

            // -------------------------
            // 4. Count total (distinct)
            // -------------------------
            // Use distinct count on primary key to avoid duplicates when joinWith is used
            $table = StoresHasUsers::tableName();
            $primaryCol = $table . '.id';
            $totalCount = (clone $query)->select($primaryCol)->distinct()->count();

            // -------------------------
            // 5. Fetch results (with pagination if listing)
            // -------------------------
            if (empty($id)) {
                $clients = $query
                    ->offset(($page - 1) * $pageSize)
                    ->limit($pageSize)
                    ->all();
            } else {
                // single or filtered fetch - return all matching (should be 0 or 1 item)
                $clients = $query->all();
                $page = 1;
                $pageSize = count($clients);
            }

            // -------------------------
            // 6. Prepare response list
            // -------------------------
            $list = [];
            foreach ($clients as $store_user) {
                $list[] = $store_user->guestUser->asJsonUserClient($vendorId);
            }

            // -------------------------
            // 7. Final response
            // -------------------------
            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Clients fetched successfully.");
            $data['clients'] = $list;
            $data['pagination'] = [
                'page'       => $page,
                'pageSize'   => $pageSize,
                'totalCount' => (int)$totalCount,
                'totalPages' => (int)ceil($totalCount / max($pageSize, 1)),
            ];
        } catch (\yii\web\HttpException $e) {
            // Known HTTP exceptions (Unauthorized/BadRequest)
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['error_code'] = $e->statusCode ?: 400;

            Yii::warning([
                'message' => $e->getMessage(),
                'code' => $e->statusCode,
            ], __METHOD__);
        } catch (\Throwable $e) {
            // Unexpected errors
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t("app", "An unexpected error occurred while fetching clients.");
            $data['error_code'] = $e->getCode() ?: 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

















    public function actionViewClient()
    {
        $data = [];

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }



            /** -------------------------
             * 3. Get and validate POST data
             * ------------------------ */
            $post = Yii::$app->request->post();
            $client_id = $post['client_id'] ?? null;

            if (empty($client_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Client ID is required."));
            }

            /** -------------------------
             * 4. Find client with vendor relationship
             * ------------------------ */
            $storeUser = StoresHasUsers::find()
                ->where([
                    'vendor_details_id' => $vendorId,
                    'guest_user_id'     => $client_id
                ])
                ->one();

            if (!$storeUser) {
                throw new NotFoundHttpException(Yii::t("app", "Client not found or does not belong to your store."));
            }
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Client details fetched successfully.");
            $data['client']  = $storeUser->guestUser->asJsonUserClientView($vendorId);
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->getCode() ?: 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionAddVipClient()
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
                throw new UnauthorizedHttpException(Yii::t("app", "User is not associated with any vendor."));
            }

            // 2. Get raw JSON array
            $request = Yii::$app->request;
            $rawBody = $request->getRawBody();
            $clients = json_decode($rawBody, true);

            if (!is_array($clients)) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid request format. Expecting array of clients."));
            }

            $updated = [];
            foreach ($clients as $client) {
                $clientId = $client['client_id'] ?? null;

                if (empty($clientId)) {
                    continue;
                }

                // Find mapping record for vendor + client
                $stores_has_users = StoresHasUsers::find()
                    ->where([
                        'guest_user_id'     => $clientId,
                        'vendor_details_id' => $vendor_details_id
                    ])
                    ->all();

                if (!empty($stores_has_users)) {
                    foreach ($stores_has_users as $store_user) {
                        $store_user->is_vip = 1;
                        $store_user->save(false);
                    }
                    $updated[] = $clientId;
                }
            }

            $data['status']   = self::API_OK;
            $data['message']  = Yii::t("app", "VIP status updated successfully.");
            $data['updated']  = $updated;
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while updating VIP clients.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionClientWallet()
    {
        $data        = [];
        $transaction = null;

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get(
                'auth_code',
                Yii::$app->request->getQueryParam('auth_code')
            );
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException(
                    Yii::t("app", "User authentication failed. Please log in.")
                );
            }

            // 2. Vendor Id
            $getVendorIdByUser = User::getVendorIdByUserId($user_id);

            // 3. Input params
            $post              = Yii::$app->request->post();
            $store_has_user_id = $post['store_has_user_id'] ?? null;

            if (empty($store_has_user_id)) {
                throw new \yii\web\BadRequestHttpException(
                    Yii::t("app", "Missing required parameter: store_has_user_id")
                );
            }

            // 4. Validate store-user mapping
            $stores_has_users = StoresHasUsers::find()
                ->where([
                    'id'               => $store_has_user_id,
                    'vendor_details_id' => $getVendorIdByUser
                ])
                ->one();

            if (empty($stores_has_users)) {
                throw new \yii\web\NotFoundHttpException(
                    Yii::t("app", "Store user not found.")
                );
            }

            // 5. Wallet calculations
            $guest_user_id = $stores_has_users->guest_user_id;

            $deposit = [
                'current_balance' => GuestUserDeposits::getCreditedAmount($guest_user_id, $store_has_user_id),
                'total_deposited' => GuestUserDeposits::getDebitedAmount($guest_user_id, $store_has_user_id),
                'total_withdrawn' => GuestUserDeposits::getAvailableDepositBalance($guest_user_id, $store_has_user_id),
            ];

            // ✅ Success response
            $data['status'] = self::API_OK;
            $data['data']   = $deposit;
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error']      = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error']      = Yii::t("app", "An unexpected error occurred while fetching wallet details.");
                $data['error_code'] = 500;
            }

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionAddDeposit()
    {
        $data = [];
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

            // 2. Get and validate input parameters
            $post       = Yii::$app->request->post();
            $store_has_user_id  = $post['store_has_user_id'] ?? null;
            $amount    = $post['amount'] ?? null;
            $deposit_date_and_time = $post['deposit_date_and_time'] ?? null;
            $payment_mode = $post['payment_mode'] ?? null;

            // 3. Validate required fields
            if (empty($store_has_user_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Store user ID is required."));
            }

            if (empty($amount)) {
                throw new BadRequestHttpException(Yii::t("app", "Amount is required."));
            }
            if (empty($deposit_date_and_time)) {
                throw new BadRequestHttpException(Yii::t("app", "Deposit date and time is required."));
            }
            if (empty($payment_mode)) {
                throw new BadRequestHttpException(Yii::t("app", "Payment mode is required."));
            }

            // 4. Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            // 5. Check store user existence
            $stores_has_users = StoresHasUsers::find()->where([
                'id' => $store_has_user_id,
                'vendor_details_id' => $getVendorIdByUser
            ])->one();
            if (!$stores_has_users) {
                throw new NotFoundHttpException(Yii::t("app", "Store user not found."));
            }

            // 6. Generate unique order id not in db table 20 char string
            do {
                $order_id = Yii::$app->security->generateRandomString(20);
                $exists = GuestUserDeposits::find()->where(['order_id' => $order_id])->exists();
            } while ($exists);

            // 7. Add deposit
            $guest_user_deposits = new GuestUserDeposits();
            $guest_user_deposits->store_has_user_id = $store_has_user_id;
            $guest_user_deposits->guest_user_id = $stores_has_users->guest_user_id;
            $guest_user_deposits->amount = $amount;
            $guest_user_deposits->date_and_time = $deposit_date_and_time;
            $guest_user_deposits->payment_mode = $payment_mode;
            $guest_user_deposits->order_id = $order_id;
            $guest_user_deposits->payment_type = GuestUserDeposits::PAYMENT_TYPE_CREDIT;

            if (!$guest_user_deposits->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save deposit."));
            }

            $transaction->commit();

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Deposit added successfully.");
            $data['deposit'] = $guest_user_deposits->attributes;
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error'] = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error'] = Yii::t("app", "An unexpected error occurred while adding the deposit.");
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
