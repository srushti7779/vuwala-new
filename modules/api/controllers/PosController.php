<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\modules\admin\models\GuestUserDeposits;
use app\modules\admin\models\MemberShips;
use app\modules\admin\models\OrderDetails;
use app\modules\admin\models\OrderDiscounts;
use app\modules\admin\models\Orders;
use app\modules\admin\models\OrderTransactionDetails;
use app\modules\admin\models\ProductOrderItems;
use app\modules\admin\models\ProductOrders;
use app\modules\admin\models\Products;
use app\modules\admin\models\ProductServiceOrderMappings;
use app\modules\admin\models\Services;
use app\modules\admin\models\Sku;
use app\modules\admin\models\StoresHasUsers;
use app\modules\admin\models\StoresUsersMemberships;
use app\modules\admin\models\Units;
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

class PosController extends BKController
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
                            'products-with-sku-list',
                            'add-product-items-to-order',
                            'view-order-by-id',
                            'view-product-order-by-id',
                            'remove-product-items-from-order',
                            'select-batch-number',
                            'search-customers',
                            'add-new-service-for-existing-order',
                            'complete-payment',
                            'add-tip',
                            'apply-discount',
                            'delete-order-services',
                            'hold-order',
                            'un-hold-order',


                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'products-with-sku-list',
                            'add-product-items-to-order',
                            'view-order-by-id',
                            'view-product-order-by-id',
                            'remove-product-items-from-order',
                            'select-batch-number',
                            'search-customers',
                            'add-new-service-for-existing-order',
                            'complete-payment',
                            'add-tip',
                            'apply-discount',
                            'delete-order-services',
                            'hold-order',
                            'un-hold-order',

                        ],
                        'allow'   => true,
                        'roles'   => ['?', '*'],
                    ], 
                ],
            ],
        ]);
    }






    public function actionProductsWithSkuList()
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



    public function actionAddProductItemsToOrder()
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

        // 3. Get and validate POST data
        $post = Yii::$app->request->getRawBody();
        $post = json_decode($post, true);
        $user_id_for_order = $post['user_id'] ?? null;
        $items = $post['items'] ?? [];
        $service_order_id = $post['service_order_id'] ?? null;
        $tax_percentage = 0;

        if (empty($user_id_for_order)) {
            throw new BadRequestHttpException(Yii::t("app", "User ID is required for creating an order."));
        }

        if (!is_array($items) || empty($items)) {
            throw new BadRequestHttpException(Yii::t("app", "Items array is required and cannot be empty."));
        }


        // 4. Validate each item has required fields
        foreach ($items as $index => $item) {
            if (empty($item['sku_id']) || empty($item['quantity']) || !isset($item['batch_number'])) {
                throw new BadRequestHttpException(Yii::t("app", "Item at index {index} is missing required fields: sku_id, quantity, batch_number", ['index' => $index]));
            }

            // Verify product belongs to vendor
            $product = Products::find()
                ->joinWith(['sku'])
                ->where([
                    'products.sku_id' => $item['sku_id'],
                    'sku.vendor_details_id' => $vendor_details_id,
                    'products.batch_number' => $item['batch_number'],
                ])
                ->one();

            if (!$product) {
                throw new BadRequestHttpException(Yii::t("app", "Product with SKU {sku_id} not found or does not belong to your store.", ['sku_id' => $item['sku_id']]));
            }
        }



        // 6. Start transaction
        // $transaction = Yii::$app->db->beginTransaction();

        // 7. Create main product order
        $product_orders = new ProductOrders();
        $product_orders->vendor_details_id = $vendor_details_id;
        $product_orders->user_id = $user_id_for_order;
        $product_orders->sub_total = $totals['sub_total'] ?? 0;
        $product_orders->tax_percentage = $totals['tax_percentage'] ?? 0;
        $product_orders->tax_amount = $totals['tax_amount'] ?? 0;
        $product_orders->total_with_tax = $totals['total_with_tax'] ?? 0;
        $product_orders->payment_status = ProductOrders::STATUS_PENDING;
        $product_orders->current_status = ProductOrders::CURRENT_STATUS_ACTIVE;
        $product_orders->status = ProductOrders::STATUS_PENDING;

        if (!$product_orders->save(false)) {
            throw new ServerErrorHttpException(Yii::t("app", "Failed to create product order."));
        }
        if (!empty($service_order_id)) {
            $product_service_order_mappings = new ProductServiceOrderMappings();
            $product_service_order_mappings->product_order_id = $product_orders->id;
            $product_service_order_mappings->order_id = $service_order_id;
            $product_service_order_mappings->status = ProductServiceOrderMappings::STATUS_ACTIVE;
            $product_service_order_mappings->save(false);
        }

        // return $product_orders;

        // 8. Process each item and create order items
        $order_items = [];
        foreach ($items as $item) {
            $product = Products::find()
                ->joinWith(['sku'])
                ->where(['products.id' => $product->id])
                ->one();

            $unit_id = $product->received_units_id;
            $units = $unit_id ? Units::findOne($unit_id) : null;

            $product_order_items = new ProductOrderItems();
            $product_order_items->product_order_id = $product_orders->id;
            $product_order_items->product_id = $product->id;
            $product_order_items->quantity = $item['quantity'];
            $product_order_items->units = $units ? $units->unit_name : 'Unit';
            $product_order_items->mrp_price = $product->mrp_price;
            $product_order_items->selling_price = $product->selling_price;
            $product_order_items->sub_total = $item['quantity'] * $product->selling_price;
            $product_order_items->tax_percentage = $tax_percentage;
            $product_order_items->tax_amount = ($product_order_items->sub_total * $tax_percentage) / 100;
            $product_order_items->total_with_tax = $product_order_items->sub_total + $product_order_items->tax_amount;
            $product_order_items->status = 1; // Active

            if (!$product_order_items->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to save order item for product ID {product_id}.", ['product_id' => $item['product_id']]));
            }

            $order_items[] = $product_order_items->asJson();

            // 9. Update product stock (reduce units_received)
            $product->units_received -= $item['quantity'];
            if (!$product->save(false)) {
                throw new ServerErrorHttpException(Yii::t("app", "Failed to update stock for product ID {product_id}.", ['product_id' => $item['product_id']]));
            }
        }

        $totals = self::calculateOrderTotals($items, $tax_percentage);

        // $transaction->commit();

        $data['status'] = self::API_OK;
        $data['message'] = Yii::t("app", "Product order created successfully.");
        $data['order'] = [
            'order_id' => $product_orders->id,
            'user_id' => $user_id_for_order,
            'totals' => $totals,
            'items_count' => count($items),
            'order_items' => $order_items
        ];
        // } catch (\Throwable $e) {
        //     if (!empty($transaction) && $transaction->getIsActive()) {
        //         $transaction->rollBack();
        //     }

        //     $data['status']     = self::API_NOK;
        //     $data['error']      = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while adding product items.");
        //     $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

        //     Yii::error([
        //         'message' => $e->getMessage(),
        //         'trace'   => $e->getTraceAsString(),
        //     ], __METHOD__);
        // }

        return $this->sendJsonResponse($data);
    }


    public static function calculateOrderTotals($items, $tax_percentage = 0)
    {
        $sub_total = 0;

        foreach ($items as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);
            $sub_total += $quantity * $price;
        }

        $tax_amount = ($sub_total * $tax_percentage) / 100;
        $total_with_tax = $sub_total + $tax_amount;

        return [
            'sub_total' => round($sub_total, 2),
            'tax_percentage' => $tax_percentage,
            'tax_amount' => round($tax_amount, 2),
            'total_with_tax' => round($total_with_tax, 2)
        ];
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


    public function actionViewProductOrderById()
    {
        $data = [];
        $post = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $product_order_id = ! empty($post['product_order_id']) ? $post['product_order_id'] : '';

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


            $product_order = ProductOrders::find()
                ->where(['vendor_details_id' => $vendor_details_id, 'id' => $product_order_id])
                ->one();

            if (empty($product_order)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "Product order not found.");
                return $this->sendJsonResponse($data);
            }

            $data['status']  = self::API_OK;
            $data['details'] = $product_order->asJson();

            return $this->sendJsonResponse($data);
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


    public function actionSearchCustomers()
    {
        $data = [
            'status' => self::API_OK,
            'message' => '',
            'store_clients' => [],
            'global_users' => [],
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

            $post = Yii::$app->request->post();
            $search = trim($post['search'] ?? '');

            $storesHasUsers = StoresHasUsers::find()->select('guest_user_id')->where(['vendor_details_id' => $vendor_details_id])->all();

            // --- Store Clients Query ---
            $storeClientsQuery = User::find()
                ->where(['user.user_role' => User::ROLE_GUEST])
                ->andWhere(['user.status' => User::STATUS_ACTIVE]);
            $storeClientsQuery->andWhere(['in', 'user.id', array_map(function ($shu) {
                return $shu->guest_user_id;
            }, $storesHasUsers)]);
            $storeClientsQuery->andWhere([
                'or',
                ['like', 'user.username', $search],
                ['like', 'user.first_name', $search],
                ['like', 'user.email', $search],
                ['like', 'user.contact_no', $search],
            ]);
            $store_clients = $storeClientsQuery->all();
            //return raw query

            // --- Global Users Query ---
            $globalUsersQuery = User::find()->where(['user.user_role' => User::ROLE_USER]);
            $globalUsersQuery->andWhere([
                'or',
                ['like', 'user.username', $search],
                ['like', 'user.first_name', $search],
                ['like', 'user.email', $search],
                ['like', 'user.contact_no', $search],
            ]);
            $global_users = $globalUsersQuery->all();

            // Format results
            $data['store_clients'] = array_map(function ($user, $vendor_details_id) {
                return $user->asJsonUserClient($vendor_details_id);
            }, $store_clients, array_fill(0, count($store_clients), $vendor_details_id));

            $data['global_users'] = array_map(function ($user) {
                return $user->asJsonUserClient();
            }, $global_users);

            $data['message'] = Yii::t("app", "Customers fetched successfully.");
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e instanceof HttpException ? $e->getMessage() : Yii::t("app", "An unexpected error occurred while fetching customers.");
            $data['error_code'] = $e instanceof HttpException ? $e->statusCode : 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionCompletePayment()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = [];

        try {
            // --- AUTH ---
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            if (empty($user_id)) {
                throw new \yii\web\UnauthorizedHttpException('User authentication failed. Please log in.');
            }

            // --- INPUTS ---
            $post = Yii::$app->request->post();
            $order_id = $post['order_id'] ?? null;
            $product_order_id = $post['product_order_id'] ?? null;
            $pay_with_deposit = $post['pay_with_deposit'] ?? null;
            $payment_type = $post['payment_type'] ?? OrderTransactionDetails::PAYMENT_SOURCE_COD;
            if (empty($order_id) && empty($product_order_id)) {
                throw new \yii\web\BadRequestHttpException('Either Order ID or Product Order ID is required.');
            }

            // --- VENDOR ---
            $vendorDetails = VendorDetails::getVendorDetailsByUserId($user_id);
            if (!$vendorDetails) {
                throw new \yii\web\NotFoundHttpException('Vendor details not found for this user.');
            }
            $vendor_details_id = $vendorDetails->id;

            // --- ORDER LOAD ---
            $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $vendor_details_id]);
            $product_orders = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $vendor_details_id]);
            if (!$order && !$product_orders) {
                throw new \yii\web\NotFoundHttpException('Order or Product Order not found or not accessible.');
            }

            // --- CUSTOMER DETECTION ---
            $customer_user_id = $order->user_id ?? $product_orders->user_id ?? null;
            if (!$customer_user_id) {
                throw new \yii\web\ServerErrorHttpException('Customer information is missing for this order.');
            }
            $customer_user = User::findOne(['id' => $customer_user_id]);
            if (!$customer_user) {
                throw new \yii\web\NotFoundHttpException('Customer user not found.');
            }

            // --- BUSINESS RULE: Restrict COD ---
            if ($customer_user->user_role == User::ROLE_USER && $payment_type == OrderTransactionDetails::PAYMENT_SOURCE_COD) {
                throw new \yii\web\ForbiddenHttpException('Cash on Delivery (COD) is not allowed for registered users. Please select an online payment method.');
            }

            // --- RECALCULATE AMOUNTS ---
            Orders::recalculateOrderPrice($order_id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            Orders::recalculateOrderPrice($product_order_id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);
            if ($order) $order->refresh();
            if ($product_orders) $product_orders->refresh();

            // --- Prevent overpayment ---
            $checkPendingAmountServesOrder = OrderTransactionDetails::checkPendingAmount($order_id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
            $checkPendingAmountProductOrder = OrderTransactionDetails::checkPendingAmount($product_order_id, OrderTransactionDetails::ORDER_TYPE_PRODUCT_ORDER);
            $checkPendingAmount = $checkPendingAmountServesOrder + $checkPendingAmountProductOrder;

            if ($payment_type == OrderTransactionDetails::PAYMENT_TYPE_ONLINE && $checkPendingAmount < 0) {
                throw new \yii\web\BadRequestHttpException('Cannot process payment: Overpayment detected. Pending amount: ' . $checkPendingAmount);
            }

            // --- Handle deposits ---
            if (!empty($pay_with_deposit)) {
                $store_has_user_id = User::getStoreHasUserId($order->user_id ?? $product_orders->user_id, $vendor_details_id);
                $getAvailableDepositBalance = GuestUserDeposits::getAvailableDepositBalance($customer_user_id, $store_has_user_id);
                if ($getAvailableDepositBalance <= 0) {
                    $data['status'] = self::API_NOK;
                    $data['error']  = 'Insufficient deposit balance to complete this payment.';
                    return $this->sendJsonResponse($data);
                }
                // if checkPendingAmount and getAvailableDepositBalance is greater than or equal then deduct full amount from deposit
                if ($checkPendingAmount > $getAvailableDepositBalance) {
                    $amount = $getAvailableDepositBalance;
                } else {
                    $amount = $checkPendingAmount;
                }

                $guest_user_deposits = new GuestUserDeposits();
                $guest_user_deposits->guest_user_id     = $customer_user_id;
                $guest_user_deposits->store_has_user_id = $store_has_user_id;
                $guest_user_deposits->order_id          = json_encode(['order_id' => $order->id ?? null, 'product_order_id' => $product_order_id]);
                $guest_user_deposits->payment_type      = GuestUserDeposits::PAYMENT_TYPE_DEBIT;
                $guest_user_deposits->amount            = $amount;
                $guest_user_deposits->date_and_time     = date('Y-m-d H:i:s');
                $guest_user_deposits->save(false);
            }

            // --- TRANSACTION RECORD ---
            $order_transaction_details = new OrderTransactionDetails();
            $order_transaction_details->order_id         = $order->id ?? null;
            $order_transaction_details->product_order_id = $product_orders->id ?? null;
            $order_transaction_details->user_id          = $customer_user_id;
            $order_transaction_details->amount           = $checkPendingAmount;
            $order_transaction_details->payment_type     = $payment_type;
            $order_transaction_details->payment_source   = OrderTransactionDetails::PAYMENT_SOURCE_COD;
            $order_transaction_details->status           = OrderTransactionDetails::STATUS_SUCCESS;
            $order_transaction_details->save(false);
            Orders::recalculateOrderPrice($order_id, $product_order_id);

            //get product order
            $product_orders = ProductOrders::find()->where(['id' => $product_order_id])->andWhere(['vendor_details_id' => $vendor_details_id])->one();
            if ($product_orders) {
                $product_orders->refresh();
                if ($product_orders->status == ProductOrders::CURRENT_STATUS_COMPLETED) {
                    $product_orders->current_status = ProductOrders::CURRENT_STATUS_COMPLETED;
                    $product_orders->completed_on = date('Y-m-d H:i:s');
                    $product_orders->save(false);
                }
            }
            //get service order
            $order = Orders::find()->where(['id' => $order_id])->andWhere(['vendor_details_id' => $vendor_details_id])->one();
            if ($order) {
                $order->refresh();
                if ($order->status == Orders::STATUS_CANCELLED) {
                    $order->current_status = Orders::CURRENT_STATUS_COMPLETED;
                    $order->completed = date('Y-m-d H:i:s');
                    $order->save(false);
                }
            }

            // --- SUCCESS ---
            $data['status']         = self::API_OK;
            $data['message']        = 'Order marked as paid successfully.';
            $data['transaction_id'] = $order_transaction_details->id;
            Yii::info("Payment completed successfully. Transaction ID: {$order_transaction_details->id}", __METHOD__);

            return $this->sendJsonResponse($data);
        } catch (\yii\web\UnauthorizedHttpException $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 401;
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 400;
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\ForbiddenHttpException $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 403;
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\NotFoundHttpException $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 404;
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\yii\web\HttpException $e) {
            // Handles all other HTTP exceptions (e.g., 500)
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = $e->statusCode ?? 500;
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Throwable $e) {
            // Fallback for any unexpected error
            Yii::error('Unexpected error: ' . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            $data['status'] = self::API_NOK;
            $data['error']  = 'An unexpected error occurred while processing payment.';
        }

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

    public function actionAddTip()
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
            $order_id   = $post['order_id'] ?? null;
            $tip_amt = $post['tip_amount'] ?? 0;

            if (empty($order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid order ID is required."));
            }
            if (empty($tip_amt)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid tip amount is required."));
            }

            $order = Orders::findOne(['id' => $order_id, 'vendor_details_id' => $getVendorIdByUser]);
            if (! $order) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or you don't have permission to add services to this order."));
            }

            $order->tip_amt = $tip_amt;
            $order->save(false);

            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Tip added successfully.");
            $data['details'] = $order->asJson();
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

    public function actionRemoveProductItemsFromOrder()
    {
        $data        = [];
        $transaction = Yii::$app->db->beginTransaction();

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
            $post                 = Yii::$app->request->post();
            $product_order_item_id = $post['product_order_item_id'] ?? null;
            $product_order_id      = $post['product_order_id'] ?? null;

            if (empty($product_order_item_id) || empty($product_order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid product order item ID and order ID are required."));
            }

            // 4. Find Product Order Item
            $product_order_item = ProductOrderItems::find()->joinWith(['productOrder'])->where(['id' => $product_order_item_id])
                ->andWhere(['product_orders.id' => $product_order_id, 'product_orders.vendor_details_id' => $getVendorIdByUser])
                ->one();

            if (!$product_order_item) {
                throw new NotFoundHttpException(Yii::t("app", "Product order item not found."));
            }

            // 5. Delete the product order item
            if (! $product_order_item->delete()) {
                throw new \RuntimeException(Yii::t("app", "Failed to delete product order item."));
            }

            // 6. Recalculate product order totals
            Orders::recalculateProductOrders($product_order_id);

            // 7. Check if product order still has items
            $product_order = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $getVendorIdByUser]);

            if ($product_order) {
                $remainingItems = ProductOrderItems::find()->where(['product_order_id' => $product_order_id])->count();
                if ($remainingItems == 0) {
                    if (! $product_order->delete()) {
                        throw new \RuntimeException(Yii::t("app", "Failed to delete empty product order."));
                    }
                }
            }

            // Commit transaction
            $transaction->commit();

            // Success response
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Product order item removed successfully.");
        } catch (\yii\web\HttpException $e) {
            // Rollback transaction if active
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            // Known HTTP error
            $data['status']     = self::API_NOK;
            $data['error']      = $e->getMessage();
            $data['error_code'] = $e->statusCode;

            Yii::warning(['error' => $e->getMessage()], __METHOD__);
        } catch (\Throwable $e) {
            // Rollback transaction if active
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            // Unexpected error
            $data['status']     = self::API_NOK;
            $data['error']      = Yii::t("app", "An unexpected error occurred while removing product items.");
            $data['error_code'] = 500;

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }



    public function actionApplyDiscount()
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
            $product_order_id      = $post['product_order_id'] ?? null;
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


    public function actionDeleteOrderServices()
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
            if (!$orders) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found or you don't have permission to add services to this order."));
            }

            $services = Services::findOne(['id' => $service_id, 'vendor_details_id' => $getVendorIdByUser]);
            if (!$services) {
                throw new NotFoundHttpException(Yii::t("app", "Service not found or you don't have permission to add this service."));
            }

            $order_details = OrderDetails::find()
                ->where(['order_id' => $order_id])
                ->andWhere(['service_id' => $service_id])
                ->one();

            if ($order_details) {
                if ($order_details->delete_allowed == 1) {
                    $order_details->delete();
                    $orders->refresh();
                    Orders::recalculateOrderPrice($order_id, OrderTransactionDetails::ORDER_TYPE_SERVICE_ORDER);
                    $orders->refresh();

                    $data['status']  = self::API_OK;
                    $data['message'] = Yii::t("app", "Service removed successfully.");
                } else {
                    throw new BadRequestHttpException(Yii::t("app", "Deletion of this service is not allowed."));
                }
            } else {
                throw new NotFoundHttpException(Yii::t("app", "Service not found in this order."));
            }
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error'] = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error'] = Yii::t("app", "An unexpected error occurred while deleting the service from order.");
                $data['error_code'] = 500;
            }

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionHoldOrder()
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

            // 3. Get and validate input parameters
            $post       = Yii::$app->request->post();
            $product_order_id = $post['product_order_id'] ?? null;
            $service_order_id = $post['service_order_id'] ?? null;
            //required min one product or service order id
            if (empty($product_order_id) && empty($service_order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid product order ID or service order ID is required."));
            }
            if (!empty($product_order_id)) {
                $product_orders = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                if (!$product_orders) {
                    throw new NotFoundHttpException(Yii::t("app", "Product Order not found or you don't have permission to hold this order."));
                }
                $product_orders->current_status = ProductOrders::CURRENT_STATUS_HOLD;
                $product_orders->save(false);
                //check product_service_order_mappings
                $product_service_order_mapping = ProductServiceOrderMappings::find()->where(['product_order_id ' => $product_order_id])->one();
                $service_order_id = $product_service_order_mapping->order_id  ?? null;
                $product_order_id  = $product_service_order_mapping->product_order_id  ?? null;
                //if service order id is not empty then update service order status to hold
                if (!empty($service_order_id)) {
                    $service_orders = Orders::findOne(['id' => $service_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                    if ($service_orders) {
                        $service_orders->current_status = Orders::CURRENT_STATUS_HOLD;
                        $service_orders->save(false);
                    }
                }
            }
            if (!empty($service_order_id)) {
                $service_orders = Orders::findOne(['id' => $service_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                if (!$service_orders) {
                    throw new NotFoundHttpException(Yii::t("app", "Service Order not found or you don't have permission to hold this order."));
                }
                $service_orders->current_status = Orders::CURRENT_STATUS_HOLD;
                $service_orders->save(false);
                //check product_service_order_mappings
                $product_service_order_mapping = ProductServiceOrderMappings::find()->where(['order_id ' => $service_order_id])->one();
                $service_order_id = $product_service_order_mapping->order_id  ?? null;
                $product_order_id  = $product_service_order_mapping->product_order_id  ?? null;
                //if product order id is not empty then update product order status to hold
                if (!empty($product_order_id)) {
                    $product_orders = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                    if ($product_orders) {
                        $product_orders->current_status = ProductOrders::CURRENT_STATUS_HOLD;
                        $product_orders->save(false);
                    }
                }
            }
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order held successfully.");
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error'] = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error'] = Yii::t("app", "An unexpected error occurred while deleting the service from order.");
                $data['error_code'] = 500;
            }

            Yii::error([
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }

    public function actionUnHoldOrder()
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

            // 3. Get and validate input parameters
            $post       = Yii::$app->request->post();
            $product_order_id = $post['product_order_id'] ?? null;
            $service_order_id = $post['service_order_id'] ?? null;
            //required min one product or service order id
            if (empty($product_order_id) && empty($service_order_id)) {
                throw new BadRequestHttpException(Yii::t("app", "Valid product order ID or service order ID is required."));
            }
            if (!empty($product_order_id)) {
                $product_orders = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                if (!$product_orders) {
                    throw new NotFoundHttpException(Yii::t("app", "Product Order not found or you don't have permission to hold this order."));
                }
                $product_orders->current_status = ProductOrders::CURRENT_STATUS_ACTIVE;
                $product_orders->save(false);
                //check product_service_order_mappings
                $product_service_order_mapping = ProductServiceOrderMappings::find()->where(['product_order_id ' => $product_order_id])->one();
                $service_order_id = $product_service_order_mapping->order_id  ?? null;
                $product_order_id  = $product_service_order_mapping->product_order_id  ?? null;
                //if service order id is not empty then update service order status to hold
                if (!empty($service_order_id)) {
                    $service_orders = Orders::findOne(['id' => $service_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                    if ($service_orders) {
                        $service_orders->current_status = Orders::CURRENT_STATUS_ACTIVE;
                        $service_orders->save(false);
                    }
                }
            }
            if (!empty($service_order_id)) {
                $service_orders = Orders::findOne(['id' => $service_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                if (!$service_orders) {
                    throw new NotFoundHttpException(Yii::t("app", "Service Order not found or you don't have permission to hold this order."));
                }
                $service_orders->current_status = Orders::CURRENT_STATUS_ACTIVE;
                $service_orders->save(false);
                //check product_service_order_mappings
                $product_service_order_mapping = ProductServiceOrderMappings::find()->where(['order_id ' => $service_order_id])->one();
                $service_order_id = $product_service_order_mapping->order_id  ?? null;
                $product_order_id  = $product_service_order_mapping->product_order_id  ?? null;
                //if product order id is not empty then update product order status to hold
                if (!empty($product_order_id)) {
                    $product_orders = ProductOrders::findOne(['id' => $product_order_id, 'vendor_details_id' => $getVendorIdByUser]);
                    if ($product_orders) {
                        $product_orders->current_status = ProductOrders::CURRENT_STATUS_ACTIVE;
                        $product_orders->save(false);
                    }
                }
            }
            $data['status']  = self::API_OK;
            $data['message'] = Yii::t("app", "Order held successfully.");
        } catch (\Throwable $e) {
            if ($transaction && $transaction->getIsActive()) {
                $transaction->rollBack();
            }

            $data['status'] = self::API_NOK;
            if ($e instanceof \yii\web\HttpException) {
                $data['error'] = $e->getMessage();
                $data['error_code'] = $e->statusCode;
            } else {
                $data['error'] = Yii::t("app", "An unexpected error occurred while deleting the service from order.");
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
