<?php
namespace app\modules\admin\controllers;

use app\models\User;
use app\modules\admin\forms\UserForm;
use app\modules\admin\models\BusinessDocuments;
use app\modules\admin\models\BusinessImages;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\UserSearch;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorMainCategoryData;
use app\traits\controllers\FindModelOrFail;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * UsersController implements the CRUD actions for User model.
 */
class UsersController extends Controller
{
    use FindModelOrFail;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->modelClass = UserForm::class;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'vendor-view', 'update-onboarding-status', 'transfer-data', 'create', 'update', 'update-vendor', 'update-status', 'vendor', 'home-visitor', 'account-manager', 'qa', 'marketing', 'update-main-vendor-status', 'create-vendor'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isVendor() || User::isSubAdmin();
                        },
                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'vendor-view', 'transfer-data', 'update-onboarding-status', 'update', 'update-vendor', 'pdf', 'update-status'],
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

    public function actionStatusChange()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();

        if (empty($post['id']) || ! isset($post['val'])) {
            throw new BadRequestHttpException('Missing required parameters: id or val.');
        }

        try {
            $user = User::findOne($post['id']);
            if (! $user) {
                throw new NotFoundHttpException('User not found.');
            }

            $user->status = $post['val'];
            if ($user->update(false)) {
                return ['success' => true, 'message' => 'Status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update status.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }

    public function actionIndex($role = 'user') // default to 'user' if not passed

    {
        $searchModel = new UserSearch();

        // Apply role filter
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['user_role' => $role]);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'role'         => $role,
        ]);
    }

    public function actionVendor($role = '')
    {
        try {
            $searchModel  = new UserSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $role);
            $vendorRole   = User::ROLE_VENDOR;
            $dataProvider->query->andWhere(['user_role' => $vendorRole]);

            Yii::$app->view->title = "Vendors";

            return $this->render('index_vendor', [
                'role'         => $role,
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException("Failed to load dashboard: " . $e->getMessage());
        }
    }

    public function actionAccountManager()
    {
        return $this->renderIndex(User::ROLE_ACCOUNT_MANAGER);
    }

    public function actionHomeVisitor()
    {
        return $this->renderIndex(User::ROLE_HOME_VISITOR);
    }

    public function actionQa()
    {
        return $this->renderIndex(User::ROLE_QA);
    }

    public function actionMarketing()
    {
        return $this->renderIndex(User::ROLE_MARKETING);
    }

    private function renderIndex($role)
    {
        try {
            $searchModel           = new UserSearch();
            $dataProvider          = $searchModel->search(Yii::$app->request->queryParams, $role);
            Yii::$app->view->title = "Dashboard";

            return $this->render('index', [
                'role'         => $role,
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException("Failed to load dashboard: " . $e->getMessage());
        }
    }

    public function actionSubAdmin()
    {
        return $this->renderIndex(User::ROLE_SUB_ADMIN);
    }

    public function actionManagers()
    {
        return $this->renderIndex(User::ROLE_MANAGER);
    }

    public function actionView($id)
    {

        $model = User::findOne(['id' => $id]);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
    public function actionVendorView($id)
    {

        $model = User::findOne(['id' => $id]);

        return $this->render('vendor_view', [
            'model' => $model,
        ]);
    }

    public function actionRefererList()
    {
        try {
            $searchModel           = new UserSearch();
            Yii::$app->view->title = "Referral List";
            $dataProvider          = $searchModel->referralSearch(Yii::$app->request->queryParams);

            return $this->render('referral_list', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException("Failed to load referral list: " . $e->getMessage());
        }
    }

    public function actionCreate()
    {
        $model = new UserForm();
        $model->on(User::EVENT_BEFORE_INSERT, [$model, 'generateAuthKey']);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            // Duplicate check for vendor and admin roles
            if (in_array($model->user_role, [User::ROLE_VENDOR, User::ROLE_ADMIN])) {
                $exists = \app\models\User::find()
                    ->where(['user_role' => $model->user_role])
                    ->andWhere(['or',
                        ['username' => $model->username],
                        ['contact_no' => $model->contact_no],
                    ])
                    ->exists();

                if ($exists) {
                    Yii::$app->session->setFlash('error', 'A user with this username or contact number already exists for this role.');
                    return $this->render('create', ['model' => $model]);
                }
            }

            if ($model->save(false)) {
                return $this->redirectAfterSave($model->user_role);
            } else {
                Yii::error($model->getErrors(), __METHOD__);
                throw new \yii\web\BadRequestHttpException("Failed to save user: " . json_encode($model->getErrors()));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

public function actionCreateVendor()
{
    $model           = new User();
    $model->scenario = 'create_vendor';
    $model->status   = User::STATUS_ACTIVE;

    // AJAX validation
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return \yii\widgets\ActiveForm::validate($model);
    }

    if ($model->load(Yii::$app->request->post())) {
        // Force vendor role
        $model->user_role = User::ROLE_VENDOR;

        // Normalize inputs
        $username   = trim((string) $model->username);
        $contact_no = preg_replace('/\s+/', '', trim((string) $model->contact_no));
        $email      = trim((string) $model->email);

        // ✅ Capture allow_order_approval toggle (from form)
        $allowOrderApproval = Yii::$app->request->post('User')['allow_order_approval'] ?? 0;
        $model->allow_order_approval = $allowOrderApproval ? 1 : 0;

        // ✅ Check role + contact_no validation
        if (!empty($contact_no)) {
            $existingVendor = User::find()
                ->where([
                    'user_role'  => User::ROLE_VENDOR,
                    'contact_no' => $contact_no,
                ])
                ->one();

            if ($existingVendor) {
                Yii::$app->session->setFlash('error', "A vendor with this contact number already exists.");
                return $this->render('_form_vendor', ['model' => $model]);
            }
        }

        // ✅ Duplicate check for username / email
        $orParts = [];
        if ($username !== '') {
            $orParts[] = ['username' => $username];
        }
        if ($email !== '') {
            $orParts[] = ['email' => $email];
        }

        if (!empty($orParts)) {
            $query = User::find()
                ->where(['user_role' => $model->user_role])
                ->andWhere(array_merge(['or'], $orParts));

            $found = $query->one();
            if ($found) {
                $conflict = 'details';
                if ($email !== '' && $found->email == $email) {
                    $conflict = 'email';
                } elseif ($username !== '' && $found->username == $username) {
                    $conflict = 'username';
                }

                Yii::$app->session->setFlash('error', "A vendor with this {$conflict} already exists.");
                return $this->render('_form_vendor', ['model' => $model]);
            }
        }

        // Generate auth key & password
        $model->generateAuthKey();
        if (!empty($model->password)) {
            $model->setPassword($model->password);
        }

        // Store normalized values
        $model->contact_no = $contact_no ?: null;
        $model->username   = $username ?: null;
        $model->email      = $email ?: null;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                throw new \Exception('Failed to save vendor: ' . implode(', ', $model->getFirstErrors()));
            }

            // Get vendor details data
           $vendorDetailsData = Yii::$app->request->post('VendorDetails', []);

            // Save each store
            foreach ($vendorDetailsData as $data) {
                $vendorDetails                   = new VendorDetails();
                $vendorDetails->user_id          = $model->id;
                $vendorDetails->business_name    = $data['business_name'] ?? null;
                $vendorDetails->gst_number       = $data['gst_number'] ?? null;
                $vendorDetails->main_category_id = $data['main_category_id'] ?? null;
                $vendorDetails->address          = $data['address'] ?? null;
                $vendorDetails->status           = VendorDetails::STATUS_ACTIVE;
                $vendorDetails->created_on       = date('Y-m-d H:i:s');

                if (!$vendorDetails->save()) {
                    throw new \Exception('Failed to save vendor details: ' . implode(', ', $vendorDetails->getFirstErrors()));
                }
            }
            // Save the dropdown value user selected
            if (!$model->save(false, ['vendor_store_type'])) {
                throw new \Exception('Failed to save vendor store type.');
            }

            $transaction->commit();

            // ✅ Set success flash
            Yii::$app->session->setFlash(
                'success',
                'Vendor and store created successfully as ' . 
                ($model->vendor_store_type == User::VENDOR_STORE_TYPE_MULTI ? 'Multi Store Vendor' : 'Single Store Vendor') . '!'
            );

            return $this->redirect(['vendor-view', 'id' => $model->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
    }

    return $this->render('_form_vendor', [
        'model' => $model,
    ]);
}




    private function redirectAfterSave($userRole)
    {
        switch ($userRole) {
            case User::ROLE_VENDOR:
                // Redirect to the 'vendor' action in the same controller
                return $this->redirect(['vendor']);
            case User::ROLE_HOME_VISITOR:
                // Redirect to the 'home-visitor' action in the same controller
                return $this->redirect(['home-visitor']);
            default:
                // Redirect to the 'index' action in the same controller
                return $this->redirect(['index']);
        }
    }

    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            }

            return $this->render('update', ['model' => $model]);

        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException("Failed to update user: " . $e->getMessage());
        }
    }

    public function actionUpdateVendor($id)
    {
        try {
            $model = $this->findModel($id);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            }

            return $this->render('updatevendor', ['model' => $model]);

        } catch (\Exception $e) {
            throw new \yii\web\ServerErrorHttpException("Failed to update user: " . $e->getMessage());
        }
    }

    public function actionUpdateStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post                       = Yii::$app->request->post();
        $data                       = [];

        try {
            if (! empty($post['id'])) {
                $model = User::findOne($post['id']);
                if ($model) {
                    $model->status = $post['val'];
                    if ($model->save(false)) {
                        $data['message'] = "Status updated successfully.";
                        $data['id']      = $model->status;
                    } else {
                        $data['message'] = "Failed to update status.";
                    }
                } else {
                    throw new NotFoundHttpException('User not found.');
                }
            } else {
                throw new BadRequestHttpException('Missing user ID.');
            }
        } catch (\Exception $e) {
            $data['message'] = "An error occurred: " . $e->getMessage();
        }

        return $data;
    }

  public function actionTransferData($id)
{
    $user = User::findOne($id);

    if (!$user) {
        Yii::$app->session->setFlash('error', 'User not found.');
        return $this->redirect(['index']);
    }

    if (!in_array($user->user_role, ['vendor', 'user'])) {
        Yii::$app->session->setFlash('error', "User '{$user->username}' is not eligible for transfer.");
        return $this->redirect(['vendor']);
    }

    $db2 = Yii::$app->db2;

    // Check if user already exists in DB2
    $exists = (new \yii\db\Query())
        ->from('user')
        ->where(['or',
            ['email' => $user->email],
            ['username' => $user->username],
            ['contact_no' => $user->contact_no]
        ])
        ->createCommand($db2)
        ->queryOne();

    if ($exists) {
        Yii::$app->session->setFlash('error', "User '{$user->username}' already exists in DB2.");
        return $this->redirect(['vendor']);
    }

    $transaction = $db2->beginTransaction();

    try {
        // Insert user
        $db2->createCommand()->insert('user', [
            'username'   => $user->username,
            'email'      => $user->email,
            'first_name' => $user->first_name,
            'contact_no' => $user->contact_no,
            'user_role'  => $user->user_role,
            'status'     => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ])->execute();

        $newUserId = $db2->getLastInsertID();

        // If user is vendor, transfer vendor-related data
        if ($user->user_role === 'vendor') {
            $vendorDetailsList = VendorDetails::find()->where(['user_id' => $user->id])->all();

            foreach ($vendorDetailsList as $vendorDetail) {
                // Insert vendor details
                $db2->createCommand()->insert('vendor_details', [
                    'user_id'          => $newUserId,
                    'main_category_id' => $vendorDetail->main_category_id,
                    'website_link'     => $vendorDetail->website_link,
                    'business_name'    => $vendorDetail->business_name,
                    'description'      => $vendorDetail->description,
                    'address'          => $vendorDetail->address,
                    'logo'             => $vendorDetail->logo,
                    'is_verified'      => $vendorDetail->is_verified,
                    'gst_number'       => $vendorDetail->gst_number,
                    'shop_licence_no'  => $vendorDetail->shop_licence_no,
                    'account_number'   => $vendorDetail->account_number,
                    'ifsc_code'        => $vendorDetail->ifsc_code,
                    'status'           => $vendorDetail->status,
                ])->execute();

                $newVendorDetailsId = $db2->getLastInsertID();

                // Transfer business documents
                $docs = BusinessDocuments::findAll(['vendor_details_id' => $vendorDetail->id]);
                foreach ($docs as $doc) {
                    $db2->createCommand()->insert('business_documents', [
                        'vendor_details_id' => $newVendorDetailsId,
                        'file'              => $doc->file,
                        'document_type'     => $doc->document_type,
                        'status'            => $doc->status,
                        'created_on'        => $doc->created_on,
                        'updated_on'        => $doc->updated_on,
                        'create_user_id'    => $newUserId,
                        'update_user_id'    => $newUserId,
                    ])->execute();
                }

                // Transfer business images
                $images = BusinessImages::findAll(['vendor_details_id' => $vendorDetail->id]);
                foreach ($images as $img) {
                    $db2->createCommand()->insert('business_images', [
                        'vendor_details_id' => $newVendorDetailsId,
                        'image_file'        => $img->image_file,
                        'status'            => $img->status,
                        'created_on'        => $img->created_on,
                        'updated_on'        => $img->updated_on,
                        'create_user_id'    => $newUserId,
                        'update_user_id'    => $newUserId,
                    ])->execute();
                }

                // Transfer store timings
                $storeTimings = StoreTimings::findAll(['vendor_details_id' => $vendorDetail->id]);
                foreach ($storeTimings as $timing) {
                    $db2->createCommand()->insert('store_timings', [
                        'vendor_details_id' => $newVendorDetailsId,
                        'day_id'            => $timing->day_id,
                        'start_time'        => $timing->start_time,
                        'close_time'        => $timing->close_time,
                        'status'            => $timing->status,
                        'created_on'        => $timing->created_on,
                        'updated_on'        => $timing->updated_on,
                        'create_user_id'    => $newUserId,
                        'update_user_id'    => $newUserId,
                    ])->execute();
                }

                // Transfer main categories (with user_id and dynamic default)
                $mainCategories = VendorMainCategoryData::findAll(['vendor_details_id' => $vendorDetail->id]);

                if (!empty($mainCategories)) {
                    foreach ($mainCategories as $category) {
                        $db2->createCommand()->insert('vendor_main_category_data', [
                            'vendor_details_id' => $newVendorDetailsId,
                            'main_category_id'  => $category->main_category_id,
                            'status'            => $category->status,
                            'user_id'           => $newUserId,
                            'created_on'        => $category->created_on,
                            'updated_on'        => $category->updated_on,
                            'create_user_id'    => $newUserId,
                            'update_user_id'    => $newUserId,
                        ])->execute();
                    }
                } else {
                    // If vendor has no categories, select a dynamic default category
                    $defaultCategory = (new \yii\db\Query())
                        ->select('id')
                        ->from('main_category')
                        ->orderBy('id')
                        ->limit(1)
                        ->createCommand($db2)
                        ->queryScalar();

                    if (!$defaultCategory) {
                        throw new \Exception("DB2 main_category table is empty. Cannot set default category.");
                    }

                    $db2->createCommand()->insert('vendor_main_category_data', [
                        'vendor_details_id' => $newVendorDetailsId,
                        'main_category_id'  => $defaultCategory,
                        'status'            => 1,
                        'user_id'           => $newUserId,
                        'created_on'        => date('Y-m-d H:i:s'),
                        'updated_on'        => date('Y-m-d H:i:s'),
                        'create_user_id'    => $newUserId,
                        'update_user_id'    => $newUserId,
                    ])->execute();
                }
            }
        }

        $transaction->commit();
        Yii::$app->session->setFlash('success', "User '{$user->username}' transferred successfully.");
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', "Transfer failed: " . $e->getMessage());
    }

    return $this->redirect(['vendor']);
}


    public function actionUpdateOnboardingStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userId = Yii::$app->request->post('id');
        $option = Yii::$app->request->post('option'); // main_vendor / allow_onboarding
        $value  = Yii::$app->request->post('value');  // 1 or 0

        // Basic validation
        if (empty($userId) || empty($option)) {
            return ['success' => false, 'message' => 'Invalid request parameters'];
        }

        $model = User::findOne($userId); // Or Vendor model
        if (! $model) {
            return ['success' => false, 'message' => 'Vendor not found'];
        }

        // ✅ Only allow safe fields
        if (in_array($option, ['main_vendor', 'allow_onboarding'])) {
            $model->$option = (int) $value; // force integer (1 or 0)

            if ($model->save(false, [$option])) {
                return [
                    'success' => true,
                    'message' => ucfirst(str_replace('_', ' ', $option)) . ' '
                    . ($value ? 'enabled' : 'disabled') . ' successfully!',
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to save changes'];
            }
        }

        return ['success' => false, 'message' => 'Invalid field option'];
    }

    public function actionUpdateMainVendorStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $userId = Yii::$app->request->post('id');
            $status = Yii::$app->request->post('main_vendor');

            if (empty($userId)) {
                return ['success' => false, 'message' => 'User ID is required'];
            }

            $model = User::findOne($userId);
            if (! $model) {
                return ['success' => false, 'message' => 'User not found'];
            }

            $model->main_vendor = $status;
            if ($model->save(false)) {
                return ['success' => true, 'message' => 'Main vendor status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update main vendor status'];
            }
        } catch (\Exception $e) {
            Yii::error("Error updating main vendor status: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'An error occurred while updating status'];
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model !== null) {
            $model->delete();
        }

        return $this->redirect(['index']);
    }
}
