<?php
namespace app\modules\admin\controllers;

use app\models\User;
use app\modules\admin\models\base\MainCategory;
use app\modules\admin\models\BusinessDocuments;
use app\modules\admin\models\search\VendorDetailsSearch;
use app\modules\admin\models\ServiceHasCoupons;
use app\modules\admin\models\Services;
use app\modules\admin\models\ServiceType;
use app\modules\admin\models\StoreServiceTypes;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\SubCategory;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorMainCategoryData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class VendorDetailsController extends Controller
{

    public $userRole;

    public function __construct($id, $module, $config = [])
    {
        $this->userRole = \Yii::$app->user->identity->user_role ?? '';
        parent::__construct($id, $module, $config);
    }
    public $adminRoles = [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_QA, User::ROLE_VENDOR];

    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete'               => ['post'],
                    'soft-delete-multiple' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow'         => true,
                        'actions'       => [
                            'index',
                            'import',
                            'download',
                            'process-import',
                            'import-csv',
                            'import-excel',
                            'view',
                            'create',
                            'create-vendor',
                            'service-has-coupons',
                            'update',
                            'delete',
                            'update-status',
                            'add-business-documents',
                            'add-business-images',
                            'add-services',
                            'add-staff',
                            'add-store-timings',
                            'add-sub-category',
                            'pending-vendors-onboarding',
                            'upload-services-excel',
                            'shop-locations',
                            'soft-delete-multiple',
                            'toggle-order-approval',
                            'update-vendor-details',
                            'download-example',
                        ],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin() || User::isVendor() || User::isQa();
                        },
                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['index', 'view', 'create-vendor', 'download', 'update-vendor-details', 'service-has-coupons', 'update', 'pdf', 'download-example', 'update-status', 'upload-services-excel'],
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
     * Lists all VendorDetails models.
     * @return mixed
     */

    public function actionDownload($file)
    {
                          // Ensure the URL is valid and the file exists
        $fileUrl = $file; // Since the URL is absolute, use it directly

        // Check if the file exists at the provided URL
        if (@file_get_contents($fileUrl)) {
            return Yii::$app->response->sendFile($fileUrl);
        } else {
            Yii::$app->session->setFlash('error', 'File not found.');
            return $this->redirect(['index']);
        }
    }

    public function actionIndex()
    {
        $searchModel  = new VendorDetailsSearch();
        $dataProvider = null; // initialize

        if (in_array($this->userRole, $this->adminRoles)) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } elseif (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managerSearch(Yii::$app->request->queryParams);
        } elseif (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) {
            $dataProvider = $searchModel->vendorSearch(Yii::$app->request->queryParams);
        }

        if (User::isAdmin()) {
            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('vendor_index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionPendingVendorsOnboarding()
    {
        $searchModel = new VendorDetailsSearch();
        if (in_array($this->userRole, $this->adminRoles)) {
            $dataProvider = $searchModel->pendingVendorsOnboardingSearch(Yii::$app->request->queryParams);
        } else if (\Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionImport()
    {
        $model = new VendorDetails(); // Create an instance of VendorDetails model
        return $this->render('import', [
            'model' => $model,
        ]);
    }

    // Action to process the import 
    public function actionProcessImport()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFile = UploadedFile::getInstanceByName('file');

            if (! $uploadedFile) {
                Yii::$app->session->setFlash('error', 'No file uploaded.');
                return $this->render('import');
            }

            // Validate file type and size
            $allowedExtensions = ['csv', 'xls', 'xlsx'];
            $extension         = strtolower($uploadedFile->extension);

            if (! in_array($extension, $allowedExtensions)) {
                Yii::$app->session->setFlash('error', 'Invalid file type. Please upload a CSV or Excel file.');
                return $this->redirect(['import']);
            }

            if ($uploadedFile->size > 5 * 1024 * 1024) { // 5MB limit
                Yii::$app->session->setFlash('error', 'File size exceeds the allowed limit of 5MB.');
                return $this->redirect(['import']);
            }

            if ($uploadedFile->size == 0) {
                Yii::$app->session->setFlash('error', 'Uploaded file is empty.');
                return $this->redirect(['import']);
            }

            // Save the uploaded file
            $filePath = Yii::getAlias('@webroot/uploads/') . uniqid() . '_' . $uploadedFile->baseName . '.' . $uploadedFile->extension;

            if (! $uploadedFile->saveAs($filePath)) {
                Yii::$app->session->setFlash('error', 'Failed to upload the file.');
                return $this->redirect(['import']);
            }

            try {
                // Run import
                $importResult = ($extension === 'csv')
                    ? $this->importCsv($filePath)
                    : $this->importExcel($filePath);

                // Handle result
                if ($importResult['successCount'] > 0) {
                    Yii::$app->session->setFlash(
                        'success',
                        "Vendors imported successfully! {$importResult['successCount']} succeeded, {$importResult['failureCount']} failed."
                    );
                } else {
                    Yii::$app->session->setFlash(
                        'error',
                        'Import failed: No valid records found. Please review your file and try again.'
                    );
                }

                // Show detailed row errors (limit to 5)
                if (! empty($importResult['errors'])) {
                    $errorMessage = "Import completed with errors:<br>";
                    foreach (array_slice($importResult['errors'], 0, 5) as $error) {
                        $errorMessage .= "- {$error}<br>";
                    }

                    if (count($importResult['errors']) > 5) {
                        $errorMessage .= "... and " . (count($importResult['errors']) - 5) . " more errors.";
                    }

                    Yii::$app->session->addFlash('warning', $errorMessage);
                }

                return $this->redirect(['index']);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'An error occurred while importing: ' . $e->getMessage());
                Yii::error('Import error: ' . $e->getMessage());
            } finally {
                // Clean up the uploaded file
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        return $this->render('import');
    }

    // Function to handle CSV file import
    // Function to handle CSV file import 
    public function importCsv($filePath)
    {
        $successCount = 0;
        $failureCount = 0;
        $errors       = [];
        $hasValidData = false;
        $rowNumber    = 1; // Start from 1 to account for header

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);

            // Check if file has data beyond header
            if ($header === false) {
                throw new \Exception('File contains no data - only empty rows');
            }

            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $row = array_combine($header, $data);

                // Check if row has any non-empty values
                $hasData = false;
                foreach ($row as $value) {
                    if (! empty(trim($value))) {
                        $hasData      = true;
                        $hasValidData = true;
                        break;
                    }
                }

                if (! $hasData) {
                    continue; // Skip completely empty rows
                }

                $result = $this->saveVendorData($row, $rowNumber);
                if ($result === true) {
                    $successCount++;
                } else {
                    $failureCount++;
                    $errors[] = $result;
                }
            }
            fclose($handle);

            // If no valid data was found in the entire file
            if (! $hasValidData && $rowNumber > 1) {
                throw new \Exception('File contains only empty rows after the header');
            }
        }

        Yii::info("CSV Import: {$successCount} succeeded, {$failureCount} failed.");

        return [
            'successCount' => $successCount,
            'failureCount' => $failureCount,
            'errors'       => $errors,
        ];
    }

    // Function to handle Excel file import
    // Function to handle Excel file import
    public function importExcel($filePath)
    {
        $successCount = 0;
        $failureCount = 0;
        $errors       = [];
        $hasValidData = false;
        $rowNumber    = 1; // Start from 1 to account for header

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray();

        if (count($data) < 2) {
            throw new \Exception('Excel file contains no data rows - only header or empty');
        }

        $header = array_shift($data);

        foreach ($data as $rowData) {
            $rowNumber++;
            $row = array_combine($header, $rowData);

            // Check if row has any non-empty values
            $hasData = false;
            foreach ($row as $value) {
                if (! empty(trim($value))) {
                    $hasData      = true;
                    $hasValidData = true;
                    break;
                }
            }

            if (! $hasData) {
                continue; // Skip completely empty rows
            }

            $result = $this->saveVendorData($row, $rowNumber);
            if ($result === true) {
                $successCount++;
            } else {
                $failureCount++;
                $errors[] = $result;
            }
        }

        // If no valid data was found in the entire file
        if (! $hasValidData) {
            throw new \Exception('Excel file contains only empty rows after the header');
        }

        Yii::info("Excel Import: {$successCount} succeeded, {$failureCount} failed.");

        return [
            'successCount' => $successCount,
            'failureCount' => $failureCount,
            'errors'       => $errors,
        ];
    }

    // Common function to save vendor data      
    public function saveVendorData($row, $rowNumber)
    {
        // Check for empty row
        if (empty(array_filter($row, function ($value) {
            return ! is_null($value) && $value !== '' && trim($value) !== '';
        }))) {
            return "Row {$rowNumber}: Empty row skipped";
        }

        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'contact_no', 'business_name'];
        foreach ($requiredFields as $field) {
            if (empty($row[$field]) || trim($row[$field]) === '') {
                return "Row {$rowNumber}: Missing required field '{$field}'";
            }
        }

        // Validate email format
        if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            return "Row {$rowNumber}: Invalid email format '{$row['email']}'";
        }

        // Validate contact number format (example: 10 digits)
        if (! preg_match('/^\d{10,15}$/', $row['contact_no'])) {
            return "Row {$rowNumber}: Invalid contact number format '{$row['contact_no']}'";
        }

        // Check for duplicate contact number with vendor role
        // Check for duplicate contact number with vendor role
        $existingVendorByContact = User::find()
            ->where(['contact_no' => $row['contact_no'], 'user_role' => User::ROLE_VENDOR])
            ->one();
        if ($existingVendorByContact) {
            return "Row {$rowNumber}: Vendor with contact number '{$row['contact_no']}' already exists).";
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Create User object
            $user             = new User();
            $user->first_name = $row['first_name'];
            $user->last_name  = $row['last_name'];
            $user->email      = $row['email'];
            $user->contact_no = $row['contact_no'];
            $user->username   = $row['contact_no'] . '@' . User::ROLE_VENDOR . '.com';
            $user->user_role  = User::ROLE_VENDOR;
            $user->status     = User::STATUS_ACTIVE;
            $user->setPassword(Yii::$app->security->generateRandomString(10));
            $user->generateAuthKey();

            // Validate and save the user
            if (! $user->validate() || ! $user->save()) {
                $errors = implode(', ', $user->getFirstErrors());
                throw new \Exception("User validation failed: {$errors}");
            }

            // Create VendorDetails object
            $vendor                   = new VendorDetails();
            $vendor->user_id          = $user->id;
            $vendor->business_name    = $row['business_name'];
            $vendor->description      = $row['description'] ?? null;
            $vendor->main_category_id = $row['main_category_id'] ?? null;
            $vendor->website_link     = $row['website_link'] ?? null;
            $vendor->gst_number       = $row['gst_number'] ?? null;
            $vendor->latitude         = $row['latitude'] ?? null;
            $vendor->longitude        = $row['longitude'] ?? null;
            $vendor->address          = $row['address'] ?? null;
            $vendor->logo             = $row['logo'] ?? null;
            $vendor->account_number   = $row['account_number'] ?? null;
            $vendor->ifsc_code        = $row['ifsc_code'] ?? null;
            $vendor->shop_licence_no  = $row['shop_licence_no'] ?? null;
            $vendor->avg_rating       = $row['avg_rating'] ?? 0;
            $vendor->min_order_amount = $row['min_order_amount'] ?? 0;
            $vendor->commission       = $row['commission'] ?? 0;
            $vendor->offer_tag        = $row['offer_tag'] ?? null;
            $vendor->service_radius   = $row['service_radius'] ?? null;
            $vendor->min_service_fee  = $row['min_service_fee'] ?? 0;
            $vendor->discount         = $row['discount'] ?? 0;
            $vendor->status           = VendorDetails::STATUS_VERIFICATION_PENDING;
            // Assign proper main vendor ID
           $vendor->main_vendor_user_id = Yii::$app->user->id;

            // Set the created_on and updated_on fields automatically
            $vendor->created_on = date('Y-m-d H:i:s');
            $vendor->updated_on = date('Y-m-d H:i:s');

            // Set update_user_id and create_user_id
            $vendor->update_user_id = Yii::$app->user->id;
            $vendor->create_user_id = Yii::$app->user->id;

            // Validate and save the vendor data
            if (! $vendor->validate() || ! $vendor->save()) {
                $errors = implode(', ', $vendor->getFirstErrors());
                throw new \Exception("Vendor validation failed: {$errors}");
            }

            $transaction->commit();
            Yii::info("Row {$rowNumber}: Vendor successfully imported: User ID {$user->id}, Vendor ID {$vendor->id}");
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Import failed for row $rowNumber: " . $e->getMessage());
            return "Row {$rowNumber}: " . $e->getMessage();
        }
    }

    /**
     * Displays a single VendorDetails model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $providerBusinessDocuments = new \yii\data\ArrayDataProvider([
            'allModels' => $model->businessDocuments,
        ]);
        $providerBusinessImages = new \yii\data\ArrayDataProvider([
            'allModels' => $model->businessImages,
        ]);
        $providerServices = new \yii\data\ArrayDataProvider([
            'allModels' => $model->services,
        ]);
        $providerStaff = new \yii\data\ArrayDataProvider([
            'allModels' => $model->staff,
        ]);

        // ✅ Use VendorDetails.id here
        $providerServiceCoupon = new \yii\data\ActiveDataProvider([
            'query' => ServiceHasCoupons::find()
                ->joinWith(['service'])
                ->where(['services.vendor_details_id' => $model->id]),
        ]);

        $providerStoreTimings = new \yii\data\ArrayDataProvider([
            'allModels' => $model->storeTimings,
        ]);
        $providerSubCategory = new \yii\data\ArrayDataProvider([
            'allModels' => $model->subCategories,
        ]);
        $providerVendorMainCategories = new ActiveDataProvider([
            'query' => VendorMainCategoryData::find()
                ->where(['vendor_details_id' => $model->id])
                ->joinWith('mainCategory'),
        ]);

        // ✅ This one is based on user_id (correct)
        $storeDataProvider = new \yii\data\ActiveDataProvider([
            'query' => VendorDetails::find()->where(['user_id' => $model->user_id]),
        ]);

        return $this->render('view', [
            'model'                        => $model,
            'providerBusinessDocuments'    => $providerBusinessDocuments,
            'providerBusinessImages'       => $providerBusinessImages,
            'providerServices'             => $providerServices,
            'providerStaff'                => $providerStaff,
            'providerServiceCoupon'        => $providerServiceCoupon,
            'providerStoreTimings'         => $providerStoreTimings,
            'providerSubCategory'          => $providerSubCategory,
            'providerVendorMainCategories' => $providerVendorMainCategories,
            'storeDataProvider'            => $storeDataProvider,
        ]);
    }

    /**
     * Creates a new VendorDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */public function actionCreate($id = null) // $id is the user_id passed in the URL

    {
        $model = new VendorDetails();

        if ($id !== null) {
            $model->user_id = (int) $id;
        }

        // Fetch all main categories
        $mainCategories = \app\modules\admin\models\MainCategory::find()
            ->orderBy('id')
            ->asArray()
            ->all();

        $mainCategoryList = \yii\helpers\ArrayHelper::map($mainCategories, 'id', 'title');

        if ($model->load(Yii::$app->request->post())) {

            // Re-assign user_id because it's not coming from the form
            $model->user_id = (int) $id;

            // Handle multiple selected categories
            if (is_array($model->main_category_id)) {
                $model->main_category_id = implode(',', $model->main_category_id);
            }

            if ($model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model'            => $model,
            'mainCategoryList' => $mainCategoryList,
        ]);
    }
    public function actionCreateVendor()
    {
        $vendorUser       = new User();
        $storeModel       = new VendorDetails();
        $mainCategoryList = MainCategory::find()->select(['title'])->indexBy('id')->column();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $vendorUser->load($post);
            $storeModel->load($post);

            $errors = [];

            // -------------------------
            // Vendor Validations
            // -------------------------
            if (empty($vendorUser->username) && ! empty($vendorUser->contact_no)) {
                $vendorUser->username = $vendorUser->contact_no . '@' . User::ROLE_VENDOR . '.com';
            }
            if (empty($vendorUser->username)) {
                $errors[] = 'Username cannot be empty.';
            }
            if (empty($vendorUser->contact_no)) {
                $errors[] = 'Contact number cannot be empty.';
            }
            if (empty($vendorUser->password)) {
                $vendorUser->password = 'default123'; // fallback password
            }

            // -------------------------
            // Store Validations
            // -------------------------
            if (empty($storeModel->business_name)) {
                $errors[] = 'Business name cannot be empty.';
            }
            if (empty($storeModel->address)) {
                $errors[] = 'Address cannot be empty.';
            }
            if (empty($storeModel->gst_number)) {
                $errors[] = 'GST Number cannot be empty.';
            }

            if (! empty($errors)) {
                Yii::$app->session->setFlash('error', implode('<br>', $errors));
                return $this->render('create_vendor_form', [
                    'vendorUser'       => $vendorUser,
                    'storeModel'       => $storeModel,
                    'mainCategoryList' => $mainCategoryList,
                ]);
            }

            // -------------------------
            // Prepare Vendor
            // -------------------------
            $vendorUser->user_role = User::ROLE_VENDOR;
            $vendorUser->status    = User::STATUS_ACTIVE;
            $vendorUser->setPassword($vendorUser->password);
            $vendorUser->generateAuthKey();
            $vendorUser->create_user_id       = Yii::$app->user->id;
            $vendorUser->allow_order_approval = isset($post['User']['allow_order_approval'])
                ? (int) $post['User']['allow_order_approval']
                : 0;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($vendorUser->save()) {
                    // -------------------------
                    // Enforce Single-Store Rule
                    // -------------------------
                    $existingStore = VendorDetails::find()->where(['user_id' => $vendorUser->id])->one();
                    if ($existingStore !== null) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'This vendor already has a store. Multi-store is not allowed.');
                        return $this->redirect(['view', 'id' => $existingStore->id]);
                    }

                    // Link store to user
                    $storeModel->user_id = $vendorUser->id;

                    // ✅ Handle Logo Upload via Notification::imageKitUpload()
                    $logoFile = UploadedFile::getInstance($storeModel, 'logo');
                    if ($logoFile) {
                        $uploadResult     = Yii::$app->notification->imageKitUpload($logoFile);
                        $storeModel->logo = $uploadResult['url'] ?? null;
                    } else {
                        $storeModel->logo = Yii::$app->params['defaultVendorLogo'] ?? null;
                    }

                    // Fill optional fields safely
                    $storeModel->business_name       = $storeModel->business_name ?? '';
                    $storeModel->description         = $storeModel->description ?? '';
                    $storeModel->main_vendor_user_id = $vendorUser->create_user_id ?? null;
                    $storeModel->website_link        = $storeModel->website_link ?? null;
                    $storeModel->gst_number          = $storeModel->gst_number ?? null;
                    $storeModel->latitude            = $storeModel->latitude ?? null;
                    $storeModel->longitude           = $storeModel->longitude ?? null;
                    $storeModel->address             = $storeModel->address ?? null;
                    $storeModel->shop_licence_no     = $storeModel->shop_licence_no ?? null;
                    $storeModel->avg_rating          = $storeModel->avg_rating ?? 0;
                    $storeModel->min_order_amount    = $storeModel->min_order_amount ?? 0;
                    $storeModel->commission          = $storeModel->commission ?? 0;
                    $storeModel->offer_tag           = $storeModel->offer_tag ?? null;
                    $storeModel->service_radius      = $storeModel->service_radius ?? null;
                    $storeModel->min_service_fee     = $storeModel->min_service_fee ?? 0;
                    $storeModel->discount            = $storeModel->discount ?? 0;
                    $storeModel->status              = VendorDetails::STATUS_ACTIVE;

                    if ($storeModel->save(false)) {
                        // Save selected categories into vendor_main_category_data
                        $selectedCategoryIds = $storeModel->main_category_ids ?? [];
                        foreach ($selectedCategoryIds as $categoryId) {
                            $vendorCategory                    = new VendorMainCategoryData();
                            $vendorCategory->vendor_details_id = $storeModel->id;
                            $vendorCategory->user_id           = $storeModel->user_id;
                            $vendorCategory->main_category_id  = $categoryId;
                            $vendorCategory->status            = VendorMainCategoryData::STATUS_ACTIVE;
                            $vendorCategory->save(false);
                        }

                        User::generateStoreTimings($storeModel->id);

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Vendor, store and store timings created successfully.');
                        return $this->redirect(['view', 'id' => $storeModel->id]);
                    } else {
                        Yii::error($storeModel->getErrors(), __METHOD__);
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Store could not be saved.');
                    }
                } else {
                    Yii::error($vendorUser->getErrors(), __METHOD__);
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Vendor user could not be saved.');
                }
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Something went wrong: ' . $e->getMessage());
            }
        }

        return $this->render('create_vendor_form', [
            'vendorUser'       => $vendorUser,
            'storeModel'       => $storeModel,
            'mainCategoryList' => $mainCategoryList,
        ]);
    }

    /**
     * Updates an existing VendorDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */

    public function actionUpdate($id)
    {
        $model    = $this->findModel($id);
        $oldImage = $model->logo;

        // Fetch main categories
        $mainCategories = MainCategory::find()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->asArray()
            ->all();

        // Map categories for Select2
        $mainCategoryList = ArrayHelper::map(
            $mainCategories,
            fn($item) => (string) $item['id'],
            'title'
        );

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $post         = Yii::$app->request->post();
            $upload_image = \yii\web\UploadedFile::getInstance($model, 'logo');

            // Handle logo upload
            if (! empty($upload_image)) {
                $image = Yii::$app->notification->imageKitUpload($upload_image);

                if (! empty($image) && isset($image['url'])) {
                    // upload success
                    $model->logo = $image['url'];
                } else {
                    // upload failed → keep old image + show error
                    $model->logo = $oldImage;
                    Yii::$app->session->setFlash('error', 'Logo upload failed. Please try again.');
                }
            } else {
                $model->logo = $oldImage;
            }

            // Save vendor details first
            if ($model->save(false)) {
                // Handle main categories
                $selectedCategoryIds = $post['VendorDetails']['main_category_ids'] ?? [];

                // ✅ Check for empty selection
                if (empty($selectedCategoryIds)) {
                    Yii::$app->session->setFlash('error', 'Please select at least one main category.');
                    return $this->redirect(['update', 'id' => $model->id]);
                }

                // Get existing vendor categories indexed by main_category_id
                $existingCategories = VendorMainCategoryData::find()
                    ->where(['vendor_details_id' => $model->id])
                    ->indexBy('main_category_id')
                    ->all();

                // Process selected categories - mark active or create new
                foreach ($selectedCategoryIds as $categoryId) {
                    if (isset($existingCategories[$categoryId])) {
                        $existingCategories[$categoryId]->status = VendorMainCategoryData::STATUS_ACTIVE;
                        $existingCategories[$categoryId]->save(false);
                        unset($existingCategories[$categoryId]);
                    } else {
                        $vendorCategory                    = new VendorMainCategoryData();
                        $vendorCategory->vendor_details_id = $model->id;
                        $vendorCategory->user_id           = $model->user_id;
                        $vendorCategory->main_category_id  = $categoryId;
                        $vendorCategory->status            = VendorMainCategoryData::STATUS_ACTIVE;
                        $vendorCategory->save(false);
                    }
                }

                // Deactivate unselected categories
                foreach ($existingCategories as $category) {
                    $category->status = VendorMainCategoryData::STATUS_INACTIVE;
                    $category->save(false);
                }

                Yii::$app->session->setFlash('success', 'Vendor details updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error updating vendor details.');
            }
        } else {
            // Pre-populate active categories
            $vendorMainCategories = VendorMainCategoryData::find()
                ->select('main_category_id')
                ->where([
                    'vendor_details_id' => $model->id,
                    'status'            => VendorMainCategoryData::STATUS_ACTIVE,
                ])
                ->column();

            $model->main_category_ids = array_map('strval', $vendorMainCategories);
        }

        return $this->render('update', [
            'model'            => $model,
            'mainCategoryList' => $mainCategoryList,
        ]);
    }

    /**
     * Deletes an existing VendorDetails model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if (! empty($model)) {
            $model->status = VendorDetails::STATUS_DELETE;
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
            $model = VendorDetails::find()->where([
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
     * Finds the VendorDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VendorDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VendorDetails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for BusinessDocuments
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddBusinessDocuments()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('BusinessDocuments');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formBusinessDocuments', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for BusinessImages
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddBusinessImages()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('BusinessImages');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formBusinessImages', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for Services
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddServices()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('Services');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formServices', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for Staff
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddStaff()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('Staff');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formStaff', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    public function actionServiceHasCoupons()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('Service Coupons');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formServiceCoupons ', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for StoreTimings
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddStoreTimings()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('StoreTimings');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formStoreTimings', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Action to load a tabular form grid
     * for SubCategory
     * @author Yohanes Candrajaya <moo.tensai@gmail.com>
     * @author Jiwantoro Ndaru <jiwanndaru@gmail.com>
     *
     * @return mixed
     */
    public function actionAddSubCategory()
    {
        if (Yii::$app->request->isAjax) {
            $row = Yii::$app->request->post('SubCategory');
            if (! empty($row)) {
                $row = array_values($row);
            }
            if ((Yii::$app->request->post('isNewRecord') && Yii::$app->request->post('_action') == 'load' && empty($row)) || Yii::$app->request->post('_action') == 'add') {
                $row[] = [];
            }

            return $this->renderAjax('_formSubCategory', ['row' => $row]);
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionUploadServicesExcel()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        ini_set('memory_limit', '1024M'); // allow higher memory for import
        set_time_limit(300);              // prevent timeout

        try {
            $vendorId = Yii::$app->request->post('vendor_details_id');
            $file     = UploadedFile::getInstanceByName('excel_file');

            if (! $file) {
                throw new \yii\web\BadRequestHttpException("No file uploaded.");
            }

            if (! in_array($file->extension, ['xlsx', 'xls'])) {
                throw new \yii\web\BadRequestHttpException("Invalid file format.");
            }

            $uploadDir = Yii::getAlias('@runtime/tmp/');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $path = $uploadDir . uniqid('upload_') . '.' . $file->extension;
            if (! $file->saveAs($path)) {
                throw new \yii\web\ServerErrorHttpException("Failed to save uploaded file.");
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true); // ❗ Don't read styles/formulas
            $spreadsheet = $reader->load($path);
            $worksheet   = $spreadsheet->getActiveSheet();

            $rows = $worksheet->toArray(null, true, true, true);
            unset($spreadsheet, $worksheet, $reader); // 🧹 Free memory
            gc_collect_cycles();

            $header = array_map('trim', array_shift($rows));

            $expectedHeaders = [
                'main_category',
                'service_type',
                'sub_category',
                'main_services_name',
                'main_services_description',
                'from_price',
                'to_price',
                'is_price_range',
                'multi_selection',
                'main_services_price',
                'main_services_duration',
                'home_visit',
                'walk_in',
                'service_for',
                'is_parent_service',
                'child_services_name',
                'child_services_price',
                'child_services_duration',
            ];

            $missingHeaders    = array_diff($expectedHeaders, $header);
            $unexpectedHeaders = array_diff($header, $expectedHeaders);

            if (! empty($missingHeaders) || ! empty($unexpectedHeaders)) {
                if (file_exists($path)) {
                    unlink($path);
                }

                return [
                    'status'  => 'error',
                    'message' => "Excel header mismatch. Missing: " . implode(', ', $missingHeaders) . " | Unexpected: " . implode(', ', $unexpectedHeaders),
                ];
            }

            $successCount = 0;
            $failureCount = 0;

            foreach ($rows as $row) {
                $rowData = array_combine($header, $row);
                // 🔁 Extract fields
                $mainCategory     = trim($rowData['main_category'] ?? '');
                $serviceTypeVal   = trim($rowData['service_type'] ?? '');
                $subCategoryTitle = trim($rowData['sub_category'] ?? '');
                $mainServiceName  = trim($rowData['main_services_name'] ?? '');
                $mainServiceDesc  = trim($rowData['main_services_description'] ?? '');

                $from_price     = trim($rowData['from_price'] ?? '');
                $to_price       = trim($rowData['to_price'] ?? '');
                $is_price_range = trim($rowData['is_price_range'] ?? '');

                $multi_selection      = trim($rowData['multi_selection'] ?? 0);
                $mainServicePrice     = trim($rowData['main_services_price'] ?? 0);
                $mainServiceDuration  = trim($rowData['main_services_duration'] ?? '');
                $homeVisit            = trim($rowData['home_visit'] ?? 0);
                $walkIn               = trim($rowData['walk_in'] ?? 0);
                $serviceFor           = trim($rowData['service_for'] ?? '');
                $isParent             = trim($rowData['is_parent_service'] ?? 0);
                $childServiceName     = trim($rowData['child_services_name'] ?? '');
                $childServicePrice    = trim($rowData['child_services_price'] ?? '');
                $childServiceDuration = trim($rowData['child_services_duration'] ?? '');

                // 🧭 Step 1: Main Category
                $mainCategoryModel = \app\modules\admin\models\MainCategory::findOne(['title' => trim($mainCategory)]);
                if (! $mainCategoryModel) {
                    $failureCount++;
                    continue;
                }
                $mainCategoryId = $mainCategoryModel->id;

                $vendor_main_category_data = VendorMainCategoryData::findOne([
                    'main_category_id'  => $mainCategoryId,
                    'vendor_details_id' => $vendorId,
                ]);

                if (! empty($vendor_main_category_data)) {

                    // 🧭 Step 2: Service Type (prevent duplicates)
                    $serviceType = \app\modules\admin\models\ServiceType::findOne([
                        'main_category_id' => $mainCategoryId,
                        'type'             => $serviceTypeVal,
                    ]);
                    if (! $serviceType) {
                        $serviceType                   = new \app\modules\admin\models\ServiceType();
                        $serviceType->main_category_id = $mainCategoryId;
                        $serviceType->type             = $serviceTypeVal;

                        $serviceType->status = \app\modules\admin\models\ServiceType::STATUS_ACTIVE;
                        $serviceType->save(false);
                    }

                    // 🧭 Step 2: Service Type (prevent duplicates)
                    $VendorserviceType = StoreServiceTypes::findOne([
                        'main_category_id' => $mainCategoryId,
                        'type'             => $serviceTypeVal,
                        'store_id'         => $vendorId,
                    ]);
                    if (! $VendorserviceType) {
                        $VendorserviceType                   = new StoreServiceTypes();
                        $VendorserviceType->main_category_id = $mainCategoryId;
                        $VendorserviceType->service_type_id  = $serviceType->id;
                        $VendorserviceType->image            = $serviceType->image ?? ''; // Use service type image if available
                        $VendorserviceType->store_id         = $vendorId;
                        $VendorserviceType->type             = $serviceTypeVal;
                        $VendorserviceType->status           = StoreServiceTypes::STATUS_ACTIVE;
                        $VendorserviceType->save(false);
                    }

                    // 🧭 Step 3: SubCategory (prevent duplicates)
                    $slug        = \app\models\User::generateUniqueSlug($subCategoryTitle . $vendorId, $vendorId);
                    $subCategory = \app\modules\admin\models\SubCategory::findOne([
                        'main_category_id'      => $mainCategoryId,
                        'vendor_details_id'     => $vendorId,
                        'service_type_id'       => $serviceType->id,
                        'store_service_type_id' => $VendorserviceType->id,
                        'title'                 => trim($subCategoryTitle),
                    ]);
                    if (! $subCategory) {
                        $subCategory                        = new SubCategory();
                        $subCategory->main_category_id      = $mainCategoryId;
                        $subCategory->vendor_details_id     = $vendorId;
                        $subCategory->store_service_type_id = $VendorserviceType->id ?? '';
                        $subCategory->image                 = $serviceType->image ?? ''; // Use service type image if available
                        $subCategory->service_type_id       = $serviceType->id;
                        $subCategory->title                 = $subCategoryTitle;
                        $subCategory->slug                  = $slug;
                        $subCategory->status                = SubCategory::STATUS_ACTIVE;
                        $subCategory->save(false);
                    }
                    // 🧭 Step 4: Check for existing parent service first
                    $parentSlug = User::generateUniqueSlug($mainServiceName . $mainServiceDuration . $mainServicePrice . $serviceFor, $vendorId);

                    $parentService = Services::findOne([
                        'vendor_details_id' => $vendorId,
                        'sub_category_id'   => $subCategory->id,
                        'slug'              => $parentSlug,
                    ]);

                    // If not exists, create new parent
                    if (! $parentService) {
                        $parentService = new Services();
                    }
                    $parentService->vendor_details_id     = $vendorId;
                    $parentService->sub_category_id       = $subCategory->id;
                    $parentService->service_name          = $mainServiceName;
                    $parentService->description           = $mainServiceDesc;
                    $parentService->store_service_type_id = $VendorserviceType->id ?? '';
                    $parentService->multi_selection       = $multi_selection;
                    $parentService->price                 = ! empty($from_price) ? $from_price : $mainServicePrice;

                    $parentService->is_price_range = $is_price_range;
                    $parentService->from_price     = $from_price;
                    $parentService->to_price       = $to_price;

                    $parentService->duration          = $mainServiceDuration;
                    $parentService->home_visit        = $homeVisit;
                    $parentService->walk_in           = $walkIn;
                    $parentService->service_for       = $serviceFor;
                    $parentService->is_parent_service = $isParent ? 1 : '';
                    $parentService->type              = ($walkIn) ? Services::TYPE_WALK_IN : Services::TYPE_HOME_VISIT;
                    $parentService->slug              = $parentSlug;
                    $parentService->status            = Services::STATUS_ACTIVE;

                    if (! $parentService->save(false)) {
                        Yii::error("Parent service save failed: " . json_encode($parentService->errors));
                        $failureCount++;
                        continue;
                    }

                    // 🧭 Step 5: Handle child service only if parent was/is valid
                    if ($isParent && ! empty($childServiceName) && ! empty($childServicePrice) && ! empty($childServiceDuration)) {
                        // Check if child service already exists
                        $childServiceName     = trim($childServiceName);
                        $childServicePrice    = trim($childServicePrice);
                        $childServiceDuration = trim($childServiceDuration);
                        $childSlug            = User::generateUniqueSlug($childServiceName . $childServicePrice . $childServiceDuration, $vendorId);

                        $existingChild = Services::find()
                            ->where([
                                'vendor_details_id' => $vendorId,
                                'sub_category_id'   => $subCategory->id,
                                'parent_id'         => $parentService->id,
                                'slug'              => $childSlug,
                            ])
                            ->andWhere(['duration' => $childServiceDuration])
                            ->andWhere(['service_for' => $serviceFor])
                            ->one();

                        if ($existingChild) {
                            $childService = $existingChild;
                        } else {
                            $childService = new Services();
                        }

                        $childService->vendor_details_id     = $vendorId;
                        $childService->sub_category_id       = $subCategory->id;
                        $childService->service_name          = $childServiceName;
                        $childService->description           = $mainServiceDesc;
                        $childService->store_service_type_id = $VendorserviceType->id ?? '';
                        $childService->price                 = $childServicePrice;
                        $childService->duration              = $childServiceDuration;
                        $childService->home_visit            = $homeVisit;
                        $childService->walk_in               = $walkIn;
                        $childService->type                  = $parentService->type;
                        $childService->service_for           = $serviceFor;
                        $childService->parent_id             = $parentService->id;
                        $childService->is_parent_service     = '';
                        $childService->slug                  = $childSlug;
                        $childService->status                = Services::STATUS_ACTIVE;
                        $childService->save(false);
                    }
                }

                $successCount++;

                unset($rowData);
                gc_collect_cycles(); // force cleanup
            }

            unlink($path); // ✅ always clean up temp file

            return [
                'status'  => 'success',
                'message' => "Import complete: {$successCount} rows succeeded, {$failureCount} failed.",
            ];
        } catch (\Throwable $e) {
            Yii::error("Excel import error: " . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => "Error: " . $e->getMessage(),
            ];
        }
    }

    public function actionUploadServicessssExcel()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $vendorId = Yii::$app->request->post('vendor_details_id');
            $file     = UploadedFile::getInstanceByName('excel_file');

            if (! $file) {
                throw new \yii\web\BadRequestHttpException("No file uploaded.");
            }

            if (! in_array($file->extension, ['xlsx', 'xls'])) {
                throw new \yii\web\BadRequestHttpException("Invalid file format. Only .xlsx and .xls allowed.");
            }

            $uploadDir = Yii::getAlias('@runtime/tmp/');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $path = $uploadDir . uniqid('upload_') . '.' . $file->extension;
            if (! $file->saveAs($path)) {
                throw new \yii\web\ServerErrorHttpException("Failed to save uploaded file.");
            }

            $spreadsheet = IOFactory::load($path);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray();

            $header = array_map('trim', array_shift($sheetData)); // remove first row

            $expectedHeaders = [
                'main_category',
                'service_type',
                'sub_category',
                'main_services_name',
                'main_services_description',
                'main_services_price',
                'main_services_duration',
                'home_visit',
                'walk_in',
                'service_for',
                'is_parent_service',
                'child_services_name',
                'child_services_price',
                'child_services_duration',
            ];

            // Compare expected headers with actual headers
            $missingHeaders    = array_diff($expectedHeaders, $header);
            $unexpectedHeaders = array_diff($header, $expectedHeaders);

            if (! empty($missingHeaders) || ! empty($unexpectedHeaders)) {
                $errorMessage = "Invalid Excel format. Please ensure the file contains all required columns.\n";
                if (! empty($missingHeaders)) {
                    $errorMessage .= "Missing columns: " . implode(', ', $missingHeaders) . ".\n";
                }
                if (! empty($unexpectedHeaders)) {
                    $errorMessage .= "Unexpected columns: " . implode(', ', $unexpectedHeaders) . ".\n";
                }

                // Clean up uploaded file
                if (file_exists($path)) {
                    unlink($path);
                }

                return [
                    'status'  => 'error',
                    'message' => trim($errorMessage),
                ];
            }

            $rows         = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($sheetData as $row) {
                $rowData = array_combine($header, $row);

                // 🔁 Extract fields
                $mainCategory         = trim($rowData['main_category'] ?? '');
                $serviceTypeVal       = trim($rowData['service_type'] ?? '');
                $subCategoryTitle     = trim($rowData['sub_category'] ?? '');
                $mainServiceName      = trim($rowData['main_services_name'] ?? '');
                $mainServiceDesc      = trim($rowData['main_services_description'] ?? '');
                $mainServicePrice     = trim($rowData['main_services_price'] ?? '');
                $mainServiceDuration  = trim($rowData['main_services_duration'] ?? '');
                $homeVisit            = trim($rowData['home_visit'] ?? 0);
                $walkIn               = trim($rowData['walk_in'] ?? 0);
                $serviceFor           = trim($rowData['service_for'] ?? '');
                $isParent             = trim($rowData['is_parent_service'] ?? 0);
                $childServiceName     = trim($rowData['child_services_name'] ?? '');
                $childServicePrice    = trim($rowData['child_services_price'] ?? '');
                $childServiceDuration = trim($rowData['child_services_duration'] ?? '');

                // 🧭 Step 1: Main Category
                $mainCategoryModel = \app\modules\admin\models\MainCategory::findOne(['title' => trim($mainCategory)]);
                if (! $mainCategoryModel) {
                    $failureCount++;
                    continue;
                }
                $mainCategoryId = $mainCategoryModel->id;

                // 🧭 Step 2: Service Type (prevent duplicates)
                $serviceType = \app\modules\admin\models\ServiceType::findOne([
                    'main_category_id' => $mainCategoryId,
                    'type'             => $serviceTypeVal,
                ]);
                if (! $serviceType) {
                    $serviceType                   = new \app\modules\admin\models\ServiceType();
                    $serviceType->main_category_id = $mainCategoryId;
                    $serviceType->type             = $serviceTypeVal;
                    $serviceType->status           = \app\modules\admin\models\ServiceType::STATUS_ACTIVE;
                    $serviceType->save(false);
                }

                // 🧭 Step 3: SubCategory (prevent duplicates)
                $slug        = \app\models\User::generateUniqueSlug($subCategoryTitle . $vendorId, $vendorId);
                $subCategory = \app\modules\admin\models\SubCategory::findOne([
                    'main_category_id'  => $mainCategoryId,
                    'vendor_details_id' => $vendorId,
                    'service_type_id'   => $serviceType->id,
                    'title'             => trim($subCategoryTitle),
                ]);
                if (! $subCategory) {
                    $subCategory                    = new SubCategory();
                    $subCategory->main_category_id  = $mainCategoryId;
                    $subCategory->vendor_details_id = $vendorId;
                    $subCategory->service_type_id   = $serviceType->id;
                    $subCategory->title             = $subCategoryTitle;
                    $subCategory->slug              = $slug;
                    $subCategory->status            = SubCategory::STATUS_ACTIVE;
                    $subCategory->save(false);
                }

                // 🧭 Step 4: Check for existing parent service first
                $parentSlug = User::generateUniqueSlug($mainServiceName . $mainServiceDuration . $mainServicePrice . $serviceFor, $vendorId);

                $parentService = Services::findOne([
                    'vendor_details_id' => $vendorId,
                    'sub_category_id'   => $subCategory->id,
                    'slug'              => $parentSlug,
                ]);

                // If not exists, create new parent
                if (! $parentService) {
                    $parentService                    = new Services();
                    $parentService->vendor_details_id = $vendorId;
                    $parentService->sub_category_id   = $subCategory->id;
                    $parentService->service_name      = $mainServiceName;
                    $parentService->description       = $mainServiceDesc;
                    $parentService->price             = $mainServicePrice;
                    $parentService->duration          = $mainServiceDuration;
                    $parentService->home_visit        = $homeVisit;
                    $parentService->walk_in           = $walkIn;
                    $parentService->service_for       = $serviceFor;
                    $parentService->is_parent_service = $isParent ? 1 : 0;
                    $parentService->type              = ($homeVisit && $walkIn) ? Services::TYPE_HOME_VISIT : Services::TYPE_WALK_IN;
                    $parentService->slug              = $parentSlug;
                    $parentService->status            = Services::STATUS_ACTIVE;

                    if (! $parentService->save(false)) {
                        Yii::error("Parent service save failed: " . json_encode($parentService->errors));
                        $failureCount++;
                        continue;
                    }
                }

                // 🧭 Step 5: Handle child service only if parent was/is valid
                if ($isParent && ! empty($childServiceName)) {
                    $childSlug = User::generateUniqueSlug($childServiceName . $childServicePrice . $childServiceDuration, $vendorId);

                    $existingChild = Services::find()
                        ->where([
                            'vendor_details_id' => $vendorId,
                            'sub_category_id'   => $subCategory->id,
                            'parent_id'         => $parentService->id,
                            'slug'              => $childSlug,
                        ])
                        ->andWhere(['duration' => $childServiceDuration])
                        ->andWhere(['service_for' => $serviceFor])
                        ->one();

                    if ($existingChild) {
                        Yii::info("Skipped duplicate child service: $childServiceName");
                    } else {
                        $childService                    = new Services();
                        $childService->vendor_details_id = $vendorId;
                        $childService->sub_category_id   = $subCategory->id;
                        $childService->service_name      = $childServiceName;
                        $childService->description       = $mainServiceDesc;
                        $childService->price             = $childServicePrice;
                        $childService->duration          = $childServiceDuration;
                        $childService->home_visit        = $homeVisit;
                        $childService->walk_in           = $walkIn;
                        $childService->service_for       = $serviceFor;
                        $childService->parent_id         = $parentService->id;
                        $childService->is_parent_service = 0;
                        $childService->type              = ($homeVisit && $walkIn) ? Services::TYPE_HOME_VISIT : Services::TYPE_WALK_IN;
                        $childService->slug              = $childSlug;
                        $childService->status            = Services::STATUS_ACTIVE;
                        $childService->save(false);
                    }
                }

                $successCount++;
            }

            if (file_exists($path)) {
                unlink($path);
            }

            return [
                'status'  => 'success',
                'message' => "Import complete: {$successCount} rows succeeded, {$failureCount} failed.",
            ];
        } catch (\Throwable $e) {
            Yii::error("Excel import error: " . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => "Error: " . $e->getMessage(),
            ];
        }
    }

    public function actionShopLocations()
    {
        $model = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE])->all();
        return $this->render('shop-locations', [
            'model' => $model,
        ]);
    }
    public function actionSoftDeleteMultiple()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('ids', []);
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $model = VendorDetails::findOne($id);
                if ($model) {
                    $model->status = VendorDetails::STATUS_DELETE;
                    $model->save(false);
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'error', 'message' => 'No IDs received'];
    }

    /**
     * Toggle allow order approval status for vendor
     */
    public function actionToggleOrderApproval()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (! Yii::$app->request->isPost) {
            return [
                'status'  => 'error',
                'message' => 'Invalid request method',
            ];
        }

        $userId             = Yii::$app->request->post('id'); // vendor = user
        $allowOrderApproval = Yii::$app->request->post('allow_order_approval');

        if (empty($userId)) {
            return [
                'status'  => 'error',
                'message' => 'User ID is required',
            ];
        }

        // 🔑 check that the logged-in admin is allowed to update this vendor
        $currentUserId = Yii::$app->user->id;

        // If only admins can change for others:
        if (! User::isAdmin() && $currentUserId != $userId) {
            return [
                'status'  => 'error',
                'message' => 'Not authorized to update this vendor',
            ];
        }

        $model = User::findOne(['id' => $userId]);
        if (! $model) {
            return [
                'status'  => 'error',
                'message' => 'Vendor not found',
            ];
        }

        $model->allow_order_approval = ($allowOrderApproval === '1') ? 1 : 0;

        if ($model->save(false)) {
            return [
                'status'               => 'success',
                'message'              => 'Order approval setting updated successfully',
                'allow_order_approval' => $model->allow_order_approval,
            ];
        }

        return [
            'status'  => 'error',
            'message' => 'Failed to update order approval setting',
        ];
    }

    public function actionUpdateVendorDetails()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request                    = Yii::$app->request;

        if (! $request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $id    = $request->post('id');
        $model = VendorDetails::findOne($id);

        if (! $model) {
            return ['success' => false, 'message' => 'Vendor not found'];
        }

        // Update fields
        $model->business_name  = $request->post('business_name');
        $model->avg_rating     = $request->post('avg_rating');
        $model->commission     = $request->post('commission');
        $model->gender_type    = $request->post('gender_type');
        $model->gst_number     = $request->post('gst_number');
        $model->account_number = $request->post('account_number');
        $model->ifsc_code      = $request->post('ifsc_code');

        // Contact number belongs to related User model
        $contactNo = $request->post('contact_no');
        if ($model->user) {
            $model->user->contact_no = $contactNo;
            $model->user->save(false);
        }

        if ($model->save(false)) {
            return [
                'success' => true,
                'data'    => [
                    'business_name'         => Html::encode($model->business_name),
                    'contact_no'            => $model->user ? Html::encode($model->user->contact_no) : 'Not Available',
                    'avg_rating'            => Html::encode($model->avg_rating),
                    'commission'            => Html::encode($model->commission),
                    'commission_type_badge' => $model->getCommissionTypeBadge(),
                    'gender_badge'          => $model->getGenderBadge(),
                    'gst_number'            => Html::encode($model->gst_number),
                    'account_number'        => Html::encode($model->account_number),
                    'ifsc_code'             => Html::encode($model->ifsc_code),
                ],
            ];
        }

        return ['success' => false, 'message' => 'Failed to update vendor'];
    }

    public function actionDownloadExample()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $expectedHeaders = [
            'first_name',
            'last_name',
            'username',
            'contact_no',
            'email',
            'business_name',
            'description',
            'website_link',
            'latitude',
            'longitude',
            'address',
            'gst_number',
            'logo',
            'account_number',
            'ifsc_code',
        ];

        // Write headers in first row
        $col = 1;
        foreach ($expectedHeaders as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }

        // Make headers bold
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($expectedHeaders));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        // Optional: Auto-size columns
        for ($i = 1; $i <= count($expectedHeaders); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        // Output file
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'sample-vendorstore-upload-example.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
