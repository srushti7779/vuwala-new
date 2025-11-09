<?php

namespace app\modules\api\controllers;

use app\modules\api\controllers\BKController;
use yii;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\components\AuthSettings;
use app\modules\admin\models\base\ProductOrders;
use app\modules\admin\models\VendorDetails;
use Exception;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Tcpdf;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class OrdersController extends BKController
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
                            'pos-order-dashboard',
                            'pos-order-list',
                            'pos-order-view',
                            'download-invoice'








                        ],

                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ],
                    [

                        'actions' => [
                            'pos-order-dashboard',
                            'pos-order-list',
                            'pos-order-view',
                            'download-invoice'















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



    public function actionPosOrderDashboard()
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
            if (!$shop) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'No shop details found for this user.';
                $data['message'] = Yii::t("app", "No shop details found for this user.");
                return $this->sendJsonResponse($data);
            }

            // Get date filters (optional)
            $start_date = Yii::$app->request->post('start_date');
            $end_date   = Yii::$app->request->post('end_date');

            // Base query
            $query = ProductOrders::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['status' => ProductOrders::STATUS_COMPLETED]);

            // Apply date filter only if provided
            if (!empty($start_date) && !empty($end_date)) {
                $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
                $end_date   = date('Y-m-d 23:59:59', strtotime($end_date));
                $query->andWhere(['between', 'created_on', $start_date, $end_date]);
            }

            // Dashboard Metrics
            $dashboard = [];

            // Total orders and value
            $dashboard['total_orders'] = (clone $query)->count();
            $dashboard['total_value']  = (clone $query)->sum('total_with_tax') ?? 0;

            // Orders grouped by status
            $statusCounts = (clone $query)
                ->select(['status', 'COUNT(*) AS count', 'SUM(total_with_tax) AS total'])
                ->groupBy('status')
                ->asArray()
                ->all();

            $dashboard['by_status'] = [];
            foreach ($statusCounts as $row) {
                $dashboard['by_status'][] = [
                    'status' => $row['status'],
                    'count'  => (int)$row['count'],
                    'total'  => (float)$row['total'],
                ];
            }

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


    public function actionPosOrderList()
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
            if (!$shop) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'No shop details found for this user.';
                $data['message'] = Yii::t("app", "No shop details found for this user.");
                return $this->sendJsonResponse($data);
            }

            $vendor_details_id = $shop->id;

            // Inputs
            $start_date = Yii::$app->request->post('start_date');
            $end_date   = Yii::$app->request->post('end_date');
            $search     = Yii::$app->request->post('search');
            $page       = (int)Yii::$app->request->post('page', 1);
            $perPage    = (int)Yii::$app->request->post('per_page', 20);

            // sanitize pagination
            if ($page < 1) $page = 1;
            $perPage = max(1, min(100, $perPage)); // 1..100

            // Base query
            $query = ProductOrders::find()
                ->alias('po')
                ->joinWith(['user u'])
                ->where(['po.vendor_details_id' => $vendor_details_id]);

            // Date filters (optional)
            if (!empty($start_date)) {
                $start = date('Y-m-d 00:00:00', strtotime($start_date));
                $query->andWhere(['>=', 'po.created_on', $start]);
            }
            if (!empty($end_date)) {
                $end = date('Y-m-d 23:59:59', strtotime($end_date));
                $query->andWhere(['<=', 'po.created_on', $end]);
            }

            // Search (optional) — group OR conditions to avoid messing with previous where()
            if (!empty($search)) {
                $query->andWhere([
                    'or',
                    ['like', 'po.id', $search],
                    ['like', 'u.first_name', $search],
                    ['like', 'u.last_name', $search],
                    ['like', 'u.contact_no', $search],
                ]);
            }

            // total count
            $totalCount = (clone $query)->count();

            // sorting & pagination
            $query->orderBy(['po.created_on' => SORT_DESC]);
            $offset = ($page - 1) * $perPage;
            $models = $query->offset($offset)->limit($perPage)->all();

            // map models to arrays (adjust fields you want to expose)
            $items = [];
            foreach ($models as $m) {
                $items[] = $m->asJsonList();
            }

            $totalPages = (int)ceil($totalCount / $perPage);

            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Orders fetched successfully.");
            $data['details'] = [
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_count' => (int)$totalCount,
                    'total_pages' => $totalPages,
                ],
                'details' => $items,
            ];
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
        }

        return $this->sendJsonResponse($data);
    }


    public function actionPosOrderView()
    {
        $data = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $id = Yii::$app->request->post('id');

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
            }
            if (empty($id) || !is_numeric($id) || $id <= 0) {
                throw new BadRequestHttpException(Yii::t("app", "A valid order ID is required."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (!$shop) {
                $data['status'] = self::API_NOK;
                $data['error'] = 'No shop details found for this user.';
                $data['message'] = Yii::t("app", "No shop details found for this user.");
                return $this->sendJsonResponse($data);
            }

            $vendor_details_id = $shop->id;

            // Fetch order ensuring it belongs to this vendor
            $order = ProductOrders::find()
                ->where(['id' => $id, 'vendor_details_id' => $vendor_details_id])
                ->one();

            if (!$order) {
                throw new NotFoundHttpException(Yii::t("app", "Order not found."));
            }

            // Prepare response data
            $data['status'] = self::API_OK;
            $data['message'] = Yii::t("app", "Order details fetched successfully.");
            $data['details'] = $order->asJsonView();

        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "Authentication failed.");
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "The requested order does not exist.");
        } catch (\Throwable $e) {
            $data['status'] = self::API_NOK;
            $data['error'] = $e->getMessage();
            $data['message'] = Yii::t("app", "An unexpected error occurred.");
            Yii::error([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
            Yii::$app->response->statusCode = 500;

        }
        return $this->sendJsonResponse($data);
    }


public function actionDownloadInvoice()
{
    $data = [];
    $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
    $auth = new AuthSettings();
    $user_id = $auth->getAuthSession($headers);
    $id = Yii::$app->request->post('id');

    try {
        // 1️⃣ Authentication
        if (empty($user_id)) {
            throw new UnauthorizedHttpException(Yii::t("app", "User not authorized."));
        }

        // 2️⃣ Validate order ID
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            throw new BadRequestHttpException(Yii::t("app", "A valid order ID is required."));
        }

        // 3️⃣ Fetch vendor/shop details
        $shop = VendorDetails::findOne(['user_id' => $user_id]);
        if (!$shop) {
            return $this->sendJsonResponse([
                'status' => self::API_NOK,
                'error' => 'No shop details found for this user.',
                'message' => Yii::t("app", "No shop details found for this user."),
            ]);
        }

        $vendor_details_id = $shop->id;

        // 4️⃣ Fetch order with product items
        $order = ProductOrders::find()
            ->with(['productOrderItems' => function ($query) {
                $query->select([
                    'product_order_id',
                    'product_id',
                    'quantity',
                    'units',
                    'selling_price',
                    'tax_percentage',
                ])->asArray();
            }])
            ->where(['id' => $id, 'vendor_details_id' => $vendor_details_id])
            ->asArray()
            ->one();

        if (!$order) {
            throw new NotFoundHttpException(Yii::t("app", "Order not found."));
        }

        // Debug the order data
        \Yii::debug($order, 'OrderData');

        // 5️⃣ Calculate item totals
        foreach ($order['productOrderItems'] as &$item) {
            $item['sub_total'] = $item['selling_price'] * $item['quantity'];
            $item['tax_amount'] = ($item['sub_total'] * $item['tax_percentage']) / 100;
            $item['total_with_tax'] = $item['sub_total'] + $item['tax_amount'];
        }
        unset($item);

        // 6️⃣ Calculate overall totals
        $order['sub_total'] = array_sum(array_column($order['productOrderItems'], 'sub_total'));
        $order['tax_amount'] = array_sum(array_column($order['productOrderItems'], 'tax_amount'));
        $order['total_with_tax'] = array_sum(array_column($order['productOrderItems'], 'total_with_tax'));

        // 7️⃣ Generate invoice PDF
        $pdfContent = $this->generateInvoicePdf($order);

        if ($pdfContent === false) {
            throw new ServerErrorHttpException(Yii::t("app", "Failed to generate invoice PDF."));
        }

        // 8️⃣ Send PDF as download
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->getHeaders()->set('Content-Type', 'application/pdf');
        Yii::$app->response->getHeaders()->set(
            'Content-Disposition',
            'attachment; filename="invoice_order_' . $order['id'] . '.pdf"'
        );
        return $pdfContent;

    } catch (UnauthorizedHttpException $e) {
        return $this->sendJsonResponse([
            'status' => self::API_NOK,
            'error' => $e->getMessage(),
            'message' => Yii::t("app", "Authentication failed."),
        ]);
    } catch (NotFoundHttpException $e) {
        return $this->sendJsonResponse([
            'status' => self::API_NOK,
            'error' => $e->getMessage(),
            'message' => Yii::t("app", "The requested order does not exist."),
        ]);
    } catch (\Throwable $e) {
        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);

        return $this->sendJsonResponse([
            'status' => self::API_NOK,
            'error' => $e->getMessage(),
            'message' => Yii::t("app", "An unexpected error occurred."),
        ]);
    }
}


public function generateInvoicePdf($order)
{
    // Fetch order data
    $data = $order;

    // Render the view to HTML
    $html = Yii::$app->controller->renderPartial('invoice', [
        'order' => $data,
    ]);

    // Use mPDF to generate the PDF
    try {
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => Yii::getAlias('@runtime/mpdf'),
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        // Write HTML content to PDF
        $mpdf->WriteHTML($html);

        // Return the PDF content as a string (binary)
        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    } catch (\Mpdf\MpdfException $e) {
        Yii::error([
            'message' => 'PDF generation failed: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);
        return false;
    }
}





          
}
 