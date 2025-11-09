<?php
namespace app\modules\admin\controllers;

use app\models\User;
use app\modules\admin\models\search\VendorEarningsSearch;
use app\modules\admin\models\VendorEarnings;
use app\modules\admin\models\VendorPayout;
use app\modules\admin\models\VendorSettlements;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * VendorEarningsController implements the CRUD actions for VendorEarnings model.
 */
class VendorEarningsController extends Controller
{

    public $userRole;

    public function __construct($id, $module, $config = [])
    {
        $this->userRole = \Yii::$app->user->identity->user_role;
        parent::__construct($id, $module, $config);
    }
    public $adminRoles = [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_QA, User::ROLE_VENDOR];

    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'create', 'update', 'delete', 'update-status', 'store-approved'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isVendor() || User::isQa();
                        },

                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'update', 'pdf', 'update-status', 'store-approved'],
                        'matchCallback' => function () {
                            return User::isManager();
                        },
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all VendorEarnings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VendorEarningsSearch();
        if (in_array($this->userRole, $this->adminRoles)) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        if (User::isAdmin()) {
            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else if (User::isVendor()) {
            return $this->render('vendor_index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    /**
     * Displays a single VendorEarnings model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new VendorEarnings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new VendorEarnings();

        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing VendorEarnings model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing VendorEarnings model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (! empty($model)) {

            $model->save(false);
        }

        return $this->redirect(['index']);
    }

    public function actionUpdateStatus()
    {
        $data                        = [];
        $post                        = \Yii::$app->request->post();
        \Yii::$app->response->format = 'json';
        if (! empty($post['id'])) {
            $model = VendorEarnings::find()->where([
                'id' => $post['id'],
            ])->one();
            if (! empty($model)) {

                $model->status = $post['val'];

            }
            if ($model->save(false)) {
                $data['message'] = "Updated";
                $data['id']      = $model->status;
            } else {
                $data['message'] = "Not Updated";

            }

        }
        return $data;
    }

    /**
     * Finds the VendorEarnings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VendorEarnings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorEarnings::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
public function actionStoreApproved()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ids = Yii::$app->request->post('ids', []);
    if (empty($ids) || !is_array($ids)) {
        return ['success' => false, 'message' => 'No records selected.'];
    }

    // Optional inputs for payout uniqueness / idempotency
    $idempotencyKey = Yii::$app->request->post('idempotency_key', null); // optional global key for entire operation
    $periodStart = Yii::$app->request->post('period_start', null); // optional YYYY-MM-DD
    $periodEnd   = Yii::$app->request->post('period_end', null);   // optional YYYY-MM-DD
    $paymentType  = VendorPayout::PAYMENT_TYPE_CREDIT; // adjust if you accept per-request

    $db = Yii::$app->db;
    $transaction = $db->beginTransaction();

    $createdSettlements = [];
    $skipped = [];
    $vendorTotals = []; // vendor_id => amount
    $vendorSettlementIds = []; // vendor_id => [settlement_ids]

    try {
        foreach ($ids as $id) {
            $earning = VendorEarnings::findOne($id);

            if (!$earning) {
                $skipped[$id] = 'Earning not found';
                continue;
            }

            // treat numeric/boolean status values or string 'approved'
            $isApproved = (strtolower((string)$earning->status) === 'approved' || (int)$earning->status === 1);
            if (!$isApproved) {
                $skipped[$id] = 'Earning not approved';
                continue;
            }

            // If settlement already exists (unique constraint and FK exist), skip
            $exists = VendorSettlements::find()
                ->where(['vendor_earnings_id' => $earning->id])
                ->exists();

            if ($exists) {
                $skipped[$id] = 'Already settled';
                continue;
            }

            // Create settlement (DB unique constraint will prevent duplicates in concurrent runs)
            $settlement = new VendorSettlements();
            $settlement->vendor_earnings_id = $earning->id;
            $settlement->status = 1; // settled
            $settlement->created_on = date('Y-m-d H:i:s');
            $settlement->updated_on = date('Y-m-d H:i:s');
            $settlement->create_user_id = Yii::$app->user->id ?? null;
            $settlement->update_user_id = Yii::$app->user->id ?? null;

            try {
                // use save(false) or save() depending on validation needs
                $saved = $settlement->save(false);
            } catch (\yii\db\IntegrityException $ie) {
                // likely unique constraint violation on vendor_earnings_id — treat as already settled
                Yii::warning("IntegrityException while saving settlement for earning {$earning->id}: " . $ie->getMessage(), __METHOD__);
                $skipped[$id] = 'Already settled (race)';
                continue;
            }

            if ($saved === false) {
                // Unexpected failure
                $skipped[$id] = 'Failed to save settlement';
                continue;
            }

            $createdSettlements[] = $settlement->id;

            // Aggregate vendor totals
            $vendorId = $earning->vendor_details_id;
            $amount = (float) $earning->vendor_received_amount;
            if (!isset($vendorTotals[$vendorId])) {
                $vendorTotals[$vendorId] = 0.0;
            }
            $vendorTotals[$vendorId] += $amount;

            $vendorSettlementIds[$vendorId][] = $settlement->id;
        }

        // Nothing to payout
        if (empty($vendorTotals)) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'No settlements were created. Check that earnings are approved and not already settled.',
                'skipped' => $skipped,
            ];
        }

        $createdPayouts = [];

        foreach ($vendorTotals as $vendorId => $totalAmount) {
            // First: try to find an existing payout to re-use (idempotency or period-based)
            $existingPayout = null;

            if (!empty($idempotencyKey)) {
                $existingPayout = VendorPayout::findOne(['idempotency_key' => $idempotencyKey, 'vendor_details_id' => $vendorId]);
            }

            if (!$existingPayout && !empty($periodStart) && !empty($periodEnd)) {
                $existingPayout = VendorPayout::findOne([
                    'vendor_details_id' => $vendorId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'payment_type' => $paymentType,
                ]);
            }

            if ($existingPayout) {
                // reuse existing payout and link settlements
                $payout = $existingPayout;
            } else {
                // create new payout
                $payout = new VendorPayout();
                $payout->vendor_details_id = $vendorId;
                $payout->amount = $totalAmount;
                $payout->payment_type = $paymentType;
                $payout->method_reason = 'Vendor Earnings Settlement';
                $payout->type_id = 1;
                $payout->status = VendorPayout::STATUS_PROCESSING;
                $payout->created_on = date('Y-m-d H:i:s');
                $payout->updated_on = date('Y-m-d H:i:s');
                $payout->create_user_id = Yii::$app->user->id ?? null;
                $payout->update_user_id = Yii::$app->user->id ?? null;

                // attach idempotency/period info if provided
                if (!empty($idempotencyKey)) {
                    $payout->idempotency_key = $idempotencyKey;
                }
                if (!empty($periodStart)) {
                    $payout->period_start = $periodStart;
                }
                if (!empty($periodEnd)) {
                    $payout->period_end = $periodEnd;
                }

                try {
                    $payout->save(false);
                } catch (\yii\db\IntegrityException $ie) {
                    // unique constraint hit (race or duplicate) — fetch the existing payout and continue
                    Yii::warning("IntegrityException while creating payout for vendor {$vendorId}: " . $ie->getMessage(), __METHOD__);
                    // try to find existing by idempotency or period / vendor
                    if (!empty($idempotencyKey)) {
                        $payout = VendorPayout::findOne(['idempotency_key' => $idempotencyKey, 'vendor_details_id' => $vendorId]);
                    }
                    if (empty($payout) && !empty($periodStart) && !empty($periodEnd)) {
                        $payout = VendorPayout::findOne([
                            'vendor_details_id' => $vendorId,
                            'period_start' => $periodStart,
                            'period_end' => $periodEnd,
                            'payment_type' => $paymentType,
                        ]);
                    }

                    if (empty($payout)) {
                        // If still empty, rethrow — unexpected
                        throw $ie;
                    }
                }
            }

            // Link settlements to this payout (bulk update)
            $settlementIds = $vendorSettlementIds[$vendorId] ?? [];
            if (!empty($settlementIds)) {
                VendorSettlements::updateAll(
                    ['vendor_payout_id' => $payout->id, 'updated_on' => date('Y-m-d H:i:s')],
                    ['id' => $settlementIds]
                );
            }

            $createdPayouts[] = [
                'vendor_id' => $vendorId,
                'payout_id' => $payout->id,
                'amount' => $totalAmount,
                'settlements' => $settlementIds,
            ];
        }

        $transaction->commit();

        return [
            'success' => true,
            'message' => 'Vendor Settlements stored and vendor-wise payouts created successfully.',
            'created_settlements' => $createdSettlements,
            'created_payouts' => $createdPayouts,
            'skipped' => $skipped,
        ];
    } catch (\Throwable $e) {
        if ($transaction && $transaction->getIsActive()) {
            $transaction->rollBack();
        }

        Yii::error([
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], __METHOD__);

        return [
            'success' => false,
            'message' => 'An error occurred while processing settlements.',
            'error' => $e->getMessage(),
        ];
    }
}

}
