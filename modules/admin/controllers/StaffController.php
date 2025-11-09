<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\VendorDetails;
use Yii;
use app\models\User;
use app\modules\admin\models\Staff;
use app\modules\admin\models\search\StaffSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StaffController implements the CRUD actions for Staff model.
 */


class StaffController extends Controller
{

    public $userRole;

    public function __construct($id, $module, $config = [])
    {
        $this->userRole = \Yii::$app->user->identity->user_role;
        parent::__construct($id, $module, $config);
    }
    public $adminRoles = [User::ROLE_ADMIN, User::ROLE_SUBADMIN, User::ROLE_QA,User::ROLE_VENDOR];


    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view','create-vendor', 'create', 'update', 'delete', 'update-status'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isSubAdmin()||  User::isVendor()||User::isQa();
                        }
                       
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'pdf', 'update-status'],
                        'matchCallback' => function () {
                            return User::isManager();
                        }
                    ],
                    [
                        'allow' => false
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all Staff models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StaffSearch();
         if (in_array($this->userRole, $this->adminRoles)) {

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else if(\Yii::$app->user->identity->user_role==User::ROLE_MANAGER){
            $dataProvider = $searchModel->managersearch(Yii::$app->request->queryParams);
        }

        if(User::isAdmin()){
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        }else if(User::isVendor()){

              return $this->render('vendor_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

        }
    
    }



    /**
     * Displays a single Staff model.
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
     * Creates a new Staff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
 public function actionCreate()
{
    $model = new Staff();
    $transaction = Yii::$app->db->beginTransaction();

    try {
        if ($model->load(Yii::$app->request->post())) {
            if (User::isAdmin()) {
                $vendorDetails = VendorDetails::findOne(['id' => $model->vendor_details_id]);
            } else {
                $vendorDetails = VendorDetails::findOne(['user_id' => Yii::$app->user->id]);

                if (!$vendorDetails) {
                    Yii::$app->session->setFlash('error', 'Vendor information not found.');
                    return $this->redirect(['index']);
                }
                $model->vendor_details_id = $vendorDetails->id;
            }
            if (!$vendorDetails) {
                Yii::$app->session->setFlash('error', 'Vendor details not found.');
                return $this->redirect(['index']);
            }

            // Check for duplicate mobile
            if (Staff::find()->where(['mobile_no' => $model->mobile_no])->exists()) {
                Yii::$app->session->setFlash('error', 'A staff member with this mobile number already exists.');
                return $this->redirect(['index']);
            }

            // Check for duplicate email
            if (!empty($model->email) && Staff::find()->where(['email' => $model->email])->exists()) {
                Yii::$app->session->setFlash('error', 'A staff member with this email already exists.');
                return $this->redirect(['index']);
            }

            // Create user for staff
            $user = new User();
            $user->username = $model->mobile_no . '@' . $model->role . '.com';
            $user->email = $model->email;
            $user->first_name = $model->full_name;
            $user->contact_no = $model->mobile_no;
            $user->date_of_birth = $model->dob;
            $user->gender = $model->gender;
            $user->user_role = $model->role;

            if (!$user->save(false)) {
                Yii::$app->session->setFlash('error', 'Failed to create user for staff.');
                Yii::error("User save error: " . json_encode($user->getErrors()));
                return $this->redirect(['index']);
            }

            $model->user_id = $user->id;
            $model->current_status = Staff::CURRENT_STATUS_IDLE;
            $model->status = 1;
            $model->create_user_id = Yii::$app->user->id;
            $model->created_on = date('Y-m-d H:i:s');

            // Profile image upload
            $file = \yii\web\UploadedFile::getInstance($model, 'profile_image');
            if ($file) {
                $image = Yii::$app->notification->imageKitUpload($file);
                if (isset($image['url'])) {
                    $model->profile_image = $image['url'];
                }
            }

            // Save staff
            if ($model->save()) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Staff added successfully.');
                return $this->redirect(['index']);
            } else {
                Yii::error("Staff save error: " . json_encode($model->getErrors()));
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save staff. Please check required fields.');
            }
        }
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Exception during staff creation: " . $e->getMessage());
        Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
    }

    return $this->render('create', ['model' => $model]);
}

public function actionCreateVendor()
{
    if (!User::isVendor()) {
        throw new \yii\web\ForbiddenHttpException("Only vendors can access this page.");
    }

    $model = new Staff();
    $transaction = Yii::$app->db->beginTransaction();

    try {
        if ($model->load(Yii::$app->request->post())) {
            // Get vendor details of logged-in vendor
            $vendorDetails = VendorDetails::findOne(['user_id' => Yii::$app->user->id]);

            if (!$vendorDetails) {
                Yii::$app->session->setFlash('error', 'Vendor information not found.');
                return $this->redirect(['index']);
            }

            // Assign vendor
            $model->vendor_details_id = $vendorDetails->id;

            // Duplicate mobile number check
            if (Staff::find()->where(['mobile_no' => $model->mobile_no])->exists()) {
                Yii::$app->session->setFlash('error', 'A staff member with this mobile number already exists.');
                return $this->redirect(['index']);
            }

            // Duplicate email check
            if (!empty($model->email) && Staff::find()->where(['email' => $model->email])->exists()) {
                Yii::$app->session->setFlash('error', 'A staff member with this email already exists.');
                return $this->redirect(['index']);
            }

            // Create user for the staff
            $user = new User();
            $user->username = $model->mobile_no . '@' . $model->role . '.com';
            $user->email = $model->email;
            $user->first_name = $model->full_name;
            $user->contact_no = $model->mobile_no;
            $user->date_of_birth = $model->dob;
            $user->gender = $model->gender;
            $user->user_role = $model->role;

            if (!$user->save(false)) {
                Yii::$app->session->setFlash('error', 'Failed to create user for staff.');
                Yii::error("User save error: " . json_encode($user->getErrors()));
                return $this->redirect(['index']);
            }

            $model->user_id = $user->id;
            $model->current_status = Staff::CURRENT_STATUS_IDLE;
            $model->status = 1; // Or your default active status
            $model->create_user_id = Yii::$app->user->id;
            $model->created_on = date('Y-m-d H:i:s');

            // Upload profile image
            $file = \yii\web\UploadedFile::getInstance($model, 'profile_image');
            if ($file) {
                $image = Yii::$app->notification->imageKitUpload($file);
                if (isset($image['url'])) {
                    $model->profile_image = $image['url'];
                }
            }

            // Save staff
            if ($model->save()) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Staff added successfully.');
                return $this->redirect(['index']);
            } else {
                Yii::error("Staff save error: " . json_encode($model->getErrors()));
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save staff. Please check required fields.');
            }
        }
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Exception during staff creation: " . $e->getMessage());
        Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
    }

    return $this->render('create_vendor', ['model' => $model]);
}



    
public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $vendor_details_id = Yii::$app->request->get('vendor_details_id', null);

    if (!empty($vendor_details_id)) {
        $model->vendor_details_id = $vendor_details_id;
    }

    $redirectUrl = !empty($vendor_details_id)
        ? ['/admin/vendor-details/view', 'id' => $vendor_details_id]
        : ['index'];
    $oldEmail = $model->email;
    $oldMobile = $model->mobile_no;

    $postData = Yii::$app->request->post();

    if ($model->load($postData)) {
        $newEmail = $model->email;
        $newMobile = $model->mobile_no;

        if ($newEmail === $oldEmail && $newMobile === $oldMobile) {
            Yii::$app->session->setFlash('error', 'A staff member with the same email or mobile number already exists.');
            return $this->render('update', ['model' => $model]);
        }
        $emailExists = Staff::find()
            ->where(['email' => $newEmail])
            ->andWhere(['<>', 'id', $model->id])
            ->exists();

        if ($emailExists) {
            $model->addError('email', 'A staff member with the same email already exists.');
            return $this->render('update', ['model' => $model]);
        }
        $mobileExists = Staff::find()
            ->where(['mobile_no' => $newMobile])
            ->andWhere(['<>', 'id', $model->id])
            ->exists();

        if ($mobileExists) {
            $model->addError('mobile_no', 'A staff member with the same mobile number already exists.');
            return $this->render('update', ['model' => $model]);
        }

        $file = \yii\web\UploadedFile::getInstance($model, 'profile_image');
        if ($file) {
            $image = Yii::$app->notification->imageKitUpload($file);
            if (isset($image['url'])) {
                $model->profile_image = $image['url'];
            }
        } else {
            $model->profile_image = $model->getOldAttribute('profile_image');
        }
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Staff details updated successfully.');
            return $this->redirect($redirectUrl);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update staff details. Please try again.');
        }
    }

    return $this->render('update', ['model' => $model]);
}




    /**
     * Deletes an existing Staff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
      
        $model = $this->findModel($id);
        if(!empty($model)){
            $model->status = Staff::STATUS_DELETE;
            $model->save(false); 
        }

        return $this->redirect(['index']);
    }
    
    public function actionUpdateStatus(){
		$data =[];
		$post = \Yii::$app->request->post();
		\Yii::$app->response->format = 'json';
		if (! empty ( $post ['id'] ) ) {
			$model = Staff::find()->where([
				'id' => $post['id'],
			])->one();
			if(!empty($model)){

                $model->status = $post['val'];
              
               
			}
			if($model->save(false)){
				$data['message'] = "Updated";
                $data['id'] = $model->status ;
			}else{
				$data['message'] = "Not Updated";
                
			}

	}
	return $data;
}

    
    /**
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Staff::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
