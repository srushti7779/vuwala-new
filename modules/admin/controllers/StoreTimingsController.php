<?php
namespace app\modules\admin\controllers;

use app\models\User;
use app\modules\admin\models\base\StoreTimingsHasBrakes;
use app\modules\admin\models\search\StoreTimingsSearch;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\VendorDetails;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * StoreTimingsController implements the CRUD actions for StoreTimings model.
 */
class StoreTimingsController extends Controller
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
                        'actions'       => ['index', 'view', 'create-store', 'bulk-update-timings','add-break-timings','remove-break-timings', 'create', 'update', 'delete', 'update-status'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isVendor() || User::isQa();
                        },

                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'update', 'bulk-update-timings','add-break-timings','remove-break-timings', 'pdf', 'update-status'],
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
     * Lists all StoreTimings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StoreTimingsSearch();
        if (in_array($this->userRole, $this->adminRoles)) {

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StoreTimings model.
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
     * Creates a new StoreTimings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new StoreTimings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // ✅ Redirect to the correct StoreTiming view
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCreateStore()
    {
        $model = new StoreTimings();

        // Fetch the vendor associated with the logged-in user
        $vendorDetails = VendorDetails::findOne(['user_id' => Yii::$app->user->id]);
        // print_r($vendorDetails);

        if (! $vendorDetails) {
            Yii::$app->session->setFlash('error', 'Vendor details not found for current user.');
            Yii::warning("VendorDetails not found for user_id: " . Yii::$app->user->id);
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->vendor_details_id = $vendorDetails->id;
            $model->status            = 1;
            $model->create_user_id    = Yii::$app->user->id;
            $model->update_user_id    = Yii::$app->user->id;
            $model->created_on        = date('Y-m-d H:i:s');
            $model->updated_on        = date('Y-m-d H:i:s');

            // Correct time format
            $model->start_time = date('H:i:s', strtotime($model->start_time));
            $model->close_time = date('H:i:s', strtotime($model->close_time));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Store Timing created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error("Failed to save StoreTiming. Errors: " . json_encode($model->errors));
                Yii::$app->session->setFlash('error', 'Failed to save Store Timing.');
            }
        }

        return $this->render('create_store', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing StoreTimings model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->loadAll(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Store timing updated successfully.');

            return $this->redirect([
                '/admin/vendor-details/view',
                'id' => $model->vendor_details_id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StoreTimings model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (! empty($model)) {
            $model->status = StoreTimings::STATUS_DELETE;
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
            $model = StoreTimings::find()->where([
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
     * Finds the StoreTimings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StoreTimings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StoreTimings::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
public function actionBulkUpdateTimings()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $data = Yii::$app->request->post('StoreTimings', []);
    $success = false;
    $errors = [];

    foreach ($data as $id => $row) {
        $model = StoreTimings::findOne($id);
        if ($model) {
            $start = strtotime($row['start_time']);
            $close = strtotime($row['close_time']);

            if ($start > $close) {
                $errors[] = "Start Time cannot be greater than Close Time for {$model->day->title}.";
                continue;
            }

            $model->start_time = $row['start_time'];
            $model->close_time = $row['close_time'];

            if (!$model->save(false)) {
                $errors[] = "Failed to update {$model->day->title}.";
            } else {
                $success = true;
            }
        }
    }

    return [
        'success' => $success && empty($errors),
        'message' => !empty($errors)
            ? implode("<br>", $errors)
            : ($success ? 'Store timings updated successfully.' : 'No data received.'),
    ];
}
public function actionAddBreakTimings()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $request = Yii::$app->request;

    $timeSlots = $request->post('timeSlots', []); // array of breaks
    $storeTimingId = $request->post('store_timing_id');

    // Basic validation
    if (empty($timeSlots) || !$storeTimingId) {
        return [
            'success' => false,
            'message' => 'Please provide break times and store timing ID.',
        ];
    }

    $storeTiming = StoreTimings::findOne($storeTimingId);
    if (!$storeTiming) {
        return [
            'success' => false,
            'message' => 'Invalid store timing ID.',
        ];
    }

    $storeStart = strtotime($storeTiming->start_time);
    $storeClose = strtotime($storeTiming->close_time);

    $successCount = 0;
    $errors = [];

    foreach ($timeSlots as $slot) {
        $start = isset($slot['start_time']) ? strtotime($slot['start_time']) : null;
        $end = isset($slot['end_time']) ? strtotime($slot['end_time']) : null;

        // Validate presence
        if (!$start || !$end) {
            $errors[] = 'Start and end time are required for all breaks.';
            continue;
        }

        // Validate time order
        if ($start >= $end) {
            $errors[] = "Break {$slot['start_time']} - {$slot['end_time']} start time must be before end time.";
            continue;
        }

        // Validate within store hours
        if ($start < $storeStart || $end > $storeClose) {
            $errors[] = "Break {$slot['start_time']} - {$slot['end_time']} is outside store hours ({$storeTiming->start_time} - {$storeTiming->close_time}).";
            continue;
        }

        // Save break
        $model = new StoreTimingsHasBrakes();
        $model->store_timing_id = $storeTimingId;
        $model->start_time = date("H:i:s", $start);
        $model->end_time = date("H:i:s", $end);
        $model->status = 1;
        $model->created_on = date('Y-m-d H:i:s');
        $model->updated_on = date('Y-m-d H:i:s');
        $model->create_user_id = Yii::$app->user->id;

        if ($model->save()) {
            $successCount++;
        } else {
            $errors[] = "Failed to save break {$slot['start_time']} - {$slot['end_time']}.";
        }
    }

    // Return JSON response for SweetAlert
    if ($successCount > 0 && empty($errors)) {
        return [
            'success' => true,
            'message' => 'Break Timings added successfully.'
        ];
    } elseif ($successCount > 0) {
        return [
            'success' => true,
            'message' => "Some breaks added successfully.<br>" . implode("<br>", $errors)
        ];
    } else {
        return [
            'success' => false,
            'message' => implode("<br>", $errors)
        ];
    }
}

public function actionRemoveBreakTimings($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $break = StoreTimingsHasBrakes::findOne($id);

    if (!$break) {
        return [
            'success' => false,
            'message' => 'Break not found.',
        ];
    }

    try {
        $break->delete();
        return [
            'success' => true,
            'message' => 'Break removed successfully.',
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to remove break. Please try again.',
        ];
    }
}








}




