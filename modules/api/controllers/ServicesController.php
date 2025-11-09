<?php

namespace app\modules\api\controllers;

use app\components\AuthSettings;
use app\models\User;
use app\modules\api\controllers\BKController;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\ArrayHelper;
use app\modules\admin\models\Banner;
use app\modules\admin\models\BannerChargeLogs;
use app\modules\admin\models\BannerTimings;
use app\modules\admin\models\ComboPackages;
use app\modules\admin\models\ComboServices;
use app\modules\admin\models\ProductServices;
use app\modules\admin\models\Services;
use app\modules\admin\models\ServiceType;
use app\modules\admin\models\StoreServiceTypes;
use app\modules\admin\models\SubCategory;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorMainCategoryData;
use DateTime;
use Exception;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class ServicesController extends BKController

{

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
                            'vendor-selected-main-category',
                            'vendor-service-types',
                            'services-list',
                            'change-service-status',
                            'view-services-by-id',
                            'get-service-types',
                            'add-or-update-service-type',
                            'add-or-update-service',
                            'services-list-for-combo',
                            'add-or-update-combo-service',
                            'list-combo-packages',
                            'view-combo-services',
                            'change-combo-package-status',
                            'get-sub-categories',
                            'add-or-update-sub-category'





                        ],

                        'allow' => true,

                        'roles' => [

                            '@'

                        ]

                    ],

                    [

                        'actions' => [
                            'vendor-selected-main-category',
                            'vendor-service-types',
                            'services-list',
                            'change-service-status',
                            'view-services-by-id',
                            'get-service-types',
                            'add-or-update-service-type',
                            'add-or-update-service',
                            'services-list-for-combo',
                            'add-or-update-combo-service',
                            'list-combo-packages',
                            'view-combo-services',
                            'change-combo-package-status',
                            'get-sub-categories',
                            'add-or-update-sub-category'

                        ],

                        'allow' => true,

                        'roles' => [

                            '?',

                            '*',

                            //'@' 

                        ]

                    ]

                ]

            ]

        ]);
    }



    public function actionVendorSelectedMainCategory()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }

            $vendor_main_category_data = VendorMainCategoryData::find()
                ->where(['vendor_details_id' => $shop->id])
                ->andWhere(['status' => VendorMainCategoryData::STATUS_ACTIVE])
                ->all();

            $list = [];
            if (! empty($vendor_main_category_data)) {
                foreach ($vendor_main_category_data as $index => $item) {
                    $jsonItem          = $item->asJson();
                    $jsonItem['index'] = $index;
                    $list[]            = $jsonItem;
                }
            }

            if (empty($list)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No main category found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }




    public function actionVendorServiceTypes()
    {
        $data             = [];
        $headers          = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth             = new AuthSettings();
        $user_id          = $auth->getAuthSession($headers);
        $post             = Yii::$app->request->post();
        $main_category_id = $post['main_category_id'] ?? null;

        try {
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t("app", "User authentication failed. Please log in."));
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException(Yii::t("app", "No shop details found for this user."));
            }


            $vendor_service_types = StoreServiceTypes::find()
                ->where(['store_id' => $shop->id])
                ->andWhere(['main_category_id' => $main_category_id])
                ->andWhere(['in', 'status', [StoreServiceTypes::STATUS_ACTIVE, StoreServiceTypes::STATUS_INACTIVE]])
                ->all();

            $list = [];

            if (! empty($vendor_service_types)) {
                foreach ($vendor_service_types as $vendor_service_type) {

                    // Count related services (active + inactive only)
                    $serviceCount = Services::find()
                        ->where(['store_service_type_id' => $vendor_service_type->id])
                        ->andWhere(['in', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE, Services::STATUS_ADMIN_WAITING_FOR_APPROVAL]])
                        ->count();

                    // Only add categories that have at least 1 service
                    if ($serviceCount > 0) {
                        $list[] = $vendor_service_type->asJsonVendor();
                    }
                }
            }

            if (empty($list)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No service types found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "An unexpected error occurred: {message}", ['message' => $e->getMessage()]);
        }

        return $this->sendJsonResponse($data);
    }





    public function actionServicesList()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch required fields
            $sub_category_id = ! empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
            $home_visit      = ! empty($post['home_visit']) ? $post['home_visit'] : null;
            $walk_in         = ! empty($post['walk_in']) ? $post['walk_in'] : null;

            // Validate required fields
            if (empty($sub_category_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'Subcategory ID is required.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and subcategory
            $query = Services::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['sub_category_id' => $sub_category_id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE, Services::STATUS_ADMIN_WAITING_FOR_APPROVAL]])
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ]);

            // Apply filters only if values are provided
            if ($home_visit !== null) {
                $query->andWhere(['home_visit' => 1]);
            }

            if ($walk_in !== null) {
                $query->andWhere(['walk_in' => 1]);
            }

            $services = $query->all();
            // Check if services exist
            if (empty($services)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No services found for the selected subcategory.');
            } else {
                // Format service data
                $list = [];
                foreach ($services as $service) {
                    $list[] = $service->asJson();
                }

                // Prepare success response
                $data['status']   = self::API_OK;
                $data['message']  = Yii::t('app', 'Services retrieved successfully.');
                $data['services'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }



    public function actionChangeServiceStatus()
    {

        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized. Please login to continue.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found. Please complete your vendor profile.'));
            }

            // Fetch required fields
            $services_id = ! empty($post['services_id']) ? $post['services_id'] : null;
            $status      = ! empty($post['status']) ? $post['status'] : null;

            // Validate required fields
            if (empty($services_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service ID and status is required.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and service ID
            $services = Services::findOne(['id' => $services_id, 'vendor_details_id' => $vendor_details_id]);

            if (empty($services)) {
                throw new NotFoundHttpException(Yii::t('app', 'Service not found or you do not have permission to delete this service.'));
            }

            // Mark the service as deleted
            $services->status = $status;
            if ($services->save(false)) {
                $data['status']  = self::API_OK;
                $data['details'] = $services;
                $data['message'] = Yii::t('app', 'Service status changed successfully.');
            } else {
                throw new ServerErrorHttpException(Yii::t('app', 'Failed to change the service status. Please try again later.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (ServerErrorHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }



    public function actionViewServicesById()
    {

        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed. Please log in."),
            ]);
        }

        try {
            $service_id = $post['service_id'] ?? null;

            if (empty($service_id)) {
                throw new \yii\web\BadRequestHttpException(Yii::t("app", "service id is required."));
            }

            $vendorDetails = VendorDetails::findOne([
                'user_id' => $user_id
            ]);

            if (! $vendorDetails) {
                throw new \yii\web\NotFoundHttpException(Yii::t("app", "Active vendor not found."));
            }

            $services = Services::find()
                ->where([
                    'vendor_details_id' => $vendorDetails->id,
                    'id'                => $service_id,
                ])

                ->one();

            $data['status']  = self::API_OK;
            $data['details'] = $services->asJson();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t("app", "Unexpected error: ") . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }



    public function actionGetServiceTypes()
    {
        $data             = [];
        $post             = Yii::$app->request->post();
        $headers          = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth             = new AuthSettings();
        $user_id          = $auth->getAuthSession($headers);
        $main_category_id = $post['main_category_id'] ?? null;
        $search           = $post['search'] ?? null;

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Get vendor details
            $vendorDetails = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendorDetails) {
                throw new BadRequestHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch ServiceTypes 
            $serviceTypes = ServiceType::find()
                ->where(['status' => ServiceType::STATUS_ACTIVE])
                ->andWhere(['main_category_id' => $main_category_id]);
                if(!empty($search)){
                    $serviceTypes = $serviceTypes->andWhere(['like', 'type', $search]);
                }
               $serviceTypes = $serviceTypes->all();

            if (empty($serviceTypes)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No service types found for the selected main categories.');
            } else {
                $list = [];
                foreach ($serviceTypes as $serviceType) {
                    $list[] = $serviceType->asJson(); // Assuming asJson() exists
                }

                $data['status']        = self::API_OK;
                $data['message']       = Yii::t('app', 'Service types retrieved successfully.');
                $data['service_types'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (\Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


    public function actionAddOrUpdateServiceType()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            $service_type_id  = isset($post['service_type_id']) ? $post['service_type_id'] : null;
            $main_category_id = isset($post['main_category_id']) ? $post['main_category_id'] : null;

            if (empty($service_type_id) || empty($main_category_id)) {
                throw new BadRequestHttpException(Yii::t('app', 'main_category_id and service_type_id are required.'));
            }

            $vendor_details_id = $vendor->id;

            // Fetch service type
            $serviceType = ServiceType::find()->where(['id' => $service_type_id])->one();
            if (! $serviceType) {
                throw new BadRequestHttpException(Yii::t('app', 'Invalid service type.'));
            }

            // Check if combination already exists
            $existing = StoreServiceTypes::find()
                ->where(['store_id' => $vendor_details_id])
                ->andWhere(['service_type_id' => $service_type_id])
                ->andWhere(['main_category_id' => $main_category_id])
                ->andWhere(['type' => $serviceType->type])
                ->one();

            if (empty($existing)) {
            $store_service_types    = new StoreServiceTypes();

            }

            // Create new StoreServiceTypes entry
            $store_service_types->store_id         = $vendor_details_id;
            $store_service_types->service_type_id  = $service_type_id;
            $store_service_types->main_category_id = $main_category_id;
            $store_service_types->type             = $serviceType->type;
            $store_service_types->image            = $serviceType->image;
            $store_service_types->status           = StoreServiceTypes::STATUS_ACTIVE;

            if ($store_service_types->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service type saved successfully.');
                $data['details'] = $store_service_types->asJson();
            } else {
                throw new Exception(Yii::t('app', 'Failed to save service type.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }




    public function actionAddOrUpdateService()
    {
        $data    = [];
        $request           = Yii::$app->request;


        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            $rawBody = $request->getRawBody();      // Get raw JSON string
            $post    = json_decode($rawBody, true);

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Fetch required fields

            $services_id           = ! empty($post['id']) ? $post['id'] : null;
            $service_name          = ! empty($post['service_name']) ? $post['service_name'] : null;
            $image                 = ! empty($post['image']) ? $post['image'] : null;
            $price                 = ! empty($post['price']) ? $post['price'] : null;
            $sub_category_id       = ! empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
            $duration              = ! empty($post['duration']) ? $post['duration'] : null;
            $home_visit            = isset($post['home_visit']) ? $post['home_visit'] : null;
            $walk_in               = ! empty($post['walk_in']) ? $post['walk_in'] : null;
            $service_for           = ! empty($post['service_for']) ? $post['service_for'] : null;
            $description           = ! empty($post['description']) ? $post['description'] : null;
            $is_parent_service     = ! empty($post['is_parent_service']) ? $post['is_parent_service'] : 0;
            $store_service_type_id = ! empty($post['store_service_type_id']) ? $post['store_service_type_id'] : null;
            $is_price_range        = ! empty($post['is_price_range']) ? $post['is_price_range'] : 0;
            $from_price            = ! empty($post['from_price']) ? $post['from_price'] : 0;
            $to_price              = ! empty($post['to_price']) ? $post['to_price'] : 0;
            $multi_selection       = ! empty($post['multi_selection']) ? $post['multi_selection'] : false; 
            $is_sessions_required   = $post['is_sessions_required'] ?? 0;
            $is_product_required = $post['is_product_required'] ?? 0;
            $sub_services_json = $post['sub_services_json'] ?? '';




            // Validate required fields
            if (empty($service_name)) {
                throw new BadRequestHttpException(Yii::t('app', 'Service name is required.'));
            }



            if ($is_parent_service != 1) {
                if (empty($duration)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Duration is required.'));
                }

                if (is_null($home_visit) && is_null($walk_in)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Either Home Visit or Walk-in must be selected.'));
                }

                if (! empty($home_visit) && ! empty($walk_in)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Only one option allowed: either Home Visit or Walk-in.'));
                }

                if (empty($service_for)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Service for is required.'));
                }

                if (empty($description)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Description is required.'));
                }
            }
            if ($is_price_range == 1) {
                // Both empty → throw error
                if (empty($from_price)) {
                    throw new BadRequestHttpException(Yii::t('app', 'Either from_price  is required when price range is enabled.'));
                }

                // If both are provided → validate their relationship
                if (! empty($from_price) && ! empty($to_price) && $from_price >= $to_price) {
                    throw new BadRequestHttpException(Yii::t('app', 'from_price must be less than to_price.'));
                }
            }



            // Generate unique slug
            $vendor_details_id = $vendor->id;
            $slug = User::generateUniqueSlug($service_name . $duration . $price . $service_for, $vendor_details_id);

            if (! empty($services_id)) {
                $services = Services::find()->where([
                    'id' => $services_id,
                ])->one();
            }

            // If the service doesn't exist, create a new one
            if (empty($services)) {
                $services = new Services();
            }

            // Populate the service details
            $services->vendor_details_id     = $vendor_details_id;
            $services->sub_category_id       = $sub_category_id;
            $services->slug                  = $slug;
            $services->store_service_type_id = $store_service_type_id;
            $services->multi_selection       = $multi_selection;
            $services->service_name          = $service_name;
            $services->image                 = $image;
            $services->price                 = ! empty($from_price) ? $from_price : $price;
            $services->from_price            = $from_price;
            $services->to_price              = $to_price;
            $services->is_price_range        = $is_price_range;
            $services->duration              = $duration;
            $services->home_visit            = $home_visit;
            $services->walk_in               = $walk_in;
            $services->is_product_required   = $is_product_required;
            $services->is_parent_service     = $is_parent_service;
            $services->is_sessions_required   = $is_sessions_required;
            if (! empty($home_visit)) {
                $services->type = Services::TYPE_HOME_VISIT;
            } elseif (! empty($walk_in)) {
                $services->type = Services::TYPE_WALK_IN;
            }

            $services->description = $description;
            $services->service_for = $service_for;
            $services->status      = Services::STATUS_ADMIN_WAITING_FOR_APPROVAL;

            // Save the service details
            if ($services->save(false)) {

                if (!empty($product_ids_json_decode_parent)) {
                    foreach ($product_ids_json_decode_parent as $productData) {

                        $product_services = ProductServices::findOne(['service_id' => $services->id, 'product_id' => $productData->product_id]);

                        if (empty($product_services)) {
                            $product_services = new ProductServices();
                        }

                        $product_services->service_id = $services->id;
                        $product_services->product_id = $productData->product_id;
                        $product_services->uom_id  = $productData->uom_id;
                        $product_services->quantity = $productData->quantity;
                        $product_services->status = ProductServices::STATUS_ACTIVE;
                        $product_services->save(false);
                    }
                }


                if ($services->is_parent_service == 1) {
                    // ❌ Disallow sub-services when price range is used
                    if (! empty($services->to_price)) {
                        throw new BadRequestHttpException(Yii::t('app', 'Child services cannot be added when a price range is used (to_price must be empty).'));
                    }


                    if (empty($sub_services_json)) {
                        throw new BadRequestHttpException(Yii::t('app', 'Sub services data is required for parent service.'));
                    }

                    $sub_services_json_decode = $sub_services_json;

                    if (! is_array($sub_services_json_decode)) {
                        throw new BadRequestHttpException(Yii::t('app', 'Invalid sub services format.'));
                    }

                    $servicesChildDeleteSTate = Services::find()->where([
                        'vendor_details_id' => $vendor_details_id,
                        'sub_category_id'   => $sub_category_id,
                        'parent_id'         => $services->id,
                    ])->all();

                    if (! empty($servicesChildDeleteSTate)) {
                        foreach ($servicesChildDeleteSTate as $servicesChildDeleteSTateData) {
                            $servicesChildDeleteSTateData->status = Services::STATUS_DELETE;
                            $servicesChildDeleteSTateData->save(false);
                        }
                    }


                    foreach ($sub_services_json_decode as $index => $childData) {
                        // ✅ Validate required fields
                        if (empty($childData['service_name'])) {
                            throw new BadRequestHttpException(Yii::t('app', "Sub service name is required at item {$index}."));
                        }

                        if (empty($childData['price'])) {
                            throw new BadRequestHttpException(Yii::t('app', "Price is required at sub service {$childData['service_name']}."));
                        }







                        // ✅ Generate slug
                        $slugChild = User::generateUniqueSlug($childData['service_name'] . $childData['price'] . ($childData['duration'] ?? 0) . $service_for, $vendor_details_id);

                        // ✅ Check if exists
                        $servicesChild = Services::find()->where([
                            'vendor_details_id' => $vendor_details_id,
                            'sub_category_id'   => $sub_category_id,
                            'slug'              => $slugChild,
                            'parent_id'         => $services->id,
                        ])->one();

                        if (empty($servicesChild)) {
                            $servicesChild = new Services();
                        }

                        // ✅ Populate child service
                        $servicesChild->vendor_details_id     = $vendor_details_id;
                        $servicesChild->sub_category_id       = $sub_category_id;
                        $servicesChild->slug                  = $slugChild;
                        $servicesChild->store_service_type_id = $store_service_type_id;

                        $servicesChild->service_name = $childData['service_name'];
                        $servicesChild->price        = $childData['price'];

                        $servicesChild->image       = $image;
                        $servicesChild->duration    = $duration ?? '';
                        $servicesChild->home_visit  = $home_visit;
                        $servicesChild->walk_in     = $walk_in;
                        $servicesChild->parent_id   = $services->id;
                        $servicesChild->type        = $services->type;
                        $servicesChild->description = $childData->description ?? null;
                        $servicesChild->service_for = $service_for;
                        $servicesChild->is_sessions_required = $is_sessions_required;
                        $servicesChild->is_product_required = $is_product_required;
                        $servicesChild->status      = Services::STATUS_ACTIVE;
                        $servicesChild->save(false);
                    }
                }

                $data['status']  = self::API_OK;
                $data['message'] = Yii::t('app', 'Service added or updated successfully.');
                $data['details'] = $services->asJson();
            } else {
                throw new Exception(Yii::t('app', 'Failed to save the service.'));
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }




    public function actionServicesListForCombo()
    {
        $data    = [];
        $post    = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);

        try {
            // Ensure user is authenticated
            if (empty($user_id)) {
                throw new UnauthorizedHttpException(Yii::t('app', 'User not found or unauthorized.'));
            }

            // Fetch vendor details
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (empty($vendor)) {
                throw new NotFoundHttpException(Yii::t('app', 'Vendor details not found.'));
            }

            // Get vendor details ID
            $vendor_details_id = $vendor->id;

            // Fetch services based on vendor and subcategory
            $services = Services::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->andWhere(['IN', 'status', [Services::STATUS_ACTIVE, Services::STATUS_INACTIVE]]) // include both
                ->andWhere([
                    'or',
                    ['parent_id' => null],
                    ['parent_id' => ''],
                ])
                ->all();

            // Check if services exist
            if (empty($services)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t('app', 'No services found for the selected subcategory.');
            } else {
                // Format service data
                $list = [];
                foreach ($services as $service) {
                    $list[] = $service->asJsonForCombo();
                }

                // Prepare success response
                $data['status']   = self::API_OK;
                $data['message']  = Yii::t('app', 'Services retrieved successfully.');
                $data['services'] = $list;
            }
        } catch (UnauthorizedHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (BadRequestHttpException $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = $e->getMessage();
        } catch (Exception $e) {
            $data['status'] = self::API_NOK;
            $data['error']  = Yii::t('app', 'An unexpected error occurred: ') . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }




    public function actionListComboPackages()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $get     = Yii::$app->request->get();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $query = ComboPackages::find()
                ->where(['vendor_details_id' => $vendor->id]);

            // Optional filter by status
            if (isset($get['status'])) {
                $query->andWhere(['status' => $get['status']]);
            }

            $comboPackages = $query->orderBy(['id' => SORT_DESC])->all();

            $list = [];
            foreach ($comboPackages as $combo) {
                $list[] = $combo->asJsonVender();
            }

            $data['status']         = self::API_OK;
            $data['combo_packages'] = $list;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }



    public function actionViewComboServices()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $get     = Yii::$app->request->get();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $combo_package_id = $get['combo_package_id'] ?? null;
            if (! $combo_package_id) {
                throw new \yii\web\BadRequestHttpException("combo_package_id is required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $comboServices = ComboServices::find()
                ->where([
                    'combo_package_id'  => $combo_package_id,
                    'vendor_details_id' => $vendor->id,
                    'status'            => ComboServices::STATUS_ACTIVE,
                ])
                ->with('service') // assumes relation getService() exists
                ->all();

            $list = [];
            foreach ($comboServices as $combo) {
                if ($combo->service) {
                    $list[] = $combo->service->asJson();
                }
            }

            $data['status']           = self::API_OK;
            $data['combo_package_id'] = $combo_package_id;
            $data['services']         = $list;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }




    public function actionAddOrUpdateComboService()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            // Extract and validate required fields
            $title          = $post['title'] ?? null;
            $price          = $post['price'] ?? null;
            $time           = $post['duration'] ?? null;
            $services_ids   = $post['services_ids'] ?? null; // Array
            $discount_price = ! empty($post['discount_price']) ? $post['discount_price'] : 0;
            $isUpdate       = ! empty($post['id']);

            if (is_string($services_ids)) {
                // If string starts with [ and ends with ], treat it as JSON array
                $trimmed = trim($services_ids);
                if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                    $services_ids = json_decode($trimmed, true);
                } else {
                    // fallback: CSV string "6171,6174,6170"
                    $services_ids = array_filter(array_map('trim', explode(',', $trimmed)));
                }
            }

            if (! $title || $price === null || ! $time || empty($services_ids) || ! is_array($services_ids)) {
                throw new \yii\web\BadRequestHttpException("Title, price, duration, and services_ids (array) are required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id, 'status' => VendorDetails::STATUS_ACTIVE]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Active vendor not found.");
            }

            $comboPackage = null;

            // Check duplicate if not updating
            if (! $isUpdate) {
                $duplicate = ComboPackages::find()
                    ->where([
                        'vendor_details_id' => $vendor->id,
                        'title'             => $title,
                        'price'             => $price,
                        'time'              => $time,
                    ])
                    ->exists();

                if ($duplicate) {
                    throw new \yii\web\ConflictHttpException("A combo package with the same title, price, and time already exists.");
                }

                $comboPackage                 = new ComboPackages();
                $comboPackage->created_on     = date('Y-m-d H:i:s');
                $comboPackage->create_user_id = $user_id;
            } else {
                $comboPackage = ComboPackages::findOne([
                    'id'                => $post['id'],
                    'vendor_details_id' => $vendor->id,
                ]);

                if (! $comboPackage) {
                    throw new \yii\web\NotFoundHttpException("Combo package not found.");
                }

                // Remove old combo services
                ComboServices::deleteAll([
                    'combo_package_id'  => $comboPackage->id,
                    'vendor_details_id' => $vendor->id,
                ]);
            }

            // Set/update combo package fields
            $comboPackage->vendor_details_id = $vendor->id;
            $comboPackage->title             = $title;
            $comboPackage->price             = $price;
            $comboPackage->discount_price    = $discount_price;
            $comboPackage->time              = $time;
            $comboPackage->is_home_visit     = $post['home_visit'] ?? 0;
            $comboPackage->is_walk_in        = $post['walk_in'] ?? 0;
            $comboPackage->service_for       = $post['service_for'] ?? null;
            $comboPackage->description       = $post['description'] ?? null;
            $comboPackage->status            = $post['status'] ?? 1;
            $comboPackage->updated_on        = date('Y-m-d H:i:s');
            $comboPackage->update_user_id    = $user_id;

            if (! $comboPackage->save(false)) {
                throw new \yii\web\ServerErrorHttpException("Failed to save combo package.");
            }

            // Save combo services
            foreach ($services_ids as $service_id) {
                $comboService                    = new ComboServices();
                $comboService->vendor_details_id = $vendor->id;
                $comboService->combo_package_id  = $comboPackage->id;
                $comboService->services_id       = $service_id;
                $comboService->status            = ComboServices::STATUS_ACTIVE;
                $comboService->created_on        = date('Y-m-d H:i:s');
                $comboService->create_user_id    = $user_id;

                if (! $comboService->save()) {
                    throw new \yii\web\ServerErrorHttpException("Failed to save service ID $service_id");
                }
            }

            $data['status']        = self::API_OK;
            $data['message']       = $isUpdate ? "Combo package and services updated successfully." : "Combo package and services created successfully.";
            $data['combo_package'] = $comboPackage->attributes;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


    public function actionChangeComboPackageStatus()
    {
        $data    = [];
        $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
        $auth    = new AuthSettings();
        $user_id = $auth->getAuthSession($headers);
        $post    = Yii::$app->request->post();

        if (! $user_id) {
            return $this->sendJsonResponse([
                'status'  => self::API_NOK,
                'message' => Yii::t("app", "Vendor authentication failed."),
            ]);
        }

        try {
            $combo_package_id = $post['combo_package_id'] ?? null;
            $new_status       = $post['status'] ?? null;

            if ($combo_package_id === null || $new_status === null) {
                throw new \yii\web\BadRequestHttpException("combo_package_id and status are required.");
            }

            $vendor = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            $combo = ComboPackages::findOne([
                'id'                => $combo_package_id,
                'vendor_details_id' => $vendor->id,
            ]);

            if (! $combo) {
                throw new \yii\web\NotFoundHttpException("Combo package not found.");
            }

            $combo->status         = $new_status;
            $combo->updated_on     = date('Y-m-d H:i:s');
            $combo->update_user_id = $user_id;

            if (! $combo->save(false)) {
                throw new \yii\web\ServerErrorHttpException("Failed to update combo package status.");
            }

            $data['status']  = self::API_OK;
            $data['message'] = "Combo package status updated.";
            $data['details'] = $combo->attributes;
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


        public function actionGetSubCategories()
    {
        $data = [];

        try {
            // Get authentication header
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);

            if (! $user_id) {
                return $this->sendJsonResponse([
                    'status'  => self::API_NOK,
                    'message' => Yii::t("app", "Vendor authentication failed."),
                ]);
            }

            // Fetch POST data
            $post                  = Yii::$app->request->post();
            $main_category_id      = $post['main_category_id'] ?? null;
            $service_type_id       = $post['service_type_id'] ?? null;
            $store_service_type_id = $post['store_service_type_id'] ?? null;

            // Validate required fields
            if (! $main_category_id || ! $service_type_id || ! $store_service_type_id) {
                throw new \yii\web\BadRequestHttpException("main_category_id, service_type_id and store_service_type_id are required.");
            }

            // Fetch vendor details
            $vendor_details = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $vendor_details) {
                throw new \yii\web\NotFoundHttpException("Vendor not found.");
            }

            // Get subcategories
            $sub_categories = SubCategory::find()
                ->where([
                    'main_category_id'      => $main_category_id,
                    'service_type_id'       => $service_type_id,
                    'store_service_type_id' => $store_service_type_id,
                    'vendor_details_id'     => $vendor_details->id,
                    'status'                => SubCategory::STATUS_ACTIVE,
                ])
                ->all();

            if (empty($sub_categories)) {
                $data['status']  = self::API_NOK;
                $data['message'] = Yii::t("app", "No sub-categories found for this vendor.");
            } else {
                $data['status']  = self::API_OK;
                $data['details'] = array_map(function ($item) {
                    return $item->asJsonVendorStoreService();
                }, $sub_categories);
            }
        } catch (\yii\web\HttpException $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $data['status']  = self::API_NOK;
            $data['message'] = "Unexpected error: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


    
    public function actionAddOrUpdateSubCategory()
    {
        try {
            $headers = Yii::$app->request->headers->get('auth_code', Yii::$app->request->getQueryParam('auth_code'));
            $auth    = new AuthSettings();
            $user_id = $auth->getAuthSession($headers);
            if (! $user_id) {
                throw new UnauthorizedHttpException("Invalid or expired authentication.");
            }

            $post                  = Yii::$app->request->post();
            $id                    = $post['id'] ?? null; // subcategory ID (optional for update)
            $main_category_id      = $post['main_category_id'] ?? null;
            $service_type_id       = $post['service_type_id'] ?? null;
            $store_service_type_id = $post['store_service_type_id'] ?? null;
            $title                 = trim($post['title'] ?? '');

            if (! $main_category_id || ! $service_type_id || ! $store_service_type_id || empty($title)) {
                throw new \Exception("Missing required parameters.");
            }

            $shop = VendorDetails::findOne(['user_id' => $user_id]);
            if (! $shop) {
                throw new NotFoundHttpException("No shop details found for this user.");
            }

            $vendor_details_id = $shop->id;

            $serviceType = \app\modules\admin\models\ServiceType::findOne(['id' => $service_type_id]);
            if (! $serviceType) {
                throw new NotFoundHttpException("Invalid service_type_id provided.");
            }

            // Update by ID (if provided)
            if (! empty($id)) {
                $subCategory = SubCategory::findOne(['id' => $id, 'vendor_details_id' => $vendor_details_id]);
                if (! $subCategory) {
                    throw new NotFoundHttpException("Sub-category with ID not found or not yours.");
                }
            } else {
                // Try to find based on composite key
                $subCategory = SubCategory::findOne([
                    'main_category_id'      => $main_category_id,
                    'vendor_details_id'     => $vendor_details_id,
                    'service_type_id'       => $service_type_id,
                    'store_service_type_id' => $store_service_type_id,
                    'title'                 => $title,
                ]);

                if (! $subCategory) {
                    $subCategory = new SubCategory();
                }
            }

            $slug = User::generateUniqueSlug($title . $vendor_details_id, $vendor_details_id);

            $subCategory->main_category_id      = $main_category_id;
            $subCategory->vendor_details_id     = $vendor_details_id;
            $subCategory->store_service_type_id = $store_service_type_id;
            $subCategory->image                 = $serviceType->image ?? null;
            $subCategory->service_type_id       = $service_type_id;
            $subCategory->title                 = $title;
            $subCategory->slug                  = $slug;
            $subCategory->status                = SubCategory::STATUS_ACTIVE; 

            if ($subCategory->save(false)) {
                $data['status']  = self::API_OK;
                $data['message'] = $id ? "Sub-category updated successfully." : "Sub-category created successfully.";
                $data['details'] = $subCategory->asJsonVendorStoreService();
            } else {
                $data['message'] = "Failed to save sub-category.";
                $data['errors']  = $subCategory->getErrors();
            }
        } catch (Exception $e) {
            $data['message'] = $e->getMessage();
        } catch (Exception $e) {
            $data['message'] = "An unexpected error occurred: " . $e->getMessage();
        }

        return $this->sendJsonResponse($data);
    }


    
}
