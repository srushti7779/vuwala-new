<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\admin\models\base\ProductOrderItems;
use app\modules\admin\models\Brands;
use app\modules\admin\models\MainCategory;
use app\modules\admin\models\MemberShips;
use app\modules\admin\models\Orders;
use app\modules\admin\models\ProductCategories;
use app\modules\admin\models\ProductOrders;
use app\modules\admin\models\Products;
use app\modules\admin\models\ProductTypes;
use app\modules\admin\models\Sku;
use app\modules\admin\models\StoreServiceTypes;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoresUsersMemberships;
use app\modules\admin\models\Units;
use app\modules\admin\models\UOMHierarchy;
use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorSuppliers;
use app\modules\admin\models\WastageProducts;
use app\modules\admin\models\WasteTypes;
use app\modules\api\controllers\BKController;
use Exception;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class InventoryController extends BKController
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
                            'brands',
                            'units',
                            'get-units-by-sku',
                            'create-client',
                            'get-clients',
                            'view-client',
                            'add-or-update-membership',
                            'get-memberships',
                            'add-or-update-suppliers',
                            'suppliers-list',
                            'suppliers-list-for-drop-down',
                            'view-supplier',
                            'product-types',
                            'product-categories-list',
                            'add-or-update-sku',
                            'add-vip-client',
                            'auto-generate-sku',
                            'sku-list',
                            'sku-drop-down-list',
                            'view-sku',
                            'product-list-by-sku',
                            'add-or-update-product',
                            'product-list',
                            'view-product',
                            'change-sku-status',
                            'search-users',
                            'supplier-transaction-history',
                            'wastage-types',
                            'add-or-update-wastage',
                            'wastage-products-list',
                            'get-all-sku',
                            'select-batch-number',
                            'expiring-products'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'brands',
                            'units',
                            'get-units-by-sku',
                            'create-client',
                            'get-clients',
                            'view-client',
                            'create-membership',
                            'get-memberships',
                            'add-or-update-suppliers',
                            'suppliers-list',
                            'suppliers-list-for-drop-down',
                            'view-supplier',
                            'product-types',
                            'product-categories-list',
                            'add-or-update-sku',
                            'add-vip-client',
                            'auto-generate-sku',
                            'sku-list',
                            'sku-drop-down-list',
                            'view-sku',
                            'product-list-by-sku',
                            'add-or-update-product',
                            'product-list',
                            'view-product',
                            'change-sku-status',
                            'search-users',
                            'supplier-transaction-history',
                            'wastage-types',
                            'add-or-update-wastage',
                            'wastage-products-list',
                            'get-all-sku',
                            'select-batch-number',
                            'expiring-products'






                        ],
                        'allow'   => true,
                        'roles'   => ['?', '*'],
                    ],
                ],
            ],
        ]);
    }


    public function actionBrands()
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

            $brands = Brands::find()->all();
            $list   = [];

            if (! empty($brands)) {
                foreach ($brands as $brand) {
                    $list[] = $brand->asJson();
                }
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Brands fetched successfully.");
                $data['details'] = $list;
            } else {
                // Return empty list, not an error
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "No brands found.");
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['error']   = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
        } catch (\Throwable $e) {
            $data['status']  = self::API_NOK;
            $data['error']   = $e->getMessage();
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUnits()
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

            $units = Units::find()->all();
            $list  = [];
            if (! empty($units)) {
                foreach ($units as $unit) {
                    $list[] = $unit->asJson();
                }
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "Units fetched successfully.");
                $data['details'] = $list;
            } else {
                // Return empty list, not an error
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t("app", "No units found.");
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['error']   = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
        } catch (\Throwable $e) {
            $data['status']  = self::API_NOK;
            $data['error']   = $e->getMessage();
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
        }

        return $this->sendJsonResponse($data);
    }



    public function actionGetUnitsBySku()
    {
        $data = [
            'status' => self::API_OK,
            'message' => '',
            'units' => [],
        ];

        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Auth check
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }

            $post = Yii::$app->request->post();
            $sku_id = $post['sku_id'] ?? null;

            if (empty($sku_id)) {
                throw new BadRequestHttpException(Yii::t("app", "SKU is required."));
            }

            // Get all hierarchy rows for given SKU
            $uom_hierarchy = UOMHierarchy::find()
                ->select(['units_id','of_units_id'])
                ->where(['sku_id' => $sku_id])
                ->asArray()
                ->all();

            if (empty($uom_hierarchy)) {
                $data['message'] = Yii::t("app", "No units found for this SKU.");
                return $this->sendJsonResponse($data);
            }

            // Collect all IDs (both units_id and of_units_id)
            $allUnitIds = [];
            foreach ($uom_hierarchy as $row) {
                if (!empty($row['units_id'])) {
                    $allUnitIds[] = $row['units_id'];
                }
                if (!empty($row['of_units_id'])) {
                    $allUnitIds[] = $row['of_units_id'];
                }
            }
            $allUnitIds = array_unique($allUnitIds);

            // Fetch all units for those IDs
            $units = Units::find()
                ->where(['id' => $allUnitIds])
                ->asArray()
                ->all();

            $data['units'] = $units;
            $data['message'] = Yii::t("app", "Units fetched successfully.");
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
                ->where(['email' => $email])
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
            $user->email      = $email;
            $user->contact_no = $contact_no;
            $user->address    = $address;
            $user->user_role  = $role;
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
            $data['client_id'] = $user->id;
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




   public function actionAddOrUpdateMembership()
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

        // Get vendor ID for logged-in user
        $vendorId = User::getVendorIdByUserId($user_id);
        if (empty($vendorId)) {
            throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
        }

        /** -------------------------
         * 2. Request Data
         * ------------------------ */
        $post = Yii::$app->request->post();

        $membershipId   = $post['membership_id'] ?? null; // <-- new
        $membershipName = trim($post['membership_name'] ?? '');
        $color          = trim($post['color'] ?? '');
        $discount       = trim($post['discount'] ?? '');

        if (empty($membershipName) || empty($color) || $discount === '') {
            throw new BadRequestHttpException(Yii::t("app", "Membership name, color, and discount are required."));
        }

        /** -------------------------
         * 3. Create or Update
         * ------------------------ */
        if ($membershipId) {
            // Update flow
            $membership = MemberShips::find()
                ->where(['id' => $membershipId, 'vendor_details_id' => $vendorId])
                ->one();

            if (!$membership) {
                throw new NotFoundHttpException(Yii::t("app", "Membership not found for this vendor."));
            }

            // Check duplicate (exclude current one)
            $existingMembership = MemberShips::find()
                ->where([
                    'vendor_details_id' => $vendorId,
                    'membership_name'   => $membershipName,
                ])
                ->andWhere(['!=', 'id', $membershipId])
                ->one();

            if ($existingMembership) {
                throw new BadRequestHttpException(Yii::t("app", "Membership name already exists for this vendor."));
            }

            // Update fields
            $membership->membership_name = $membershipName;
            $membership->color           = $color;
            $membership->discount        = $discount;

            if (!$membership->save()) {
                throw new Exception(Yii::t("app", "Failed to update membership: ") . json_encode($membership->getErrors()));
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Membership updated successfully.");
            $data['membership_name'] = $membershipName;
            $data['color'] = $color;
            $data['discount'] = $discount;
            
        } else {
            // Create flow
            $existingMembership = MemberShips::find()
                ->where(['vendor_details_id' => $vendorId, 'membership_name' => $membershipName])
                ->one();

            if ($existingMembership) {
                throw new BadRequestHttpException(Yii::t("app", "Membership name already exists for this vendor."));
            }

            $membership = new MemberShips();
            $membership->vendor_details_id = $vendorId;
            $membership->membership_name   = $membershipName;
            $membership->color             = $color;
            $membership->discount          = $discount;
            $membership->status            = MemberShips::STATUS_ACTIVE;

            if (!$membership->save()) {
                throw new Exception(Yii::t("app", "Failed to create membership: ") . json_encode($membership->getErrors()));
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Membership created successfully.");
        }
           $data['membership_id'] = $membership->id;
            $data['membership_name'] = $membershipName;
            $data['color'] = $color;
            $data['discount'] = $discount;
          
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


            if (!Yii::$app->request->isGet) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only GET requests are allowed."));
            }


            $memberships = MemberShips::find()
                ->where(['vendor_details_id' => $vendorId])
                ->orderBy(['id' => SORT_DESC])
                ->all();



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

        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** -------------------------
             * 1. Authentication
             * ------------------------ */
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));

            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(
                    Yii::t("app", "User authentication failed. Please log in.")
                );
            }

            $vendorId = User::getVendorIdByUserId($user_id);
            if (empty($vendorId)) {
                throw new BadRequestHttpException(
                    Yii::t("app", "Vendor ID not found for this user.")
                );
            }


            $id = Yii::$app->request->get('id');

            /** -------------------------
             * 3. Build query
             * ------------------------ */
            $query = StoresHasUsers::find()
                ->where(['vendor_details_id' => $vendorId]);

            if (!empty($id)) {
                // Filter for specific client
                $query->andWhere(['guest_user_id' => $id]);
            }

            /** -------------------------
             * 4. Fetch results
             * ------------------------ */
            if (empty($id)) {
                // Pagination for all clients
                $page     = max(1, (int) Yii::$app->request->post('page', 1));
                $pageSize = max(1, (int) Yii::$app->request->post('pageSize', 10));
                $is_vip = Yii::$app->request->post('is_vip');


                if (!empty($is_vip)) {
                    $query->andWhere(['is_vip' => $is_vip]);
                }

                $totalCount = (clone $query)->count();
                $clients = $query
                    ->offset(($page - 1) * $pageSize)
                    ->limit($pageSize)
                    ->all();
            } else {
                // Single client fetch (no pagination)
                $clients = $query->all();
                $totalCount = count($clients);
                $page = 1;
                $pageSize = $totalCount;
            }

            /** -------------------------
             * 5. Prepare response
             * ------------------------ */
            $list = [];
            foreach ($clients as $store_user) {
                $list[] = $store_user->asJson();
            }

            $transaction->commit();

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Clients fetched successfully.");
            $data['clients'] = $list;
            $data['pagination'] = [
                'page'       => $page,
                'pageSize'   => $pageSize,
                'totalCount' => (int) $totalCount,
                'totalPages' => ceil($totalCount / max($pageSize, 1)),
            ];
        } catch (\Throwable $e) {
            if ($transaction->getIsActive()) {
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
            $data['client']  = $storeUser->asJson();
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


    public function actionAddOrUpdateSuppliers()
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
                throw new BadRequestHttpException(Yii::t("app", "Vendor ID not found for this user."));
            }

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            $post = Yii::$app->request->post();

            $suppliers_firm_name = trim($post['suppliers_firm_name'] ?? '');
            $contact_person      = trim($post['contact_person'] ?? '');
            $gst_number          = trim($post['gst_number'] ?? '');
            $phone_number        = trim($post['phone_number'] ?? '');
            $mail                = trim($post['mail'] ?? '');
            $location            = trim($post['location'] ?? '');
            $vendor_supplier_id  = $post['vendor_supplier_id'] ?? null;

            // 3. Duplicate prevention (firm name, GST, or phone number for same vendor)
            $duplicateQuery = VendorSuppliers::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere([
                    'or',
                    ['suppliers_firm_name' => $suppliers_firm_name],
                    ['gst_number' => $gst_number],
                    ['phone_number' => $phone_number],
                ]);

            if (!empty($vendor_supplier_id)) {
                $duplicateQuery->andWhere(['<>', 'id', $vendor_supplier_id]); // exclude self on update
            }

            if ($duplicateQuery->exists()) {
                throw new ConflictHttpException(Yii::t("app", "A supplier with the same details already exists."));
            }

            // 4. Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            if (!empty($vendor_supplier_id)) {
                $vendor_suppliers = VendorSuppliers::findOne(['id' => $vendor_supplier_id, 'vendor_details_id' => $vendor_details_id]);
                if (!$vendor_suppliers) {
                    throw new NotFoundHttpException(Yii::t("app", "Supplier not found."));
                }
            } else {
                $vendor_suppliers = new VendorSuppliers();
            }

            $vendor_suppliers->suppliers_firm_name = $suppliers_firm_name;
            $vendor_suppliers->contact_person      = $contact_person;
            $vendor_suppliers->gst_number          = $gst_number;
            $vendor_suppliers->phone_number        = $phone_number;
            $vendor_suppliers->mail                = $mail;
            $vendor_suppliers->location            = $location;
            $vendor_suppliers->vendor_details_id   = $vendor_details_id;

            if (!$vendor_suppliers->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save supplier."));
            }

            $transaction->commit();

            $data['status']   = self::API_OK;
            $data['supplier'] = $vendor_suppliers->asJson();
        } catch (\Throwable $e) {
            if (!empty($transaction) && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

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


    public function actionSuppliersList()
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

            // 2. Validate method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST data
            $post     = Yii::$app->request->post();
            $search   = trim($post['search'] ?? '');
            $page     = max(1, ($post['page'] ?? 1));
            $pageSize = max(1, ($post['per_page'] ?? 10));

            // 4. Build query
            $query = VendorSuppliers::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->orderBy(['id' => SORT_DESC]);

            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'suppliers_firm_name', $search],
                    ['like', 'contact_person', $search],
                    ['like', 'phone_number', $search],
                    ['like', 'mail', $search],
                ]);
            }

            // 5. Pagination
            $totalCount = $query->count();
            $suppliers  = $query
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->all();

            // 6. Format data
            $suppliersData = array_map(function ($supplier) {
                return $supplier->asJson();
            }, $suppliers);

            $data = [
                'status' => self::API_OK,
                'pagination' => [
                    'page'       => $page,
                    'per_page'   => $pageSize,
                    'total'      => (int)$totalCount,
                    'total_page' => ceil($totalCount / $pageSize),
                ],
                'suppliers' => $suppliersData,
            ];
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching suppliers.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionSuppliersListForDropDown()
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

            // 2. Validate method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST data
            $search   = trim($post['search'] ?? '');


            // 4. Build query
            $query = VendorSuppliers::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->orderBy(['id' => SORT_DESC]);

            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'suppliers_firm_name', $search],
                    ['like', 'contact_person', $search],
                    ['like', 'phone_number', $search],
                    ['like', 'mail', $search],
                ]);
            }


            $suppliers  = $query->all();

            // 6. Format data
            $suppliersData = array_map(function ($supplier) {
                return $supplier->asJsonForDropDown();
            }, $suppliers);

            $data = [
                'status' => self::API_OK,
                'suppliers' => $suppliersData,
            ];
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching suppliers.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionViewSupplier()
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



            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get supplier ID from POST
            $post = Yii::$app->request->post();
            $vendor_supplier_id = ($post['vendor_supplier_id'] ?? 0);

            if (empty($vendor_supplier_id)) {
                throw new BadRequestHttpException(Yii::t("app", "vendor_supplier_id is required."));
            }

            // 4. Fetch supplier, ensuring it belongs to the authenticated vendor
            $supplier = VendorSuppliers::findOne([
                'id' => $vendor_supplier_id,
                'vendor_details_id' => $vendor_details_id
            ]);

            if (!$supplier) {
                throw new NotFoundHttpException(Yii::t("app", "Supplier not found or does not belong to your account."));
            }

            // 5. Response data
            $data['status']   = self::API_OK;
            $data['supplier'] = $supplier->asJsonView();
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching supplier details.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionProductTypes()
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


            $product_types_list = [];
            $product_types = ProductTypes::find()->all();
            if (!empty($product_types)) {
                foreach ($product_types as $product_types_data) {
                    $product_types_list[] = $product_types_data->asJson();
                }
            }


            // 5. Response data
            $data['status']   = self::API_OK;
            $data['details'] = $product_types_list;
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching supplier details.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionProductCategoriesList(){
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

            $product_categories = ProductCategories::find()->all();
           if (!empty($product_categories)) {
               foreach ($product_categories as $product_category_data) {
                   $data['details'][] = $product_category_data->asJson();
               }
           }
            // 5. Response data
            $data['status']   = self::API_OK;
            $data['details'] = $data['details'] ?? [];


    }catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching supplier details.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }








    public function actionAddOrUpdateSku()
    {
        $data = [];

        // try {
        // 1. Authentication
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
        }

        $vendor_details_id = User::getVendorIdByUserId($user_id);
        if (empty($vendor_details_id)) {
            throw new UnauthorizedHttpException(Yii::t('app', 'User is not associated with any vendor.'));
        }

        // 2. Parse Request
        $request = Yii::$app->request;
        $post = json_decode($request->getRawBody(), true);

        if (empty($post)) {
            throw new BadRequestHttpException(Yii::t('app', 'Invalid request payload.'));
        }

        // Extract Data
        $sku_id                    = $post['sku_id'] ?? null;
        $sku_code                  = $post['sku_code'] ?? null;
        $product_category_id = $post['product_category_id'] ?? null;
        $product_name              = $post['product_name'] ?? null;
        $brand_id                  = $post['brand_id'] ?? null;
        $ean_code                  = $post['ean_code'] ?? null;
        $category_id               = $post['category_id'] ?? null;
        $product_type_id           = $post['product_type_id'] ?? null;
        $tax_rate                  = $post['tax_rate'] ?? null;
        $base_unit_id              = $post['base_unit_id'] ?? null;
        $re_order_level_for_alerts = $post['re_order_level_for_alerts'] ?? null;
        $uom_id_re_order_level     = $post['uom_id_re_order_level'] ?? null;
        $min_quantity_need         = $post['min_quantity_need'] ?? 0;
        $description               = $post['description'] ?? null;
        $image                     = $post['image'] ?? null;
        $u_o_m_hierarchy           = $post['u_o_m_hierarchy'] ?? [];

        // --- 2.1. Error Validation ---
        $errors = [];

        // Required fields
        if (empty($sku_code)) $errors['sku_code'] = 'SKU code is required.';
        if( empty($product_category_id)) $errors['product_category_id'] = 'Product category is required.';
        if (empty($product_name)) $errors['product_name'] = 'Product name is required.';
        if (empty($brand_id)) $errors['brand_id'] = 'Brand is required.';
        if (empty($category_id)) $errors['category_id'] = 'Category is required.';
        if (empty($product_type_id)) $errors['product_type_id'] = 'Product type is required.';
        if ($tax_rate === null || $tax_rate === '') $errors['tax_rate'] = 'Tax rate is required.';
        if (empty($base_unit_id)) $errors['base_unit_id'] = 'Base unit is required.';

        // UOM Hierarchy validation (if provided)
        if (!empty($u_o_m_hierarchy) && !is_array($u_o_m_hierarchy)) {
            $errors['u_o_m_hierarchy'] = 'UOM Hierarchy must be an array.';
        }

        // If any errors, return error response
        if (!empty($errors)) {
            $data['status'] = self::API_NOK;
            $data['error'] = 'Validation failed.';
            $data['validation_errors'] = $errors;
            $data['error_code'] = 422;
            return $this->sendJsonResponse($data);
        }

        // --- 2.2. UOM Hierarchy Specific Validation ---
        $uomErrors = [];
        $uomPairs = []; // To track pairs (units_id, of_units_id)
        $uomReverses = []; // To track reverses (of_units_id, units_id)
        $ofUnitsIds = []; // To track of_units_id for base_unit_id and uom_id_re_order_level validation

        if (!empty($u_o_m_hierarchy)) {
            foreach ($u_o_m_hierarchy as $index => $uom) {
                $unitsId = $uom['units_id'] ?? null;
                $ofUnitsId = $uom['of_units_id'] ?? null;
                $quantity = $uom['quantity'] ?? null;

                // Required fields for each UOM entry
                if (empty($unitsId)) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: units_id is required.";
                }
                if (empty($ofUnitsId)) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: of_units_id is required.";
                }
                if (empty($quantity) || $quantity <= 0) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: quantity is required and must be positive.";
                }

                // No self-reference (units_id == of_units_id)
                if ($unitsId == $ofUnitsId && !empty($unitsId)) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: Self-reference not allowed (units_id {$unitsId} cannot be the same as of_units_id {$ofUnitsId}).";
                }

                // No duplicate pairs
                $pairKey = "{$unitsId}-{$ofUnitsId}";
                if (isset($uomPairs[$pairKey])) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: Duplicate entry for units_id {$unitsId} and of_units_id {$ofUnitsId}.";
                } else {
                    $uomPairs[$pairKey] = true;
                }

                // No reverse pairs
                $reverseKey = "{$ofUnitsId}-{$unitsId}";
                if (isset($uomReverses[$reverseKey]) || isset($uomPairs[$reverseKey])) {
                    $uomErrors[] = "UOM Hierarchy entry #$index: Reverse entry not allowed (units_id {$unitsId} and of_units_id {$ofUnitsId} reverses an existing pair).";
                } else {
                    $uomReverses[$pairKey] = true;
                }

                // Collect of_units_id for validation
                if (!empty($ofUnitsId)) {
                    $ofUnitsIds[$ofUnitsId] = true;
                }
            }

        

            

            // If UOM errors, return them
            if (!empty($uomErrors)) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'UOM Hierarchy validation failed.';
                $data['uom_errors'] = $uomErrors;
                $data['error_code'] = 422;
                return $this->sendJsonResponse($data);
            }
        }

        // 3. Find or Create SKU
        if (!empty($sku_id)) {
            $sku = Sku::findOne(['id' => $sku_id]);
            if (!$sku) {
                throw new NotFoundHttpException(Yii::t('app', 'SKU not found.'));
            }
        } else {
            $sku = new Sku();
        }

        // 4. Assign values
        $sku->sku_code                  = $sku_code;
        $sku->product_category_id       = $product_category_id;
        $sku->product_name              = $product_name;
        $sku->brand_id                  = $brand_id;
        $sku->ean_code                  = $ean_code;
        $sku->category_id               = $category_id;
        $sku->product_type_id           = $product_type_id;
        $sku->tax_rate                  = $tax_rate;
        $sku->base_unit_id              = $base_unit_id;
        $sku->re_order_level_for_alerts = $re_order_level_for_alerts;
        $sku->uom_id_re_order_level     = $uom_id_re_order_level;
        $sku->min_quantity_need         = $min_quantity_need;
        $sku->description               = $description;
        $sku->image                     = $image;
        $sku->vendor_details_id         = $vendor_details_id;



        // Save SKU and UOM hierarchy within a transaction
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$sku->save(false)) {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to save SKU.'));
            }

            // 5. Handle UOM Hierarchy (Clear old if update)
            if (!empty($u_o_m_hierarchy)) {
                // Remove old hierarchy records (only if updating)
                $existingUomHierarchies = UOMHierarchy::find()->where(['sku_id' => $sku->id])->all();
                if (!empty($existingUomHierarchies)) {
                    foreach ($existingUomHierarchies as $existingUom) {
                        $existingUom->status = UOMHierarchy::STATUS_INACTIVE;
                        if (!$existingUom->save(false)) {
                            throw new ServerErrorHttpException(Yii::t('app', 'Failed to update existing UOM hierarchy.'));
                        }
                    }
                }

                foreach ($u_o_m_hierarchy as $uom) {
                    $skuUom = UOMHierarchy::find()->where([
                        'sku_id' => $sku->id,
                        'units_id' => $uom['units_id'],
                        'of_units_id' => $uom['of_units_id'],
                    ])->one();

                    if (empty($skuUom)) {
                        $skuUom = new UOMHierarchy();
                    }

                    $skuUom->sku_id      = $sku->id;
                    $skuUom->units_id    = $uom['units_id'];
                    $skuUom->quantity    = $uom['quantity'];
                    $skuUom->of_units_id = $uom['of_units_id'];
                    $skuUom->status      = UOMHierarchy::STATUS_ACTIVE;

                    if (!$skuUom->save(false)) {
                        throw new ServerErrorHttpException(Yii::t('app', "Failed to save UOM hierarchy entry for units_id {$uom['units_id']} and of_units_id {$uom['of_units_id']}."));
                    }
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $data['status'] = self::API_OK;
        $data['message'] = !empty($sku_id) ? 'SKU updated successfully.' : 'SKU added successfully.';
        $data['sku'] = $sku->asJson();
        // } catch (\Throwable $e) {
        //     $data['status'] = self::API_NOK;
        //     $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred while processing SKU.');
        //     $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

        //     Yii::error([
        //         'message' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //     ], __METHOD__);
        // }

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
    public function actionAutoGenerateSku()
    {
        $data = [];

        try {
            // 1. Authentication
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User authentication failed. Please log in.'));
            }

            // 2. Fetch vendor details
            $vendor = VendorDetails::find()
                ->where(['user_id' => $user_id])
                ->one();
            if (!$vendor) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User is not associated with any vendor.'));
            }



            // 4. Generate vendor prefix
            $vendor_prefix =  $this->generateVendorPrefix($vendor->business_name);

            // 5. Generate category code (if provided)


            $category_code = 'GEN';

            // 6. Generate SKU code
            $max_attempts = 10;
            $attempt = 0;
            $sku_code = null;

            while ($attempt < $max_attempts) {
                $timestamp = date('YmdHis'); // e.g., 20250829132456
                $counter = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT); // Random 3-digit number
                $sku_code = sprintf('%s-%s-%s-%s', $vendor_prefix, $category_code, $timestamp, $counter);

                // Check uniqueness
                if (!Sku::find()->where(['sku_code' => $sku_code])->exists()) {
                    break;
                }

                $attempt++;
                if ($attempt === $max_attempts) {
                    throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to generate unique SKU after {max_attempts} attempts.', ['max_attempts' => $max_attempts]));
                }
            }

            // 7. Prepare response
            $data['status'] = self::API_OK;
            $data['message'] = Yii::t('app', 'SKU generated successfully.');
            $data['sku'] = $sku_code;
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof \yii\web\HttpException ? $e->getMessage() : Yii::t('app', 'An unexpected error occurred while generating SKU.');
            $data['error_code'] = $e instanceof \yii\web\HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    private function generateVendorPrefix($firm_name)
    {
        // Remove non-alphanumeric characters and take first 3 characters
        $clean_name = preg_replace('/[^A-Za-z0-9]/', '', $firm_name);
        $prefix = strtoupper(substr($clean_name, 0, 3));
        return $prefix ?: 'VND'; // Fallback if name is empty or too short
    }

    public function actionSkuList()
    {
        $data = [];

        // try {
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

        // 2. Validate request method
        if (!Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
        }

        // 3. Get POST parameters
        $post     = Yii::$app->request->post();
        $search   = trim($post['search'] ?? '');
        $page     = max(1, ($post['page'] ?? 1));
        $pageSize = max(1, min(100, ($post['per_page'] ?? 10))); // Limit max page size
        $brand_id = $post['brand_id'] ?? null;
        $category_id = $post['category_id'] ?? null;
        $product_type_id = $post['product_type_id'] ?? null;

        // 4. Build query
        $query = Sku::find()
            ->where(['vendor_details_id' => $vendor_details_id])
            ->orderBy(['id' => SORT_DESC]);

        // Apply filters
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'product_name', $search],
                ['like', 'sku_code', $search],
                ['like', 'ean_code', $search],
                ['like', 'description', $search],
            ]);
        }

        if (!empty($brand_id)) {
            $query->andWhere(['brand_id' => $brand_id]);
        }

        if (!empty($category_id)) {
            $query->andWhere(['category_id' => $category_id]);
        }

        if (!empty($product_type_id)) {
            $query->andWhere(['product_type_id' => $product_type_id]);
        }

        // 5. Get total count for pagination
        $totalCount = $query->count();

        // 6. Get paginated results
        $skus = $query
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        // 7. Format response data
        $skuList = [];
        foreach ($skus as $sku) {
            $skuList[] = $sku->asJson();
        }

        $data = [
            'status' => self::API_OK,
            'message' => Yii::t("app", "SKU list fetched successfully."),
            'skus' => $skuList,
            'pagination' => [
                'page'       => $page,
                'per_page'   => $pageSize,
                'total'      => (int)$totalCount,
                'total_pages' => ceil($totalCount / $pageSize),
            ],
            'filters' => [
                'search' => $search,
                'brand_id' => $brand_id,
                'category_id' => $category_id,
                'product_type_id' => $product_type_id,
            ]
        ];
        // } catch (\Throwable $e) {
        //     $data['status']     = self::API_NOK;
        //     $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching SKU list.");
        //     $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

        //     Yii::error([
        //         'message' => $e->getMessage(),
        //         'trace'   => $e->getTraceAsString(),
        //     ], __METHOD__);
        // }

        return $this->sendJsonResponse($data);
    }


    public function actionSkuDropDownList()
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


            // 3. Get POST parameters
            $post     = Yii::$app->request->post();
            $search   = trim($post['search'] ?? '');


            // 4. Build query
            $query = Sku::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['NOT IN', 'id', Products::find()->select('sku_id')])
                ->orderBy(['id' => SORT_DESC]);

            // Apply filters
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'product_name', $search],
                    ['like', 'sku_code', $search],
                    ['like', 'ean_code', $search],
                    ['like', 'description', $search],
                ]);
            }


            // 6. Get paginated results
            $skus = $query->all();

            // 7. Format response data
            $skuList = [];
            foreach ($skus as $sku) {
                $skuList[] = $sku->asJsonForDropDownList();
            }

            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "SKU list fetched successfully."),
                'skus' => $skuList,
                'filters' => [
                    'search' => $search,

                ]
            ];
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching SKU list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }






    public function actionViewSku()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post = Yii::$app->request->post();
            $sku_id = $post['sku_id'] ?? null;

            // 4. Validate required parameters
            if (empty($sku_id)) {
                throw new BadRequestHttpException(Yii::t("app", "SKU ID is required."));
            }

            // 5. Find the SKU
            $sku = Sku::find()
                ->where([
                    'id' => $sku_id,
                    'vendor_details_id' => $vendor_details_id
                ])
                ->one();

            if (!$sku) {
                throw new NotFoundHttpException(Yii::t("app", "SKU not found or you don't have permission to view it."));
            }

            // 6. Format response data
            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "SKU details fetched successfully."),
                'sku' => $sku->asJson()
            ];
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching SKU details.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionProductListBySku()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post = Yii::$app->request->post();
            $sku_id = $post['sku_id'] ?? null;
            $search = trim($post['search'] ?? '');
            $supplier_id = $post['supplier_id'] ?? null;

            // 4. Validate required parameters
            if (empty($sku_id)) {
                throw new BadRequestHttpException(Yii::t("app", "SKU ID is required."));
            }

            // 5. Verify SKU exists and belongs to vendor
            $sku = Sku::find()
                ->where([
                    'id' => $sku_id,
                    'vendor_details_id' => $vendor_details_id
                ])
                ->one();

            if (!$sku) {
                throw new NotFoundHttpException(Yii::t("app", "SKU not found or you don't have permission to view it."));
            }

            // 6. Build products query
            $query = Products::find()
                ->joinWith(['sku'])
                ->where([
                    'products.sku_id' => $sku_id,
                ])
                ->andWhere(['>=', 'products.expire_date', date('Y-m-d')]) // Only future or today

                ->orderBy(['products.id' => SORT_DESC]);

            // Apply additional filters
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'products.batch_number', $search],
                    ['like', 'products.invoice_number', $search],
                    ['like', 'products.supplier_name', $search],
                ]);
            }

            if (!empty($supplier_id)) {
                $query->andWhere(['products.supplier_id' => $supplier_id]);
            }

            // 8. Get paginated results
            $products = $query->all();

            // 9. Format response data
            $productList = [];
            foreach ($products as $product) {
                $productData = $product->asJson();
                $productList[] = $productData;
            }

            // Always return an API_OK status and products (even if empty)
            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "Product list fetched successfully for SKU: {sku_code}", ['sku_code' => $sku->sku_code]),
                'products' => $productList, // Will be empty array if no products found
                'filters' => [
                    'search' => $search
                ]
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching product list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        // Always return data, even if products array is empty
        return $this->sendJsonResponse($data);
    }


    public function actionAddOrUpdateProduct()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post = Yii::$app->request->post();
            $sku_id           = !empty($post['sku_id']) ? $post['sku_id'] : null;
            $discount_allowed = !empty($post['discount_allowed']) ? $post['discount_allowed'] : 0;
            $minimum_stock    = !empty($post['minimum_stock']) ? $post['minimum_stock'] : 0;
            $units_id         = !empty($post['units_id']) ? $post['units_id'] : null;
            $supplier_id      = !empty($post['supplier_id']) ? $post['supplier_id'] : null;
            $batch_number     = !empty($post['batch_number']) ? $post['batch_number'] : null;
            $ean_code         = !empty($post['ean_code']) ? $post['ean_code'] : null;

            $purchase_date    = !empty($post['purchase_date']) ? $post['purchase_date'] : null;
            $expire_date      = !empty($post['expire_date']) ? $post['expire_date'] : null;
            $units_received   = !empty($post['units_received']) ? $post['units_received'] : 0;
            $received_units_id = !empty($post['received_units_id']) ? $post['received_units_id'] : null;
            $invoice_number   = !empty($post['invoice_number']) ? $post['invoice_number'] : null;
            $selling_price    = !empty($post['selling_price']) ? $post['selling_price'] : null;
            $mrp_price        = !empty($post['mrp_price']) ? $post['mrp_price'] : null;
            $purchased_price = !empty($post['purchased_price']) ? $post['purchased_price'] : null;
            $status = !empty($post['status']) ? $post['status'] : Products::STATUS_ACTIVE;


            // 4. Validate required fields
            $requiredFields = [
                'sku_id',
                'supplier_id',
                'batch_number',
                'purchase_date',
                'expire_date',
                'units_received',
                'received_units_id',
                'invoice_number',
                'mrp_price',
                'selling_price',
                'purchased_price',
                'ean_code'
            ];

            foreach ($requiredFields as $field) {
                if (empty($$field)) {
                    throw new BadRequestHttpException(Yii::t("app", "Missing required field: {field}", ['field' => $field]));
                }
            }
            $get_supplier = VendorSuppliers::findOne(['id' => $supplier_id, 'vendor_details_id' => $vendor_details_id]);
            if (!$get_supplier) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid supplier ID."));
            }
            $sku = Sku::findOne(['id' => $sku_id, 'vendor_details_id' => $vendor_details_id]);
            if (!$sku) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid SKU ID."));
            }

            // 5. Check if product exists
            $product = Products::findOne(['sku_id' => $sku_id, 'vendor_details_id' => $vendor_details_id, 'supplier_id' => $supplier_id, 'batch_number' => $batch_number]);
            if (!$product) {
                $product = new Products();
                $product->vendor_details_id = $vendor_details_id;
            }

            // 6. Update product attributes
            $product->sku_id = $sku_id;
            $product->discount_allowed = $discount_allowed;
            // $product->minimum_stock = $minimum_stock;
            // $product->units_id = $units_id;
            $product->supplier_id = $supplier_id;
            $product->batch_number = $batch_number;
            $product->ean_code = $ean_code;
            $product->purchase_date = $purchase_date;
            $product->expire_date = $expire_date;
            $product->units_received = $units_received;
            $product->received_units_id = $received_units_id;
            $product->invoice_number = $invoice_number;
            $product->mrp_price = $mrp_price;
            $product->selling_price = $selling_price;
            $product->purchased_price = $purchased_price;
            $product->status = $status;



            // 7. Save product
            if (!$product->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save product."));
            }

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Product added/updated successfully.");
            $data['product'] = $product->asJson();
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching SKU list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionProductList()
    {
        $data = [];

        // try {
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

        // 2. Validate request method
        if (!Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
        }

        // 3. Get POST parameters
        $post     = Yii::$app->request->post();
        $search   = trim($post['search'] ?? '');
        $page     = max(1, ($post['page'] ?? 1));
        $pageSize = max(1, min(100, ($post['per_page'] ?? 10))); // Limit max page size
        $sku_id = $post['sku_id'] ?? null;
        $supplier_id = $post['supplier_id'] ?? null;
        $low_stock = $post['low_stock'] ?? null; // Filter for low stock products

        // 4. Build query
        $query = Products::find()
            ->joinWith(['sku'])
            ->where(['sku.vendor_details_id' => $vendor_details_id])
            ->orderBy(['products.id' => SORT_DESC]);

        // Apply filters
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'sku.product_name', $search],
                ['like', 'sku.sku_code', $search],
                ['like', 'products.batch_number', $search],
                ['like', 'products.invoice_number', $search],
            ]);
        }

        if (!empty($sku_id)) {
            $query->andWhere(['products.sku_id' => $sku_id]);
        }

        if (!empty($supplier_id)) {
            $query->andWhere(['products.supplier_id' => $supplier_id]);
        }

        if (!empty($low_stock)) {
            // Filter products where current stock is below minimum stock
            $query->andWhere('products.units_received <= products.minimum_stock');
        }

        // 5. Get total count for pagination
        $totalCount = $query->count();

        // 6. Get paginated results
        $products = $query
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        // 7. Format response data
        $productList = [];
        foreach ($products as $product) {
            $productData = $product->asJson();
            $productList[] = $productData;
        }

        $data = [
            'status' => self::API_OK,
            'message' => Yii::t("app", "Product list fetched successfully."),
            'products' => $productList,
            'pagination' => [
                'page'       => $page,
                'per_page'   => $pageSize,
                'total'      => (int)$totalCount,
                'total_pages' => ceil($totalCount / $pageSize),
            ],
            'filters' => [
                'search' => $search,
                'sku_id' => $sku_id,
                'supplier_id' => $supplier_id,
                'low_stock' => $low_stock,
            ]
        ];

        // } catch (\Throwable $e) {
        //     $data['status']     = self::API_NOK;
        //     $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching product list.");
        //     $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

        //     Yii::error([
        //         'message' => $e->getMessage(),
        //         'trace'   => $e->getTraceAsString(),
        //     ], __METHOD__);
        // }

        return $this->sendJsonResponse($data);
    }

    public function actionViewProduct()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get and validate POST data
            $post = Yii::$app->request->post();
            $product_id = $post['product_id'] ?? null;

            if (empty($product_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Product ID is required."));
            }

            // 4. Find product with vendor relationship
            $product = Products::find()
                ->joinWith(['sku'])
                ->where([
                    'products.id' => $product_id,
                    'sku.vendor_details_id' => $vendor_details_id
                ])

                ->one();

            if (!$product) {
                throw new NotFoundHttpException(Yii::t("app", "Product not found or does not belong to your store."));
            }

            // 5. Prepare comprehensive response
            $productData = $product->asJson();

            // Add calculated fields
            $productData['stock_status'] = $product->units_received <= $product->minimum_stock ? 'low' : 'normal';
            $productData['days_to_expire'] = null;
            $productData['is_expired'] = false;

            if ($product->expire_date) {
                $expireDate = new \DateTime($product->expire_date);
                $currentDate = new \DateTime();
                $interval = $currentDate->diff($expireDate);
                $productData['days_to_expire'] = $expireDate > $currentDate ? $interval->days : -$interval->days;
                $productData['is_expired'] = $expireDate <= $currentDate;
                $productData['expire_status'] = $productData['is_expired'] ? 'expired' : ($productData['days_to_expire'] <= 30 ? 'expiring_soon' : 'good');
            }

            // Add stock value calculation
            if ($product->sku && $product->sku->selling_price) {
                $productData['stock_value'] = $product->units_received * $product->sku->selling_price;
            }

            // Add purchase value
            if ($product->sku && $product->sku->mrp_price) {
                $productData['purchase_value'] = $product->units_received * $product->sku->mrp_price;
            }

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Product details fetched successfully.");
            $data['product'] = $productData;
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

    public function actionChangeSkuStatus()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get and validate POST data
            $post = Yii::$app->request->post();
            $sku_id = $post['sku_id'] ?? null;
            $status = $post['status'] ?? null;

            if (empty($sku_id)) {
                throw new BadRequestHttpException(Yii::t("app", "SKU ID is required."));
            }

            if ($status === null || $status === '') {
                throw new BadRequestHttpException(Yii::t("app", "Status is required."));
            }

            // Validate status value
            $validStatuses = [
                Sku::STATUS_ACTIVE,
                Sku::STATUS_INACTIVE,
                Sku::STATUS_DELETE
            ];

            if (!in_array($status, $validStatuses)) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid status value. Valid values are: " . implode(', ', $validStatuses)));
            }

            // 4. Find SKU with vendor relationship
            $sku = Sku::find()
                ->where([
                    'id' => $sku_id,
                    'vendor_details_id' => $vendor_details_id
                ])
                ->one();

            if (!$sku) {
                throw new NotFoundHttpException(Yii::t("app", "SKU not found or does not belong to your store."));
            }

            // 5. Store old status for response
            $oldStatus = $sku->status;


            // 6. Update SKU status
            $sku->status = $status;
            $sku->update_user_id = $user_id;

            if (!$sku->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update SKU status."));
            }
            $newStatus = $sku->status;

            // 7. Log the status change
            Yii::info("SKU status changed from {$oldStatus} to {$newStatus} by user {$user_id}.", __METHOD__);

            // 8. Prepare response with updated SKU data
            $skuData = $sku->asJson();

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "SKU status updated successfully from {$oldStatus} to {$newStatus}.", [
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus
            ]);
            $data['sku'] = $skuData;
            $data['status_change'] = [
                'old_status' => $oldStatus,
                'new_status' => $status,
                'updated_by' => $user_id,
                'updated_at' => date('Y-m-d H:i:s')
            ];
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


    public function actionSearchUsers()
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

            $post = Yii::$app->request->post();
            $search = $post['search'] ?? null;

            // Prepare store users query
            $users_store = User::find()
                ->joinWith(['storesHasUsers as su'])
                ->where(['user.user_role' => User::ROLE_GUEST, 'su.vendor_details_id' => $vendor_details_id]);

            // Prepare global users query
            $users_global = User::find()
                ->where(['user.user_role' => User::ROLE_USER]);

            // Add search filter if provided
            if ($search) {
                $orCondition = [
                    'or',
                    ['like', 'username', $search],
                    ['like', 'contact_no', $search],
                    ['like', 'first_name', $search],
                    ['like', 'last_name', $search]
                ];
                $users_store->andWhere($orCondition);
                $users_global->andWhere($orCondition);
            }

            // Limit results to 30 (15 each)
            $users_store = $users_store->limit(15)->asArray()->all();
            $users_global = $users_global->limit(15)->asArray()->all();

            $data['status'] = self::API_OK;
            $data['users']  = array_merge($users_store, $users_global);
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





    public function actionSupplierTransactionHistory()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get and validate POST data
            $post = Yii::$app->request->post();
            $supplier_id = $post['supplier_id'] ?? null;

            if (empty($supplier_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Supplier ID is required."));
            }

            // Pagination parameters
            $page     = (int)($post['page'] ?? 1);
            $pageSize = (int)($post['page_size'] ?? 10);

            // Query for count and data
            $query = Products::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'supplier_id' => $supplier_id]);

            $count = $query->count();

            $pagination = new \yii\data\Pagination([
                'totalCount' => $count,
                'pageSize'   => $pageSize,
                'page'       => $page - 1,
            ]);

            $products = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            $data['status'] = self::API_OK;
            $data['total_count'] = $count;
            $data['page'] = $page;
            $data['page_size'] = $pageSize;
            $data['products'] = $products; // You may want to serialize/format these

        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while adding product items.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }
    // public function actionAddOrUpdateWastage()
    // {
    //     $data = [
    //         'status' => self::API_OK,
    //         'message' => Yii::t("app", "All wastage products processed successfully."),
    //         'errors' => [],
    //         'success' => [],
    //     ];

    //     try {
    //         // 1. Authentication
    //         $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    //         $auth    = new AuthSettings();
    //         $user_id = $auth->getAuthSession($headers);

    //         if (empty($user_id)) {
    //             throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
    //         }

    //         $vendor_details_id = User::getVendorIdByUserId($user_id);
    //         if (empty($vendor_details_id)) {
    //             throw new UnauthorizedHttpException(Yii::t("app", "User is not associated with any vendor."));
    //         }

    //         $wastage_products_row = Yii::$app->request->getRawBody();
    //         $wastage_products = json_decode($wastage_products_row, true);

    //         if (empty($wastage_products) || !is_array($wastage_products)) {
    //             throw new BadRequestHttpException(Yii::t("app", "Invalid wastage products data."));
    //         }

    //         foreach ($wastage_products as $key => $product) {
    //             $product_id = $product['product_id'] ?? null;
    //             $uom_id = $product['uom_id'] ?? null;
    //             $quantity = $product['quantity'] ?? null;
    //             $batch_number = $product['batch_number'] ?? null;
    //             $wastage_type = $product['wastage_type'] ?? null;
    //             $reason_for_wastage = $product['reason_for_wastage'] ?? null;

    //             // Validate each field
    //             if (empty($product_id) || empty($uom_id) || empty($quantity) || empty($batch_number) || empty($wastage_type) || empty($reason_for_wastage)) {
    //                 $data['errors'][] = [
    //                     'index' => $key,
    //                     'message' => Yii::t("app", "All fields are required for each wastage product."),
    //                     'product' => $product,
    //                 ];
    //                 continue; // Skip this product, process the rest
    //             }




    //             $wastage = new WastageProducts();
    //             $wastage->vendor_details_id = $vendor_details_id;
    //             $wastage->product_id = $product_id;
    //             $wastage->uom_id = $uom_id;
    //             $wastage->quantity = $quantity;
    //             $wastage->batch_number = $batch_number;
    //             $wastage->wastage_type = $wastage_type;
    //             $wastage->reason_for_wastage = $reason_for_wastage;

    //             if (!$wastage->save(false)) {
    //                 $data['errors'][] = [
    //                     'index' => $key,
    //                     'message' => Yii::t("app", "Failed to save wastage product."),
    //                     'product' => $product,
    //                     'errors' => $wastage->getErrors(),
    //                 ];
    //             } else {
    //                 $data['success'][] = [
    //                     'index' => $key,
    //                     'product_id' => $product_id,
    //                 ];
    //             }
    //         }

    //         if (!empty($data['errors'])) {
    //             $data['status'] = self::API_NOK;
    //             $data['message'] = Yii::t("app", "Some wastage products could not be processed.");
    //         } elseif (empty($data['success'])) {
    //             // If nothing succeeded
    //             $data['status'] = self::API_NOK;
    //             $data['message'] = Yii::t("app", "No wastage products were processed successfully.");
    //         }

    //     } catch (\Throwable $e) {
    //         $data['status']     = self::API_NOK;
    //         $data['message']    = $e->getMessage();
    //         $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

    //         Yii::error([
    //             'message' => $e->getMessage(),
    //             'trace'   => $e->getTraceAsString(),
    //         ], __METHOD__);
    //     }

    //     return $this->sendJsonResponse($data);
    // }



    public function actionAddOrUpdateWastage()
    {
        $data = [
            'status' => self::API_OK,
            'message' => Yii::t("app", "All wastage products processed successfully."),
            'errors' => [],
            'success' => [],
        ];

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

            $wastage_products_row = Yii::$app->request->getRawBody();
            $wastage_products = json_decode($wastage_products_row, true);

            if (empty($wastage_products) || !is_array($wastage_products)) {
                throw new BadRequestHttpException(Yii::t("app", "Invalid wastage products data."));
            }

            foreach ($wastage_products as $key => $product) {
                $product_id = $product['product_id'] ?? null;
                $uom_id = $product['uom_id'] ?? null;
                $quantity = $product['quantity'] ?? null;
                $batch_number = $product['batch_number'] ?? null;
                $wastage_type = $product['wastage_type'] ?? null;
                $reason_for_wastage = $product['reason_for_wastage'] ?? null;

                // Validate each field
                if (empty($product_id) || empty($uom_id) || empty($quantity) || empty($batch_number) || empty($wastage_type) || empty($reason_for_wastage)) {
                    $data['errors'][] = [
                        'index' => $key,
                        'message' => Yii::t("app", "All fields are required for each wastage product."),
                        'product' => $product,
                    ];
                    continue; // Skip this product, process the rest
                }

                $Products = Products::findOne(['id' => $product_id, 'vendor_details_id' => $vendor_details_id]);
                // 🔹 Check available stock
                $availableStock = $Products->getAvailableStock();
                if ($availableStock < $quantity) {
                    $data['errors'][] = [
                        'index' => $key,
                        'message' => Yii::t("app", "Insufficient stock. Available: {available}, Requested wastage: {requested}", [
                            'available' => $availableStock,
                            'requested' => $quantity,
                        ]),
                        'product' => $product,
                    ];
                    continue; // Skip saving this wastage record
                }

                // Save wastage
                $wastage = new WastageProducts();
                $wastage->vendor_details_id = $vendor_details_id;
                $wastage->product_id = $product_id;
                $wastage->uom_id = $uom_id;
                $wastage->quantity = $quantity;
                $wastage->batch_number = $batch_number;
                $wastage->wastage_type = $wastage_type;
                $wastage->reason_for_wastage = $reason_for_wastage;

                if (!$wastage->save(false)) {
                    $data['errors'][] = [
                        'index' => $key,
                        'message' => Yii::t("app", "Failed to save wastage product."),
                        'product' => $product,
                        'errors' => $wastage->getErrors(),
                    ];
                } else {
                    $data['success'][] = [
                        'index' => $key,
                        'product_id' => $product_id,
                    ];
                }
            }

            if (!empty($data['errors'])) {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "Some wastage products could not be processed.");
            } elseif (empty($data['success'])) {
                // If nothing succeeded
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "No wastage products were processed successfully.");
            }
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['message']    = $e->getMessage();
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionWastageProductsList()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post = Yii::$app->request->post();
            $search = trim($post['search'] ?? '');
            $page = max(1, ($post['page'] ?? 1));
            $pageSize = max(1, min(100, ($post['per_page'] ?? 10))); // Limit max page size
            $product_id = $post['product_id'] ?? null;
            $wastage_type = $post['wastage_type'] ?? null;
            $date_from = $post['date_from'] ?? null;
            $date_to = $post['date_to'] ?? null;

            // 4. Build base query with filters
            $baseQuery = WastageProducts::find()
                ->joinWith(['product', 'product.sku'])
                ->where(['wastage_products.vendor_details_id' => $vendor_details_id]);

            // Apply filters to base query
            if (!empty($search)) {
                $baseQuery->andWhere([
                    'or',
                    ['like', 'sku.product_name', $search],
                    ['like', 'sku.sku_code', $search],
                    ['like', 'wastage_products.batch_number', $search],
                    ['like', 'wastage_products.reason_for_wastage', $search],
                ]);
            }

            if (!empty($product_id)) {
                $baseQuery->andWhere(['wastage_products.product_id' => $product_id]);
            }

            if (!empty($wastage_type)) {
                $baseQuery->andWhere(['wastage_products.wastage_type' => $wastage_type]);
            }

            if (!empty($date_from)) {
                $baseQuery->andWhere(['>=', 'DATE(wastage_products.created_on)', $date_from]);
            }

            if (!empty($date_to)) {
                $baseQuery->andWhere(['<=', 'DATE(wastage_products.created_on)', $date_to]);
            }

            // 5. Get total count for pagination
            $totalCount = clone $baseQuery;
            $totalCount = $totalCount->count();

            // 6. Calculate Track Wastage Summary from all filtered records
            $allWastageProducts = clone $baseQuery;
            $allWastageProducts = $allWastageProducts->all();

            $totalWastageValue = 0;
            $totalWastageQty = 0;
            $wastageTypeCount = [
                WastageProducts::WASTAGE_TYPE_DAMAGED => 0,
                WastageProducts::WASTAGE_TYPE_EXPIRED => 0,
                WastageProducts::WASTAGE_TYPE_OVERSTOCK => 0,
            ];
            $wastageTypeValue = [
                WastageProducts::WASTAGE_TYPE_DAMAGED => 0,
                WastageProducts::WASTAGE_TYPE_EXPIRED => 0,
                WastageProducts::WASTAGE_TYPE_OVERSTOCK => 0,
            ];





            // 7. Get paginated results for list
            $query = clone $baseQuery;
            $wastageProducts = $query
                ->orderBy(['wastage_products.id' => SORT_DESC])
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->all();

            // 8. Format response data
            $wastageList = [];
            foreach ($wastageProducts as $wastage) {
                $wastageData = $wastage->asJson();
                $wastageList[] = $wastageData;
            }

            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "Wastage products list fetched successfully."),
                'wastage_products' => $wastageList,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $pageSize,
                    'total' => (int)$totalCount,
                    'total_pages' => ceil($totalCount / $pageSize),
                ],
                'filters' => [
                    'search' => $search,
                    'product_id' => $product_id,
                    'wastage_type' => $wastage_type,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                ],
                'track_wastage' => [
                    'total_wastage_value' => round($totalWastageValue, 2),
                    'total_wastage_qty' => (int)$totalWastageQty,
                    'total_records' => (int)$totalCount,
                ]
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching wastage products list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionGetAllSku()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post     = Yii::$app->request->post();
            $search   = trim($post['search'] ?? '');


            // 4. Build query
            $query = Sku::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->orderBy(['id' => SORT_DESC]);

            // Apply filters
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'product_name', $search],
                    ['like', 'sku_code', $search],
                    ['like', 'ean_code', $search],
                    ['like', 'description', $search],
                ]);
            }






            // 5. Get total count for pagination
            $totalCount = $query->count();

            // 6. Get paginated results
            $skus = $query->all();

            // 7. Format response data
            $skuList = [];
            foreach ($skus as $sku) {
                $skuList[] = $sku->asJsonWithOutPagination();
            }

            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "SKU list fetched successfully."),
                'skus' => $skuList,
                'filters' => [
                    'search' => $search,

                ]
            ];
        } catch (\Throwable $e) {
            $data['status']     = self::API_NOK;
            $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching SKU list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }




    public function actionSelectBatchNumber()
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

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException(Yii::t("app", "Only POST requests are allowed."));
            }

            // 3. Get POST parameters
            $post = Yii::$app->request->post();
            $sku_id = $post['sku_id'] ?? null;
            $search = trim($post['search'] ?? '');
            $supplier_id = $post['supplier_id'] ?? null;

            // 4. Validate required parameters
            if (empty($sku_id)) {
                throw new BadRequestHttpException(Yii::t("app", "SKU ID is required."));
            }

            // 5. Verify SKU exists and belongs to vendor
            $sku = Sku::find()
                ->where([
                    'id' => $sku_id,
                    'vendor_details_id' => $vendor_details_id
                ])
                ->one();

            if (!$sku) {
                throw new NotFoundHttpException(Yii::t("app", "SKU not found or you don't have permission to view it."));
            }

            // 6. Build products query
            $query = Products::find()
                ->joinWith(['sku'])
                ->where([
                    'products.sku_id' => $sku_id,
                ])
                ->andWhere(['>=', 'products.expire_date', date('Y-m-d')])
                ->orderBy(['products.id' => SORT_DESC]);

            // Apply additional filters
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'products.batch_number', $search],
                    ['like', 'products.invoice_number', $search],
                    ['like', 'products.supplier_name', $search],
                ]);
            }

            if (!empty($supplier_id)) {
                $query->andWhere(['products.supplier_id' => $supplier_id]);
            }

            // 8. Get paginated results
            $products = $query->all();

            // 9. Format response data
            $productList = [];
            foreach ($products as $product) {
                $productData = $product->asJsonSelectBachNumber();
                $productList[] = $productData;
            }

            // Always return an API_OK status and products (even if empty)
            $data = [
                'status' => self::API_OK,
                'message' => Yii::t("app", "Product list fetched successfully for SKU: {sku_code}", ['sku_code' => $sku->sku_code]),
                'products' => $productList, // Will be empty array if no products found
                'filters' => [
                    'search' => $search
                ]
            ];
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching product list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        // Always return data, even if products array is empty
        return $this->sendJsonResponse($data);
    }




    public function actionExpiringProducts()
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
                throw new \yii\web\UnauthorizedHttpException(Yii::t('app', 'User is not associated with any vendor.'));
            }

            // 2. Validate request method
            if (!Yii::$app->request->isPost) {
                throw new \yii\web\MethodNotAllowedHttpException(Yii::t('app', 'Only POST requests are allowed.'));
            }

            // 3. Get POST parameters & sanitize pagination
            $post = Yii::$app->request->post();
            $search      = trim($post['search'] ?? '');
            $supplier_id = $post['supplier_id'] ?? null;
            $days        = !empty($post['days']) ? (int)$post['days'] : 10; // default 10 days

            $page        = !empty($post['page']) ? (int)$post['page'] : 1;
            $perPage     = !empty($post['per_page']) ? (int)$post['per_page'] : 10;

            // sanitize
            $page = $page < 1 ? 1 : $page;
            $perPage = $perPage < 1 ? 10 : $perPage;
            $maxPerPage = 100;
            $perPage = $perPage > $maxPerPage ? $maxPerPage : $perPage;

            // 4. Calculate date range
            $today  = date('Y-m-d');
            $toDate = date('Y-m-d', strtotime("+{$days} days"));

            // 5. Build base query
            $query = Products::find()
                ->joinWith(['sku']) // if sku relation exists
                ->where(['products.vendor_details_id' => $vendor_details_id])
                ->andWhere(['>=', 'products.expire_date', $today])
                ->andWhere(['<=', 'products.expire_date', $toDate]);

            // Additional filters
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'products.batch_number', $search],
                    ['like', 'products.invoice_number', $search],
                ]);
            }

            if (!empty($supplier_id)) {
                $query->andWhere(['products.supplier_id' => $supplier_id]);
            }

            // Ordering
            $query->orderBy(['products.expire_date' => SORT_ASC]);

            // 6. Pagination: count + fetch page
            $total = (int) (clone $query)->count();

            $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
            $offset = ($page - 1) * $perPage;

            $products = $query->offset($offset)->limit($perPage)->all();

            // 7. Format response
            $productList = [];
            foreach ($products as $product) {
                $productList[] = method_exists($product, 'asJson') ? $product->asJson() : $product->toArray();
            }

            $data = [
                'status' => self::API_OK,
                'message' => !empty($productList)
                    ? Yii::t('app', 'Expiring products fetched successfully.')
                    : Yii::t('app', 'No expiring products found.'),
                'products' => $productList,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                ],
                'filters' => [
                    'search' => $search,
                    'supplier_id' => $supplier_id,
                    'days' => $days,
                ],
            ];
        } catch (\yii\web\HttpException $e) {
            // client-safe message, but return exception message (you can keep it simple if you prefer)
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['error_code'] = $e->statusCode ?? 400;

            Yii::warning([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = Yii::t('app', 'An unexpected error occurred while fetching product list.');
            $data['error_code'] = 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionWastageTypes()
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
            $waste_types = WasteTypes::find()
                ->all();
            if (!empty($waste_types)) {
                $data['status'] = self::API_OK;
                $data['message'] = Yii::t("app", "Wastage types fetched successfully.");
                $data['wastage_types'] = $waste_types;
            } else {
                $data['status'] = self::API_NOK;
                $data['message'] = Yii::t("app", "No wastage types found.");
            }
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching product list.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        // Always return data, even if products array is empty
        return $this->sendJsonResponse($data);
    }
}
